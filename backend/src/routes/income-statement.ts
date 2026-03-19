import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";
import { requireRole, ROLES } from "../lib/auth.js";
import {
  isVehicleCostAccount,
  getVehicleCostAmount,
  getFuelCostAmount,
  getRoadUsageCostAmount,
} from "../lib/vehicle-costs.js";
import { getPreviousYearMonth } from "../lib/salary-daily-proration.js";
import { isLocationExpenseProrationAccount } from "../lib/location-expense-proration.js";
import { getRevenueFromSpreadsheets } from "../lib/spreadsheet-revenue.js";
import * as XLSX from "xlsx";

/** 手入力専用（CSV/API一括登録不可）の勘定科目名 */
const MANUAL_INPUT_ONLY_NAMES = ["その他", "不動産収入", "人材派遣収入"];

export const incomeStatementRouter = Router();

const METADATA_TTL = 5 * 60 * 1000; // 5 minutes
let metadataCache: { data: { accountItems: unknown[]; locations: unknown[] }; expiry: number; yearMonth: string } | null = null;

function invalidateMetadataCache() {
  metadataCache = null;
}

incomeStatementRouter.get("/metadata", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const now = Date.now();
  if (metadataCache && metadataCache.yearMonth === yearMonth && metadataCache.expiry > now) {
    res.json(metadataCache.data);
    return;
  }

  const [accountItems, locations] = await Promise.all([
    prisma.accountItem.findMany({
      where: accountItemEffectiveWhere(yearMonth),
      orderBy: { sortOrder: "asc" },
    }),
    prisma.location.findMany({ orderBy: { code: "asc" } }),
  ]);

  metadataCache = {
    data: { accountItems, locations },
    expiry: now + METADATA_TTL,
    yearMonth,
  };

  res.json({ accountItems, locations });
});

incomeStatementRouter.get("/", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;
  const locationId = req.query.locationId as string | undefined;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  if (!locationId) {
    res.status(400).json({ error: "locationId is required" });
    return;
  }

  const vehicles = await prisma.vehicle.findMany({
    where: { locationId },
    select: {
      id: true,
      locationId: true,
      vehicleNo: true,
      serviceType: true,
      createdAt: true,
      updatedAt: true,
      location: true,
      course: { select: { id: true, name: true, code: true } },
    },
    orderBy: [{ locationId: "asc" }, { vehicleNo: "asc" }],
  });

  const vehicleIds = vehicles.map((v) => v.id);
  const prevYearMonth = getPreviousYearMonth(yearMonth);

  const [records, prevMonthRecords, vehicleCosts, prevMonthVehicleCosts, locationExpenses, locationParams, accountItemsForCost] =
    await Promise.all([
      prisma.monthlyRecord.findMany({
        where: {
          yearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.monthlyRecord.findMany({
        where: {
          yearMonth: prevYearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: {
          yearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: {
          yearMonth: prevYearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.locationMonthlyExpense.findMany({
        where: {
          yearMonth,
          locationId,
        },
        select: { accountItemId: true, amount: true },
      }),
      prisma.locationCalculationParameter.findMany({
        where: {
          yearMonth: prevYearMonth,
          locationId,
        },
      }),
      prisma.accountItem.findMany({
        where: accountItemEffectiveWhere(yearMonth),
        select: { id: true, code: true, category: true, name: true },
      }),
    ]);

  const vehicleCostMap = new Map<string, { leaseDepreciation: number; vehicleDepreciation: number; vehicleLease: number; insuranceCost: number; taxCost: number; fuelEfficiency: number; roadUsageFee: number }>();
  for (const vc of vehicleCosts) {
    vehicleCostMap.set(vc.vehicleId, {
      leaseDepreciation: Number(vc.leaseDepreciation),
      vehicleDepreciation: Number(vc.vehicleDepreciation),
      vehicleLease: Number(vc.vehicleLease),
      insuranceCost: Number(vc.insuranceCost),
      taxCost: Number(vc.taxCost),
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }

  const prevMonthVehicleCostMap = new Map<string, { fuelEfficiency: number; roadUsageFee: number }>();
  for (const vc of prevMonthVehicleCosts) {
    prevMonthVehicleCostMap.set(vc.vehicleId, {
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }

  const locationParam = locationParams[0] ?? null;
  const locationParamForCalc = locationParam
    ? {
        fuelUnitPrice: Number(locationParam.fuelUnitPrice ?? 0),
        roadUsageDiscountRate: Number(locationParam.roadUsageDiscountRate ?? 1),
      }
    : null;

  const accountCodeById = new Map<string, string>();
  for (const a of accountItemsForCost) {
    accountCodeById.set(a.id, a.code);
  }

  // 売上科目（手入力専用以外）は MonthlyRecord を参照せずスプレッドシートのみ
  const revenueFromSpreadsheetIds = new Set(
    accountItemsForCost
      .filter(
        (a) =>
          a.category === "revenue" && !MANUAL_INPUT_ONLY_NAMES.includes(a.name)
      )
      .map((a) => a.id)
  );

  const recordMap = new Map<string, number>();
  let lastUpdatedAt: Date | null = null;
  for (const r of records) {
    if (revenueFromSpreadsheetIds.has(r.accountItemId)) continue;
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    if (!lastUpdatedAt || r.updatedAt > lastUpdatedAt) {
      lastUpdatedAt = r.updatedAt;
    }
  }

  // 車両月次費用（イズミクラウド連携）対象科目は VehicleMonthlyCost の値を優先
  for (const v of vehicles) {
    const cost = vehicleCostMap.get(v.id);
    for (const a of accountItemsForCost) {
      if (isVehicleCostAccount(a.code)) {
        const amount = getVehicleCostAmount(cost ?? null, a.code);
        recordMap.set(`${v.id}-${a.id}`, amount);
      }
    }
  }

  // 燃料費・道路使用料（ITP連携＋計算）は前月の VehicleMonthlyCost と拠点パラメータで算出
  for (const v of vehicles) {
    const prevCost = prevMonthVehicleCostMap.get(v.id);
    const costForCalc = prevCost
      ? {
          fuelEfficiency: prevCost.fuelEfficiency,
          roadUsageFee: prevCost.roadUsageFee,
        }
      : null;
    for (const a of accountItemsForCost) {
      if (a.code === "6175") {
        recordMap.set(`${v.id}-${a.id}`, getFuelCostAmount(costForCalc, locationParamForCalc));
      } else if (a.code === "6176") {
        recordMap.set(`${v.id}-${a.id}`, getRoadUsageCostAmount(costForCalc, locationParamForCalc));
      }
    }
  }

  // 拠点別経費（別システム連携）対象科目は LocationMonthlyExpense を車両数で按分して表示
  const vehicleCount = vehicles.length;
  if (vehicleCount > 0) {
    for (const exp of locationExpenses) {
      const a = accountItemsForCost.find((x) => x.id === exp.accountItemId);
      if (a && isLocationExpenseProrationAccount(a.code)) {
        const amountPerVehicle =
          Math.round((Number(exp.amount) / vehicleCount) * 100) / 100;
        for (const v of vehicles) {
          recordMap.set(`${v.id}-${exp.accountItemId}`, amountPerVehicle);
        }
      }
    }
  }

  // 給与系科目（乗務員給料・通勤手当）は前月分の MonthlyRecord をそのまま表示（乗車回数ベースで配賦済み）
  for (const r of prevMonthRecords) {
    const a = accountItemsForCost.find((x) => x.id === r.accountItemId);
    if (a && (a.code === "6138" || a.code === "6147")) {
      recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    }
  }

  // 売上科目（手入力専用以外）は各拠点スプレッドシート参照のみ
  const revenueAccountItemIds = Array.from(revenueFromSpreadsheetIds);
  if (revenueAccountItemIds.length > 0) {
    const spreadsheetRevenue = await getRevenueFromSpreadsheets({
      locationId,
      yearMonth,
      vehicleIds: vehicles.map((v) => v.id),
      revenueAccountItemIds,
    });
    spreadsheetRevenue.forEach((amount, key) => {
      recordMap.set(key, amount);
    });
  }

  res.json({
    vehicles,
    records: Object.fromEntries(recordMap),
    yearMonth,
    lastUpdatedAt: lastUpdatedAt ? lastUpdatedAt.toISOString() : null,
  });
});

incomeStatementRouter.get("/export", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;
  const locationId = req.query.locationId as string | undefined;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const vehicles = await prisma.vehicle.findMany({
    where: locationId ? { locationId } : undefined,
    select: {
      id: true,
      locationId: true,
      vehicleNo: true,
      serviceType: true,
      createdAt: true,
      updatedAt: true,
      location: true,
      course: { select: { id: true, name: true, code: true } },
    },
    orderBy: [{ locationId: "asc" }, { vehicleNo: "asc" }],
  });

  const vehicleIds = vehicles.map((v) => v.id);
  const exportPrevYearMonth = getPreviousYearMonth(yearMonth);

  const locationIdsForExport = Array.from(new Set(vehicles.map((v) => v.locationId)));
  const [accountItems, records, exportPrevMonthRecords, vehicleCosts, prevMonthVehicleCostsExport, locationExpensesForExport, locationParamsForExport] =
    await Promise.all([
      prisma.accountItem.findMany({
        where: accountItemEffectiveWhere(yearMonth),
        orderBy: { sortOrder: "asc" },
      }),
      prisma.monthlyRecord.findMany({
        where: {
          yearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.monthlyRecord.findMany({
        where: {
          yearMonth: exportPrevYearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: {
          yearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: {
          yearMonth: exportPrevYearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.locationMonthlyExpense.findMany({
        where: {
          yearMonth,
          locationId: { in: locationIdsForExport },
        },
        select: { locationId: true, accountItemId: true, amount: true },
      }),
      prisma.locationCalculationParameter.findMany({
        where: {
          yearMonth: exportPrevYearMonth,
          locationId: { in: locationIdsForExport },
        },
      }),
    ]);

  const vehicleCostMap = new Map<string, { leaseDepreciation: number; vehicleDepreciation: number; vehicleLease: number; insuranceCost: number; taxCost: number; fuelEfficiency: number; roadUsageFee: number }>();
  for (const vc of vehicleCosts) {
    vehicleCostMap.set(vc.vehicleId, {
      leaseDepreciation: Number(vc.leaseDepreciation),
      vehicleDepreciation: Number(vc.vehicleDepreciation),
      vehicleLease: Number(vc.vehicleLease),
      insuranceCost: Number(vc.insuranceCost),
      taxCost: Number(vc.taxCost),
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }

  const prevMonthVehicleCostMapExport = new Map<string, { fuelEfficiency: number; roadUsageFee: number }>();
  for (const vc of prevMonthVehicleCostsExport) {
    prevMonthVehicleCostMapExport.set(vc.vehicleId, {
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }

  const locationParamMapExport = new Map<string, { fuelUnitPrice: number; roadUsageDiscountRate: number }>();
  for (const p of locationParamsForExport) {
    locationParamMapExport.set(p.locationId, {
      fuelUnitPrice: Number(p.fuelUnitPrice ?? 0),
      roadUsageDiscountRate: Number(p.roadUsageDiscountRate ?? 1),
    });
  }

  // 売上科目（手入力専用以外）は MonthlyRecord を参照せずスプレッドシートのみ
  const exportRevenueFromSpreadsheetIds = new Set(
    accountItems
      .filter(
        (a) =>
          a.category === "revenue" && !MANUAL_INPUT_ONLY_NAMES.includes(a.name)
      )
      .map((a) => a.id)
  );

  const recordMap = new Map<string, number>();
  for (const r of records) {
    if (exportRevenueFromSpreadsheetIds.has(r.accountItemId)) continue;
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
  }

  // 車両月次費用（イズミクラウド連携）対象科目は VehicleMonthlyCost の値を優先
  for (const v of vehicles) {
    const cost = vehicleCostMap.get(v.id);
    for (const item of accountItems) {
      if (isVehicleCostAccount(item.code)) {
        const amount = getVehicleCostAmount(cost ?? null, item.code);
        recordMap.set(`${v.id}-${item.id}`, amount);
      }
    }
  }

  // 燃料費・道路使用料（ITP連携＋計算）は前月の VehicleMonthlyCost と拠点パラメータで算出
  for (const v of vehicles) {
    const prevCost = prevMonthVehicleCostMapExport.get(v.id);
    const costForCalc = prevCost
      ? {
          fuelEfficiency: prevCost.fuelEfficiency,
          roadUsageFee: prevCost.roadUsageFee,
        }
      : null;
    const locParam = locationParamMapExport.get(v.locationId) ?? null;
    for (const item of accountItems) {
      if (item.code === "6175") {
        recordMap.set(`${v.id}-${item.id}`, getFuelCostAmount(costForCalc, locParam));
      } else if (item.code === "6176") {
        recordMap.set(`${v.id}-${item.id}`, getRoadUsageCostAmount(costForCalc, locParam));
      }
    }
  }

  // 拠点別経費（別システム連携）対象科目は LocationMonthlyExpense を車両数で按分して表示
  for (const locId of locationIdsForExport) {
    const locVehicles = vehicles.filter((v) => v.locationId === locId);
    const locVehicleCount = locVehicles.length;
    if (locVehicleCount === 0) continue;
    const locExpenses = locationExpensesForExport.filter((e) => e.locationId === locId);
    for (const exp of locExpenses) {
      const item = accountItems.find((a) => a.id === exp.accountItemId);
      if (item && isLocationExpenseProrationAccount(item.code)) {
        const amountPerVehicle =
          Math.round((Number(exp.amount) / locVehicleCount) * 100) / 100;
        for (const v of locVehicles) {
          recordMap.set(`${v.id}-${exp.accountItemId}`, amountPerVehicle);
        }
      }
    }
  }

  // 給与系科目（乗務員給料・通勤手当）は前月分の MonthlyRecord をそのまま表示（乗車回数ベースで配賦済み）
  for (const r of exportPrevMonthRecords) {
    const item = accountItems.find((x) => x.id === r.accountItemId);
    if (item && (item.code === "6138" || item.code === "6147")) {
      recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    }
  }

  // 売上科目（手入力専用以外）は各拠点スプレッドシート参照のみ
  const exportRevenueAccountItemIds = Array.from(exportRevenueFromSpreadsheetIds);
  if (exportRevenueAccountItemIds.length > 0) {
    const locationIds = Array.from(new Set(vehicles.map((v) => v.locationId)));
    for (const locId of locationIds) {
      const locVehicleIds = vehicles
        .filter((v) => v.locationId === locId)
        .map((v) => v.id);
      const spreadsheetRevenue = await getRevenueFromSpreadsheets({
        locationId: locId,
        yearMonth,
        vehicleIds: locVehicleIds,
        revenueAccountItemIds: exportRevenueAccountItemIds,
      });
      spreadsheetRevenue.forEach((amount, key) => {
        recordMap.set(key, amount);
      });
    }
  }

  const revenueIds = new Set(
    accountItems.filter((a) => a.category === "revenue").map((a) => a.id)
  );
  const expenseIds = new Set(
    accountItems.filter((a) => a.category === "expense").map((a) => a.id)
  );

  const getAmount = (vId: string, aId: string) =>
    recordMap.get(`${vId}-${aId}`) ?? 0;

  const getNetRevenue = (vId: string) => {
    let sum = 0;
    revenueIds.forEach((aId) => {
      sum += getAmount(vId, aId);
    });
    return sum;
  };

  const getTotalExpense = (vId: string) => {
    let sum = 0;
    expenseIds.forEach((aId) => {
      sum += getAmount(vId, aId);
    });
    return sum;
  };

  const getCellValue = (
    vId: string,
    item: (typeof accountItems)[0]
  ): number => {
    if (item.category === "subtotal_revenue") return getNetRevenue(vId);
    if (item.category === "subtotal_expense") return getTotalExpense(vId);
    if (item.category === "subtotal_gross")
      return getNetRevenue(vId) - getTotalExpense(vId);
    if (item.category === "summary") {
      if (item.code === "SUMMARY_REV") return getNetRevenue(vId);
      if (item.code === "SUMMARY_EXP") return getTotalExpense(vId);
      if (item.code === "SUMMARY_GROSS")
        return getNetRevenue(vId) - getTotalExpense(vId);
    }
    return getAmount(vId, item.id);
  };

  const BOM = "\uFEFF";
  const lines: string[] = [];

  const header = [
    "区分",
    "Code",
    "勘定科目",
    ...vehicles.flatMap((v) => [
      `${v.course?.name ?? v.vehicleNo}月間`,
      `${v.course?.name ?? v.vehicleNo}売上比(%)`,
    ]),
    "合計月間",
    "合計売上比(%)",
  ];
  lines.push(header.join(","));

  for (const item of accountItems) {
    const row: string[] = [
      item.category === "revenue" ? "売上" : item.category === "expense" ? "原価" : "計",
      item.code,
      item.name,
    ];

    let totalAmount = 0;
    let totalNetRevenue = 0;

    for (const v of vehicles) {
      const netRev = getNetRevenue(v.id);
      totalNetRevenue += netRev;
      const val = getCellValue(v.id, item);
      totalAmount += val;
      const ratio = netRev === 0 ? 0 : (val / netRev) * 100;
      row.push(String(val), ratio.toFixed(2));
    }

    const totalRatio =
      totalNetRevenue === 0 ? 0 : (totalAmount / totalNetRevenue) * 100;
    row.push(String(totalAmount), totalRatio.toFixed(2));
    lines.push(row.join(","));
  }

  const csv = BOM + lines.join("\n");

  res.setHeader("Content-Type", "text/csv; charset=utf-8");
  res.setHeader("Content-Disposition", `attachment; filename="pl_${yearMonth}.csv"`);
  res.send(csv);
});

incomeStatementRouter.get("/history", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;
  const vehicleId = req.query.vehicleId as string | undefined;
  const accountItemId = req.query.accountItemId as string | undefined;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const histories = await prisma.monthlyRecordHistory.findMany({
    where: {
      yearMonth,
      ...(vehicleId ? { vehicleId } : {}),
      ...(accountItemId ? { accountItemId } : {}),
    },
    include: {
      vehicle: { include: { location: true, course: true } },
      accountItem: true,
      createdBy: { select: { name: true } },
    },
    orderBy: { createdAt: "desc" },
    take: 200,
  });

  res.json(
    histories.map((h) => ({
      id: h.id,
      yearMonth: h.yearMonth,
      vehicleNo: h.vehicle.vehicleNo,
      vehicleName: h.vehicle.course?.name ?? h.vehicle.vehicleNo,
      locationName: h.vehicle.location.name,
      accountItemCode: h.accountItem.code,
      accountItemName: h.accountItem.name,
      oldAmount: Number(h.oldAmount),
      newAmount: Number(h.newAmount),
      createdAt: h.createdAt.toISOString(),
      createdByName: h.createdBy?.name ?? null,
    }))
  );
});

incomeStatementRouter.post("/records/bulk", requireRole(ROLES.EDIT_PL), async (req: Request, res: Response) => {
  const { yearMonth, records: recordsPayload } = req.body as {
    yearMonth: string;
    records: Array<{ vehicleId: string; accountItemId: string; amount: number }>;
  };

  if (!yearMonth || !Array.isArray(recordsPayload) || recordsPayload.length === 0) {
    res.status(400).json({
      error: "yearMonth and records array are required",
    });
    return;
  }

  const accountItemIds = Array.from(new Set(recordsPayload.map((r) => r.accountItemId).filter(Boolean)));
  const accountItems = await prisma.accountItem.findMany({
    where: { id: { in: accountItemIds } },
    select: { id: true, name: true },
  });
  const manualOnlyIds = new Set(
    accountItems.filter((a) => MANUAL_INPUT_ONLY_NAMES.includes(a.name)).map((a) => a.id)
  );

  await prisma.$transaction(async (tx) => {
    for (const { vehicleId, accountItemId, amount } of recordsPayload) {
      if (!vehicleId || !accountItemId) continue;
      if (manualOnlyIds.has(accountItemId)) continue;

      const existing = await tx.monthlyRecord.findUnique({
        where: {
          vehicleId_accountItemId_yearMonth: {
            vehicleId,
            accountItemId,
            yearMonth,
          },
        },
      });

      const oldAmount = existing ? Number(existing.amount) : 0;
      const newAmount = amount ?? 0;

      await tx.monthlyRecord.upsert({
        where: {
          vehicleId_accountItemId_yearMonth: {
            vehicleId,
            accountItemId,
            yearMonth,
          },
        },
        update: { amount: newAmount },
        create: {
          vehicleId,
          accountItemId,
          yearMonth,
          amount: newAmount,
        },
      });

      if (oldAmount !== newAmount) {
        await tx.monthlyRecordHistory.create({
          data: {
            vehicleId,
            accountItemId,
            yearMonth,
            oldAmount,
            newAmount,
            createdById: req.user?.id,
          },
        });
      }
    }
  });

  invalidateMetadataCache();
  res.json({ success: true });
});

incomeStatementRouter.post("/records", requireRole(ROLES.EDIT_PL), async (req: Request, res: Response) => {
  const { vehicleId, accountItemId, yearMonth, amount } = req.body;

  if (!vehicleId || !accountItemId || !yearMonth) {
    res.status(400).json({
      error: "vehicleId, accountItemId, yearMonth are required",
    });
    return;
  }

  const existing = await prisma.monthlyRecord.findUnique({
    where: {
      vehicleId_accountItemId_yearMonth: {
        vehicleId,
        accountItemId,
        yearMonth,
      },
    },
  });

  const oldAmount = existing ? Number(existing.amount) : 0;
  const newAmount = amount ?? 0;

  const record = await prisma.monthlyRecord.upsert({
    where: {
      vehicleId_accountItemId_yearMonth: {
        vehicleId,
        accountItemId,
        yearMonth,
      },
    },
    update: { amount: newAmount },
    create: {
      vehicleId,
      accountItemId,
      yearMonth,
      amount: newAmount,
    },
  });

  if (oldAmount !== newAmount) {
    await prisma.monthlyRecordHistory.create({
      data: {
        vehicleId,
        accountItemId,
        yearMonth,
        oldAmount,
        newAmount,
        createdById: req.user?.id,
      },
    });
  }

  invalidateMetadataCache();
  res.json(record);
});

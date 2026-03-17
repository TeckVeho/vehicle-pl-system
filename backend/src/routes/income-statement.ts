import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";
import { requireRole, ROLES } from "../lib/auth.js";
import { isVehicleCostAccount, getVehicleCostAmount } from "../lib/vehicle-costs.js";
import * as XLSX from "xlsx";

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

  const [records, vehicleCosts, accountItemsForCost] = await Promise.all([
    prisma.monthlyRecord.findMany({
      where: {
        yearMonth,
        vehicleId: { in: vehicles.map((v) => v.id) },
      },
    }),
    prisma.vehicleMonthlyCost.findMany({
      where: {
        yearMonth,
        vehicleId: { in: vehicles.map((v) => v.id) },
      },
    }),
    prisma.accountItem.findMany({
      where: accountItemEffectiveWhere(yearMonth),
      select: { id: true, code: true },
    }),
  ]);

  const vehicleCostMap = new Map<string, { leaseDepreciation: number; vehicleDepreciation: number; vehicleLease: number; insuranceCost: number; taxCost: number }>();
  for (const vc of vehicleCosts) {
    vehicleCostMap.set(vc.vehicleId, {
      leaseDepreciation: Number(vc.leaseDepreciation),
      vehicleDepreciation: Number(vc.vehicleDepreciation),
      vehicleLease: Number(vc.vehicleLease),
      insuranceCost: Number(vc.insuranceCost),
      taxCost: Number(vc.taxCost),
    });
  }

  const accountCodeById = new Map<string, string>();
  for (const a of accountItemsForCost) {
    accountCodeById.set(a.id, a.code);
  }

  const recordMap = new Map<string, number>();
  let lastUpdatedAt: Date | null = null;
  for (const r of records) {
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

  const [accountItems, records, vehicleCosts] = await Promise.all([
    prisma.accountItem.findMany({
      where: accountItemEffectiveWhere(yearMonth),
      orderBy: { sortOrder: "asc" },
    }),
    prisma.monthlyRecord.findMany({
      where: {
        yearMonth,
        vehicleId: { in: vehicles.map((v) => v.id) },
      },
    }),
    prisma.vehicleMonthlyCost.findMany({
      where: {
        yearMonth,
        vehicleId: { in: vehicles.map((v) => v.id) },
      },
    }),
  ]);

  const vehicleCostMap = new Map<string, { leaseDepreciation: number; vehicleDepreciation: number; vehicleLease: number; insuranceCost: number; taxCost: number }>();
  for (const vc of vehicleCosts) {
    vehicleCostMap.set(vc.vehicleId, {
      leaseDepreciation: Number(vc.leaseDepreciation),
      vehicleDepreciation: Number(vc.vehicleDepreciation),
      vehicleLease: Number(vc.vehicleLease),
      insuranceCost: Number(vc.insuranceCost),
      taxCost: Number(vc.taxCost),
    });
  }

  const recordMap = new Map<string, number>();
  for (const r of records) {
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

  await prisma.$transaction(async (tx) => {
    for (const { vehicleId, accountItemId, amount } of recordsPayload) {
      if (!vehicleId || !accountItemId) continue;

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

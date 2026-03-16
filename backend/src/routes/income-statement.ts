import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";
import { requireRole, ROLES } from "../lib/auth.js";
import * as XLSX from "xlsx";

export const incomeStatementRouter = Router();

incomeStatementRouter.get("/", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;
  const locationId = req.query.locationId as string | undefined;
  const skipStatic = req.query.skipStatic === "1";

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const [vehicles, accountItems, locations] = await Promise.all([
    prisma.vehicle.findMany({
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
    }),
    skipStatic
      ? Promise.resolve(null)
      : prisma.accountItem.findMany({
          where: accountItemEffectiveWhere(yearMonth),
          orderBy: { sortOrder: "asc" },
        }),
    skipStatic
      ? Promise.resolve(null)
      : prisma.location.findMany({ orderBy: { code: "asc" } }),
  ]);

  const records = await prisma.monthlyRecord.findMany({
    where: {
      yearMonth,
      vehicleId: { in: vehicles.map((v) => v.id) },
    },
  });

  const recordMap = new Map<string, number>();
  let lastUpdatedAt: Date | null = null;
  for (const r of records) {
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    if (!lastUpdatedAt || r.updatedAt > lastUpdatedAt) {
      lastUpdatedAt = r.updatedAt;
    }
  }

  res.json({
    vehicles,
    accountItems,
    locations,
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

  const accountItems = await prisma.accountItem.findMany({
    where: accountItemEffectiveWhere(yearMonth),
    orderBy: { sortOrder: "asc" },
  });

  const records = await prisma.monthlyRecord.findMany({
    where: {
      yearMonth,
      vehicleId: { in: vehicles.map((v) => v.id) },
    },
  });

  const recordMap = new Map<string, number>();
  for (const r of records) {
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
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
    }))
  );
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
      },
    });
  }

  res.json(record);
});

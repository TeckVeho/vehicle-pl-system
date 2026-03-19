import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";
import { getPreviousYearMonth } from "../lib/salary-daily-proration.js";

export const locationCalculationParametersRouter = Router();

/**
 * 拠点別計算パラメータ一覧取得
 * GET /api/location-calculation-parameters?yearMonth=2026-03&locationId=xxx
 *
 * yearMonth 指定時: 当月のデータがなければ前月分を引き継いで返す
 */
locationCalculationParametersRouter.get("/", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string | undefined;
  const locationId = req.query.locationId as string | undefined;

  if (!yearMonth || !/^\d{4}-\d{2}$/.test(yearMonth)) {
    const items = await prisma.locationCalculationParameter.findMany({
      where: locationId ? { locationId } : {},
      include: {
        location: { select: { id: true, code: true, name: true } },
      },
      orderBy: [{ yearMonth: "desc" }, { location: { code: "asc" } }],
    });
    return res.json(
      items.map((i) => ({
        id: i.id,
        locationId: i.locationId,
        location: i.location,
        yearMonth: i.yearMonth,
        fuelUnitPrice: Number(i.fuelUnitPrice),
        roadUsageDiscountRate: Number(i.roadUsageDiscountRate),
      }))
    );
  }

  const prevYearMonth = getPreviousYearMonth(yearMonth);
  const [locations, currentParams, prevParams] = await Promise.all([
    prisma.location.findMany({
      where: locationId ? { id: locationId } : {},
      orderBy: { code: "asc" },
      select: { id: true, code: true, name: true },
    }),
    prisma.locationCalculationParameter.findMany({
      where: { yearMonth, ...(locationId ? { locationId } : {}) },
      include: {
        location: { select: { id: true, code: true, name: true } },
      },
    }),
    prisma.locationCalculationParameter.findMany({
      where: { yearMonth: prevYearMonth, ...(locationId ? { locationId } : {}) },
    }),
  ]);

  const currentMap = new Map(
    currentParams.map((p) => [p.locationId, p])
  );
  const prevMap = new Map(
    prevParams.map((p) => [p.locationId, p])
  );

  const result = locations.map((loc) => {
    const current = currentMap.get(loc.id);
    if (current) {
      return {
        id: current.id,
        locationId: current.locationId,
        location: current.location,
        yearMonth: current.yearMonth,
        fuelUnitPrice: Number(current.fuelUnitPrice),
        roadUsageDiscountRate: Number(current.roadUsageDiscountRate),
      };
    }
    const prev = prevMap.get(loc.id);
    return {
      id: prev?.id ?? null,
      locationId: loc.id,
      location: loc,
      yearMonth,
      fuelUnitPrice: prev ? Number(prev.fuelUnitPrice) : 0,
      roadUsageDiscountRate: prev ? Number(prev.roadUsageDiscountRate) : 1,
    };
  });

  res.json(result);
});

/**
 * 拠点別計算パラメータの upsert
 * PUT /api/location-calculation-parameters
 * Body: { locationId, yearMonth, fuelUnitPrice?, roadUsageDiscountRate? }
 */
locationCalculationParametersRouter.put(
  "/",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response) => {
    const { locationId, yearMonth, fuelUnitPrice, roadUsageDiscountRate } = req.body as {
      locationId: string;
      yearMonth: string;
      fuelUnitPrice?: number;
      roadUsageDiscountRate?: number;
    };

    if (!locationId || !yearMonth || !/^\d{4}-\d{2}$/.test(yearMonth)) {
      res.status(400).json({
        error: "locationId and yearMonth (YYYY-MM) are required",
      });
      return;
    }

    const loc = await prisma.location.findUnique({
      where: { id: locationId },
    });
    if (!loc) {
      res.status(400).json({ error: "Location not found" });
      return;
    }

    const data = {
      fuelUnitPrice: Number(fuelUnitPrice ?? 0),
      roadUsageDiscountRate: Math.min(1, Math.max(0, Number(roadUsageDiscountRate ?? 1))),
    };

    const result = await prisma.locationCalculationParameter.upsert({
      where: {
        locationId_yearMonth: { locationId, yearMonth },
      },
      create: {
        locationId,
        yearMonth,
        ...data,
      },
      update: data,
    });

    res.json({
      id: result.id,
      locationId: result.locationId,
      yearMonth: result.yearMonth,
      fuelUnitPrice: Number(result.fuelUnitPrice),
      roadUsageDiscountRate: Number(result.roadUsageDiscountRate),
    });
  }
);

import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const vehicleMonthlyCostsRouter = Router();

/** 車両月次費用の勘定科目コード（イズミクラウド連携対象） */
export const VEHICLE_COST_ACCOUNT_CODES = [
  "6191", // リース車償却
  "6192", // 車両償却費
  "6193", // 車両リース
  "6194", // 損害保険料(自賠責)
  "6195", // 賦課税(自動車税)
] as const;

// 外部システム連携用: 車両月次費用一括同期（upsert）
vehicleMonthlyCostsRouter.post(
  "/sync",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response) => {
    try {
      const { yearMonth, costs } = req.body as {
        yearMonth: string;
        costs: Array<{
          vehicleId?: string;
          vehicleExternalId?: string;
          vehicleNo?: string;
          departmentId?: string;
          locationCode?: string;
          leaseDepreciation?: number;
          vehicleDepreciation?: number;
          vehicleLease?: number;
          insuranceCost?: number;
          taxCost?: number;
          fuelEfficiency?: number;  // 燃費（L、ITP連携）
          roadUsageFee?: number;   // 道路使用料（ITP連携の生データ）
        }>;
      };

      if (!yearMonth || !/^\d{4}-\d{2}$/.test(yearMonth)) {
        res.status(400).json({ error: "yearMonth (YYYY-MM) is required" });
        return;
      }

      if (!Array.isArray(costs)) {
        res.status(400).json({ error: "costs array is required" });
        return;
      }

      const results: { vehicleNo: string; status: "created" | "updated" }[] = [];

      for (const c of costs) {
        let vehicleId: string | null = null;

        if (c.vehicleId) {
          const v = await prisma.vehicle.findUnique({
            where: { id: c.vehicleId },
          });
          vehicleId = v?.id ?? null;
        }
        if (!vehicleId && c.vehicleExternalId) {
          const v = await prisma.vehicle.findFirst({
            where: { externalId: String(c.vehicleExternalId) },
          });
          vehicleId = v?.id ?? null;
        }
        if (!vehicleId && c.vehicleNo && (c.departmentId || c.locationCode)) {
          const locCode = c.locationCode ?? c.departmentId;
          const loc = await prisma.location.findUnique({
            where: { code: String(locCode) },
          });
          if (loc) {
            const v = await prisma.vehicle.findUnique({
              where: {
                locationId_vehicleNo: {
                  locationId: loc.id,
                  vehicleNo: String(c.vehicleNo).trim(),
                },
              },
            });
            vehicleId = v?.id ?? null;
          }
        }

        if (!vehicleId) {
          console.warn(
            `Vehicle not found for cost record: ${JSON.stringify(c)}, skipping`
          );
          continue;
        }

        const vehicle = await prisma.vehicle.findUnique({
          where: { id: vehicleId },
          select: { vehicleNo: true },
        });

        const data = {
          leaseDepreciation: Number(c.leaseDepreciation ?? 0),
          vehicleDepreciation: Number(c.vehicleDepreciation ?? 0),
          vehicleLease: Number(c.vehicleLease ?? 0),
          insuranceCost: Number(c.insuranceCost ?? 0),
          taxCost: Number(c.taxCost ?? 0),
          fuelEfficiency: Number(c.fuelEfficiency ?? 0),
          roadUsageFee: Number(c.roadUsageFee ?? 0),
        };

        const existing = await prisma.vehicleMonthlyCost.findUnique({
          where: {
            vehicleId_yearMonth: { vehicleId, yearMonth },
          },
        });

        await prisma.vehicleMonthlyCost.upsert({
          where: {
            vehicleId_yearMonth: { vehicleId, yearMonth },
          },
          update: data,
          create: {
            vehicleId,
            yearMonth,
            ...data,
          },
        });

        results.push({
          vehicleNo: vehicle?.vehicleNo ?? vehicleId,
          status: existing ? "updated" : "created",
        });
      }

      res.json({ synced: results.length, yearMonth, results });
    } catch (e) {
      console.error(e);
      res.status(500).json({ error: "Failed to sync vehicle monthly costs" });
    }
  }
);

import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";

export const syncLogsRouter = Router();

// 一覧取得（成功データのみ、新しい順）
syncLogsRouter.get("/", async (req: Request, res: Response) => {
  try {
    const limit = Math.min(parseInt(req.query.limit as string, 10) || 100, 500);
    const logs = await prisma.dataSyncLog.findMany({
      orderBy: { createdAt: "desc" },
      take: limit,
    });

    // locationId から拠点名を取得
    const locationIds = Array.from(
      new Set(logs.map((l) => l.locationId).filter(Boolean))
    ) as string[];
    const locations =
      locationIds.length > 0
        ? await prisma.location.findMany({
            where: { id: { in: locationIds } },
            select: { id: true, name: true },
          })
        : [];
    const locationMap = new Map(locations.map((l) => [l.id, l.name]));

    const result = logs.map((log) => ({
      id: log.id,
      source: log.source,
      syncType: log.syncType,
      recordCount: log.recordCount,
      yearMonth: log.yearMonth,
      locationId: log.locationId,
      locationName: log.locationId ? locationMap.get(log.locationId) ?? null : null,
      createdAt: log.createdAt,
    }));

    res.json(result);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to fetch sync logs" });
  }
});

// 外部システム用：連携成功時に記録を登録
syncLogsRouter.post("/", async (req: Request, res: Response) => {
  try {
    const { source, syncType, recordCount, yearMonth, locationId } = req.body;

    if (!source || !syncType) {
      res.status(400).json({
        error: "source and syncType are required",
      });
      return;
    }

    const log = await prisma.dataSyncLog.create({
      data: {
        source: String(source),
        syncType: String(syncType),
        recordCount: parseInt(String(recordCount), 10) || 0,
        yearMonth: yearMonth ? String(yearMonth) : null,
        locationId: locationId ? String(locationId) : null,
      },
    });

    res.status(201).json(log);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to create sync log" });
  }
});

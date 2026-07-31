import { Router, Request, Response } from "express";
import { z } from "zod";
import { prisma } from "../lib/prisma.js";
import { ROLES, requireRole } from "../lib/auth.js";
import { filterVisibleLocations } from "../lib/visible-locations.js";

export const locationsRouter = Router();

const patchLocationBodySchema = z.object({
  spreadsheetId: z.string().trim().min(1).nullable(),
  spreadsheetRevenueSheet: z.string().trim().min(1).nullable().optional(),
});

locationsRouter.get("/", async (req: Request, res: Response) => {
  const locations = await prisma.location.findMany({
    orderBy: { code: "asc" },
  });
  const visibleOnly = req.query.visibleOnly === "true";
  res.json(visibleOnly ? filterVisibleLocations(locations) : locations);
});

// PATCH /api/locations/:id — spreadsheetId 設定（MASTER 権限）
locationsRouter.patch(
  "/:id",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response): Promise<void> => {
    const { id } = req.params;
    const { spreadsheetId } = req.body as { spreadsheetId?: string | null };

    const existing = await prisma.location.findUnique({ where: { id } });
    if (!existing) {
      res.status(404).json({ error: "拠点が見つかりません" });
      return;
    }

    const location = await prisma.location.update({
      where: { id },
      data: { spreadsheetId: spreadsheetId ?? null },
    });
    res.json(location);
  }
);

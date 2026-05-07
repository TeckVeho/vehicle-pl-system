import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const locationsRouter = Router();

locationsRouter.get("/", async (_req: Request, res: Response) => {
  const locations = await prisma.location.findMany({
    orderBy: { code: "asc" },
  });
  res.json(locations);
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

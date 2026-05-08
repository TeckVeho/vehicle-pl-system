import { Router, Request, Response } from "express";
import { z } from "zod";
import { Prisma } from "@prisma/client";
import { prisma } from "../lib/prisma.js";
import { ROLES, requireRole } from "../lib/auth.js";

export const locationsRouter = Router();

const patchLocationBodySchema = z.object({
  spreadsheetId: z.string().trim().min(1).nullable(),
  spreadsheetRevenueSheet: z.string().trim().min(1).nullable().optional(),
});

locationsRouter.get("/", async (_req: Request, res: Response) => {
  const locations = await prisma.location.findMany({
    orderBy: { code: "asc" },
  });
  res.json(locations);
});

locationsRouter.patch(
  "/:id",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response): Promise<void> => {
    const { id } = req.params;

    const parsed = patchLocationBodySchema.safeParse(req.body);
    if (!parsed.success) {
      res.status(400).json({ error: "Invalid body", issues: parsed.error.issues });
      return;
    }

    try {
      const data: { spreadsheetId: string | null; spreadsheetRevenueSheet?: string | null } = {
        spreadsheetId: parsed.data.spreadsheetId,
      };
      if (parsed.data.spreadsheetRevenueSheet !== undefined) {
        data.spreadsheetRevenueSheet = parsed.data.spreadsheetRevenueSheet;
      }
      const updated = await prisma.location.update({
        where: { id },
        data,
      });
      res.json(updated);
    } catch (e: unknown) {
      if (
        e instanceof Prisma.PrismaClientKnownRequestError &&
        e.code === "P2025"
      ) {
        res.status(404).json({ error: "拠点が見つかりません" });
        return;
      }
      throw e;
    }
  }
);

import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const arbitraryInsuranceRouter = Router();

// 一覧取得（編集画面用）
arbitraryInsuranceRouter.get("/", async (_req: Request, res: Response) => {
  const items = await prisma.arbitraryInsuranceMaster.findMany({
    orderBy: { sortOrder: "asc" },
  });
  res.json(
    items.map((i) => ({
      id: i.id,
      tonnage: Number(i.tonnage),
      amount: Number(i.amount),
      sortOrder: i.sortOrder,
    }))
  );
});

// 金額の一括更新（編集のみ・新規追加・削除は不可）
arbitraryInsuranceRouter.patch(
  "/",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response) => {
    const { updates } = req.body as {
      updates: Array<{ id: string; amount: number }>;
    };

    if (!Array.isArray(updates) || updates.length === 0) {
      res.status(400).json({ error: "updates array is required" });
      return;
    }

    await prisma.$transaction(
      updates.map((u) =>
        prisma.arbitraryInsuranceMaster.update({
          where: { id: u.id },
          data: { amount: Number(u.amount ?? 0) },
        })
      )
    );

    res.json({ success: true });
  }
);

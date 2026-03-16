import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";

export const locationsRouter = Router();

locationsRouter.get("/", async (_req: Request, res: Response) => {
  const locations = await prisma.location.findMany({
    orderBy: { code: "asc" },
  });
  res.json(locations);
});

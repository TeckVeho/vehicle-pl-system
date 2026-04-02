import { Router, Request, Response } from "express";
import bcrypt from "bcrypt";
import { prisma } from "../lib/prisma.js";

/** Detect if a string is already a bcrypt hash (IC sends pre-hashed passwords) */
function isBcryptHash(value: string): boolean {
  return /^\$2[aby]\$\d{2}\$.{53}$/.test(value);
}

/**
 * Normalize PHP $2y$ bcrypt hashes to $2b$ for Node.js compatibility.
 * PHP Hash::make() produces $2y$ hashes which are algorithmically identical
 * to $2b$ but the Node.js bcrypt library cannot verify $2y$ hashes.
 */
function normalizeBcryptHash(hash: string): string {
  if (hash.startsWith("$2y$")) {
    return "$2b$" + hash.slice(4);
  }
  return hash;
}

// 有効な権限一覧（外部システム同期用）
export const VALID_ROLES = [
  "CREW",
  "事務員",
  "TL",
  "事業部",
  "人事労務",
  "総務広報",
  "経理財務",
  "品質管理",
  "営業",
  "現場MG",
  "本社MG",
  "部長",
  "執行役員",
  "取締役",
  "DX",
  "DX管理者",
] as const;

export const usersRouter = Router();

// 一覧取得（外部同期用）
usersRouter.get("/", async (_req: Request, res: Response) => {
  try {
    const users = await prisma.user.findMany({
      orderBy: { createdAt: "asc" },
      select: {
        id: true,
        email: true,
        name: true,
        role: true,
        externalId: true,
        createdAt: true,
        updatedAt: true,
      },
    });
    res.json(users);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to fetch users" });
  }
});

// 1件取得（externalId または id で検索）
usersRouter.get("/:idOrExternalId", async (req: Request, res: Response) => {
  try {
    const { idOrExternalId } = req.params;
    const user = await prisma.user.findFirst({
      where: {
        OR: [{ id: idOrExternalId }, { externalId: idOrExternalId }],
      },
      select: {
        id: true,
        email: true,
        name: true,
        role: true,
        externalId: true,
        createdAt: true,
        updatedAt: true,
      },
    });
    if (!user) {
      res.status(404).json({ error: "User not found" });
      return;
    }
    res.json(user);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to fetch user" });
  }
});

// 作成・更新（外部同期用 upsert: userId/externalId で検索し、あれば更新、なければ作成）
usersRouter.post("/", async (req: Request, res: Response) => {
  try {
    const { email, name, role, externalId, userId, password } = req.body;
    const extId = externalId ?? userId;

    if (!email || !name || !role) {
      res.status(400).json({
        error: "email, name, role are required",
      });
      return;
    }

    if (!VALID_ROLES.includes(role)) {
      res.status(400).json({
        error: `Invalid role. Valid roles: ${VALID_ROLES.join(", ")}`,
      });
      return;
    }

    const passwordHash = password
      ? (isBcryptHash(String(password)) ? normalizeBcryptHash(String(password)) : await bcrypt.hash(String(password), 10))
      : await bcrypt.hash("changeme", 10);

    const data = {
      email: String(email).trim(),
      name: String(name).trim(),
      role: String(role),
      externalId: extId ? String(extId).trim() : null,
      passwordHash,
    };

    let user;
    if (extId) {
      const existing = await prisma.user.findFirst({
        where: { externalId: String(extId) },
      });
      if (existing) {
        user = await prisma.user.update({
          where: { id: existing.id },
          data: {
            ...data,
            passwordHash: password ? data.passwordHash : existing.passwordHash,
          },
          select: {
            id: true,
            email: true,
            name: true,
            role: true,
            externalId: true,
            createdAt: true,
            updatedAt: true,
          },
        });
      } else {
        user = await prisma.user.create({
          data,
          select: {
            id: true,
            email: true,
            name: true,
            role: true,
            externalId: true,
            createdAt: true,
            updatedAt: true,
          },
        });
      }
    } else {
      const existing = await prisma.user.findUnique({
        where: { email: data.email },
      });
      if (existing) {
        user = await prisma.user.update({
          where: { id: existing.id },
          data: {
            name: data.name,
            role: data.role,
            externalId: data.externalId,
            ...(password && { passwordHash: data.passwordHash }),
          },
          select: {
            id: true,
            email: true,
            name: true,
            role: true,
            externalId: true,
            createdAt: true,
            updatedAt: true,
          },
        });
      } else {
        user = await prisma.user.create({
          data,
          select: {
            id: true,
            email: true,
            name: true,
            role: true,
            externalId: true,
            createdAt: true,
            updatedAt: true,
          },
        });
      }
    }

    res.json(user);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to create/update user" });
  }
});

// 一括同期（外部システム用）
usersRouter.post("/sync", async (req: Request, res: Response) => {
  try {
    const { users } = req.body;
    if (!Array.isArray(users)) {
      res.status(400).json({ error: "users array is required" });
      return;
    }

    const results: { email: string; status: "created" | "updated"; id: string }[] = [];

    for (const u of users) {
      const { email, name, role, externalId, userId, password, createdAt, updatedAt } = u;
      const extId = externalId ?? userId;
      if (!email || !name || !role) continue;
      if (!VALID_ROLES.includes(role)) continue;

      const passwordHash = password
        ? (isBcryptHash(String(password)) ? normalizeBcryptHash(String(password)) : await bcrypt.hash(String(password), 10))
        : await bcrypt.hash("changeme", 10);

      const timestamps: Record<string, Date> = {};
      if (createdAt) timestamps.createdAt = new Date(createdAt);
      if (updatedAt) timestamps.updatedAt = new Date(updatedAt);

      const data = {
        email: String(email).trim(),
        name: String(name).trim(),
        role: String(role),
        externalId: extId ? String(extId).trim() : null,
        passwordHash,
        ...timestamps,
      };

      const existing = extId
        ? await prisma.user.findFirst({ where: { externalId: String(extId) } })
        : await prisma.user.findUnique({ where: { email: data.email } });

      if (existing) {
        const updated = await prisma.user.update({
          where: { id: existing.id },
          data: {
            name: data.name,
            role: data.role,
            externalId: data.externalId,
            ...(password && { passwordHash: data.passwordHash }),
            ...(createdAt && { createdAt: new Date(createdAt) }),
            ...(updatedAt && { updatedAt: new Date(updatedAt) }),
          },
        });
        results.push({ email: data.email, status: "updated", id: updated.id });
      } else {
        const created = await prisma.user.create({ data });
        results.push({ email: data.email, status: "created", id: created.id });
      }
    }

    res.json({ synced: results.length, results });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync users" });
  }
});

// 更新
usersRouter.put("/:id", async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const { email, name, role, externalId, password } = req.body;

    const updateData: Record<string, unknown> = {};
    if (email !== undefined) updateData.email = String(email).trim();
    if (name !== undefined) updateData.name = String(name).trim();
    if (role !== undefined) {
      if (!VALID_ROLES.includes(role)) {
        res.status(400).json({
          error: `Invalid role. Valid roles: ${VALID_ROLES.join(", ")}`,
        });
        return;
      }
      updateData.role = String(role);
    }
    if (externalId !== undefined) {
      updateData.externalId = externalId ? String(externalId).trim() : null;
    }
    if (password !== undefined) {
      updateData.passwordHash = await bcrypt.hash(String(password), 10);
    }

    const user = await prisma.user.update({
      where: { id },
      data: updateData,
      select: {
        id: true,
        email: true,
        name: true,
        role: true,
        externalId: true,
        createdAt: true,
        updatedAt: true,
      },
    });
    res.json(user);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to update user" });
  }
});

// 削除
usersRouter.delete("/:id", async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    await prisma.user.delete({
      where: { id },
    });
    res.json({ success: true });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to delete user" });
  }
});

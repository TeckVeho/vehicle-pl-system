import { Request, Response, NextFunction } from "express";
import jwt from "jsonwebtoken";
import { prisma } from "./prisma.js";

const JWT_SECRET = process.env.JWT_SECRET ?? "dev-secret-change-in-production";
const COOKIE_NAME = "auth-token";
const COOKIE_MAX_AGE = 60 * 60 * 24 * 7; // 7日

export interface AuthUser {
  id: string;
  email: string;
  name: string;
  role: string;
}

export function createToken(user: AuthUser): string {
  return jwt.sign(
    { userId: user.id, email: user.email, role: user.role },
    JWT_SECRET,
    { expiresIn: "7d" }
  );
}

export function setAuthCookie(res: Response, token: string): void {
  const isProduction = process.env.NODE_ENV === "production";
  res.cookie(COOKIE_NAME, token, {
    httpOnly: true,
    secure: isProduction,
    sameSite: isProduction ? "none" : "lax",
    maxAge: COOKIE_MAX_AGE * 1000,
    path: "/",
  });
}

export function clearAuthCookie(res: Response): void {
  res.clearCookie(COOKIE_NAME, { path: "/" });
}

declare global {
  namespace Express {
    interface Request {
      user?: AuthUser;
    }
  }
}

/** 認証必須ミドルウェア。JWT を検証し req.user に設定 */
export async function requireAuth(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  const token = req.cookies?.[COOKIE_NAME] ?? req.headers.authorization?.replace("Bearer ", "");

  if (!token) {
    res.status(401).json({ error: "認証が必要です" });
    return;
  }

  try {
    const payload = jwt.verify(token, JWT_SECRET) as {
      userId: string;
      email: string;
      role: string;
    };

    const user = await prisma.user.findUnique({
      where: { id: payload.userId },
      select: { id: true, email: true, name: true, role: true },
    });

    if (!user) {
      res.status(401).json({ error: "ユーザーが見つかりません" });
      return;
    }

    req.user = user as AuthUser;
    next();
  } catch {
    res.status(401).json({ error: "認証が無効です" });
  }
}

/** 指定 role のいずれかを持つユーザーのみ許可 */
export function requireRole(allowedRoles: readonly string[]) {
  return (req: Request, res: Response, next: NextFunction): void => {
    if (!req.user) {
      res.status(401).json({ error: "認証が必要です" });
      return;
    }
    if (!allowedRoles.includes(req.user.role)) {
      res.status(403).json({ error: "この操作を行う権限がありません" });
      return;
    }
    next();
  };
}

// 権限グループ定義
export const ROLES = {
  /** 閲覧のみ（全ログインユーザー） */
  VIEW: [
    "CREW", "事務員", "TL", "事業部", "人事労務", "総務広報", "経理財務",
    "品質管理", "営業", "現場MG", "本社MG", "部長", "執行役員", "取締役", "DX", "DX管理者",
  ],
  /** 損益データの編集・インポート */
  EDIT_PL: [
    "現場MG", "本社MG", "経理財務", "部長", "執行役員", "取締役", "DX", "DX管理者",
  ],
  /** マスタ管理（勘定科目、コース） */
  MASTER: ["DX", "DX管理者"],
  /** ユーザー管理 */
  USER_ADMIN: ["DX管理者"],
} as const;

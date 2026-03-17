import { Router, Request, Response } from "express";
import bcrypt from "bcrypt";
import { prisma } from "../lib/prisma.js";
import {
  createToken,
  setAuthCookie,
  clearAuthCookie,
  requireAuth,
} from "../lib/auth.js";

export const authRouter = Router();

authRouter.post("/login", async (req: Request, res: Response) => {
  try {
    const { email, userId, password } = req.body;

    if ((!email && !userId) || !password) {
      res.status(400).json({
        error: "ユーザーID（またはメールアドレス）とパスワードを入力してください",
      });
      return;
    }

    let user = null;
    if (userId) {
      user = await prisma.user.findFirst({
        where: { externalId: String(userId).trim() },
      });
    }
    if (!user && email) {
      user = await prisma.user.findUnique({
        where: { email: String(email).trim() },
      });
    }

    if (user && (await bcrypt.compare(String(password), user.passwordHash))) {
      const isProduction = process.env.NODE_ENV === "production";
      // フロントエンド middleware 用（従来互換）
      res.cookie("auth-session", "authenticated", {
        httpOnly: true,
        secure: isProduction,
        sameSite: isProduction ? "none" : "lax",
        maxAge: 60 * 60 * 24 * 7,
        path: "/",
      });
      // 権限制御用 JWT（user 情報含む）
      const token = createToken({
        id: user.id,
        email: user.email,
        name: user.name,
        role: user.role,
      });
      setAuthCookie(res, token);
      res.json({ success: true });
      return;
    }

    res.status(401).json({
      error: "ユーザーID（またはメールアドレス）またはパスワードが正しくありません",
    });
  } catch {
    res.status(500).json({ error: "サーバーエラーが発生しました" });
  }
});

authRouter.post("/logout", (_req: Request, res: Response) => {
  res.clearCookie("auth-session", { path: "/" });
  clearAuthCookie(res);
  res.json({ success: true });
});

// 現在のユーザー情報取得（権限制御のためフロントエンドで使用）
authRouter.get("/me", requireAuth, (req: Request, res: Response) => {
  res.json(req.user);
});

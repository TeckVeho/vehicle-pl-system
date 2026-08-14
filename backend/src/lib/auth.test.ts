import jwt from "jsonwebtoken";
import { describe, expect, it, vi } from "vitest";
import {
  createToken,
  requireRole,
  ROLES,
} from "./auth.js";
import { JWT_SECRET } from "../__tests__/helpers/auth.js";

describe("createToken", () => {
  it("encodes userId, email, and role in JWT", () => {
    const token = createToken({
      id: "user-1",
      email: "test@example.com",
      name: "Test User",
      role: "DX",
    });
    const payload = jwt.verify(token, JWT_SECRET) as {
      userId: string;
      email: string;
      role: string;
    };
    expect(payload).toMatchObject({
      userId: "user-1",
      email: "test@example.com",
      role: "DX",
    });
  });
});

describe("requireRole", () => {
  function mockRes() {
    const res = {
      statusCode: 200,
      body: undefined as unknown,
      status(code: number) {
        this.statusCode = code;
        return this;
      },
      json(data: unknown) {
        this.body = data;
        return this;
      },
    };
    return res;
  }

  it("returns 401 when req.user is missing", () => {
    const middleware = requireRole(ROLES.MASTER);
    const req = {} as Parameters<typeof middleware>[0];
    const res = mockRes();
    const next = vi.fn();

    middleware(req, res as never, next);

    expect(res.statusCode).toBe(401);
    expect(res.body).toMatchObject({ error: "認証が必要です" });
    expect(next).not.toHaveBeenCalled();
  });

  it("returns 403 when role is not allowed", () => {
    const middleware = requireRole(ROLES.MASTER);
    const req = {
      user: { id: "u1", email: "u@test.local", name: "Crew", role: "CREW" },
    } as Parameters<typeof middleware>[0];
    const res = mockRes();
    const next = vi.fn();

    middleware(req, res as never, next);

    expect(res.statusCode).toBe(403);
    expect(res.body).toMatchObject({ error: "この操作を行う権限がありません" });
    expect(next).not.toHaveBeenCalled();
  });

  it("calls next when role is allowed", () => {
    const middleware = requireRole(ROLES.MASTER);
    const req = {
      user: { id: "u1", email: "u@test.local", name: "DX", role: "DX" },
    } as Parameters<typeof middleware>[0];
    const res = mockRes();
    const next = vi.fn();

    middleware(req, res as never, next);

    expect(next).toHaveBeenCalledOnce();
  });
});

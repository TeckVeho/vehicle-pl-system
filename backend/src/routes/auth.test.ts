import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { masterToken, mockUser } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const { bcryptCompareMock } = vi.hoisted(() => ({
  bcryptCompareMock: vi.fn(),
}));

vi.mock("bcrypt", () => ({
  default: {
    compare: bcryptCompareMock,
  },
}));

const app = getTestApp();

describe("auth routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
  });

  describe("POST /api/auth/login", () => {
    it("returns 400 when credentials are missing", async () => {
      const res = await request(app).post("/api/auth/login").send({});
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "ユーザーID（またはメールアドレス）とパスワードを入力してください",
      });
    });

    it("returns 401 for invalid credentials", async () => {
      prismaMock.user.findUnique.mockResolvedValue(null);
      prismaMock.user.findFirst.mockResolvedValue(null);

      const res = await request(app)
        .post("/api/auth/login")
        .send({ email: "bad@example.com", password: "wrong" });

      expect(res.status).toBe(401);
    });

    it("returns 200 with token on success", async () => {
      prismaMock.user.findFirst.mockResolvedValue({
        id: "u1",
        email: "admin@example.com",
        name: "Admin",
        role: "DX",
        passwordHash: "hashed",
      });
      bcryptCompareMock.mockResolvedValue(true);

      const res = await request(app)
        .post("/api/auth/login")
        .send({ email: "admin@example.com", password: "password" });

      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({ success: true });
      expect(res.body.token).toBeTypeOf("string");
      expect(res.headers["set-cookie"]).toBeDefined();
    });
  });

  describe("POST /api/auth/logout", () => {
    it("returns success and clears cookies", async () => {
      const res = await request(app).post("/api/auth/logout");
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({ success: true });
    });
  });

  describe("GET /api/auth/me", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).get("/api/auth/me");
      expect(res.status).toBe(401);
    });

    it("returns current user when authenticated", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/auth/me")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        id: "u1",
        email: "u1@test.local",
        role: "DX",
      });
    });
  });
});

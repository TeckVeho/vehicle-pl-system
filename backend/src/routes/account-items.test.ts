import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { crewToken, masterToken, mockUser } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const app = getTestApp();

describe("account-items routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "ai-1", code: "5010", name: "売上", category: "revenue", sortOrder: 1 },
    ]);
  });

  describe("GET /api/account-items", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).get("/api/account-items");
      expect(res.status).toBe(401);
    });

    it("returns account items for authenticated user", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/account-items")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body).toHaveLength(1);
    });

    it("filters by yearMonth when provided", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/account-items?yearMonth=2026-03")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(prismaMock.accountItem.findMany).toHaveBeenCalled();
    });
  });

  describe("POST /api/account-items", () => {
    it("returns 403 for non-MASTER role", async () => {
      prismaMock.user.findUnique.mockResolvedValue(mockUser("CREW", "u2"));
      const token = crewToken();
      const res = await request(app)
        .post("/api/account-items")
        .set("Cookie", `auth-token=${token}`)
        .send({ code: "9999", name: "Test", category: "expense" });

      expect(res.status).toBe(403);
    });

    it("returns 400 when required fields are missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/account-items")
        .set("Cookie", `auth-token=${token}`)
        .send({ code: "9999" });

      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "code, name, category are required" });
    });

    it("creates account item for MASTER", async () => {
      prismaMock.accountItem.aggregate.mockResolvedValue({ _max: { sortOrder: 10 } });
      prismaMock.accountItem.create.mockResolvedValue({
        id: "ai-new",
        code: "9999",
        name: "新科目",
        category: "expense",
        sortOrder: 11,
      });
      const token = masterToken();
      const res = await request(app)
        .post("/api/account-items")
        .set("Cookie", `auth-token=${token}`)
        .send({ code: "9999", name: "新科目", category: "expense" });

      expect(res.status).toBe(200);
      expect(res.body.name).toBe("新科目");
    });
  });
});

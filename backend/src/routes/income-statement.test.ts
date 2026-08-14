import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { masterToken, mockUser } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const app = getTestApp();

describe("income-statement routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "ai-1", code: "5010", name: "売上", category: "revenue", sortOrder: 1 },
    ]);
    prismaMock.location.findMany.mockResolvedValue([
      { id: "loc-1", code: "LOC002", name: "東京" },
    ]);
  });

  describe("GET /api/income-statement/metadata", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).get("/api/income-statement/metadata");
      expect(res.status).toBe(401);
    });

    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/income-statement/metadata")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth is required" });
    });

    it("returns account items and visible locations", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/income-statement/metadata?yearMonth=2026-03")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body.accountItems).toHaveLength(1);
      expect(res.body.locations).toHaveLength(1);
      expect(prismaMock.accountItem.findMany).toHaveBeenCalled();
      expect(prismaMock.location.findMany).toHaveBeenCalled();
    });

    it("uses metadata cache on repeated requests", async () => {
      const token = masterToken();
      await request(app)
        .get("/api/income-statement/metadata?yearMonth=2026-04")
        .set("Cookie", `auth-token=${token}`);
      await request(app)
        .get("/api/income-statement/metadata?yearMonth=2026-04")
        .set("Cookie", `auth-token=${token}`);

      expect(prismaMock.accountItem.findMany).toHaveBeenCalledTimes(1);
      expect(prismaMock.location.findMany).toHaveBeenCalledTimes(1);
    });
  });
});

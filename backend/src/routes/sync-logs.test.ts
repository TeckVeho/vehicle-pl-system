import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { masterToken, mockUser } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const app = getTestApp();

describe("sync-logs routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
  });

  describe("GET /api/sync-logs", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).get("/api/sync-logs");
      expect(res.status).toBe(401);
    });

    it("returns logs with location names", async () => {
      const createdAt = new Date("2026-03-01T00:00:00.000Z");
      prismaMock.dataSyncLog.findMany.mockResolvedValue([
        {
          id: "log-1",
          source: "ATMTC",
          syncType: "atmtc_transactions",
          recordCount: 5,
          yearMonth: "2026-03",
          locationId: "loc-1",
          createdAt,
        },
      ]);
      prismaMock.location.findMany.mockResolvedValue([
        { id: "loc-1", name: "東京" },
      ]);

      const token = masterToken();
      const res = await request(app)
        .get("/api/sync-logs")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body).toHaveLength(1);
      expect(res.body[0]).toMatchObject({
        syncType: "atmtc_transactions",
        locationName: "東京",
      });
    });

    it("respects limit query parameter", async () => {
      prismaMock.dataSyncLog.findMany.mockResolvedValue([]);
      const token = masterToken();
      await request(app)
        .get("/api/sync-logs?limit=50")
        .set("Cookie", `auth-token=${token}`);

      expect(prismaMock.dataSyncLog.findMany).toHaveBeenCalledWith(
        expect.objectContaining({ take: 50 })
      );
    });
  });

  describe("POST /api/sync-logs", () => {
    it("returns 400 when source or syncType is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/sync-logs")
        .set("Cookie", `auth-token=${token}`)
        .send({ source: "ATMTC" });

      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "source and syncType are required" });
    });

    it("creates sync log entry", async () => {
      prismaMock.dataSyncLog.create.mockResolvedValue({
        id: "log-new",
        source: "ATMTC",
        syncType: "daily_operating",
        recordCount: 3,
        yearMonth: "2026-03",
        locationId: null,
        createdAt: new Date(),
      });
      const token = masterToken();
      const res = await request(app)
        .post("/api/sync-logs")
        .set("Cookie", `auth-token=${token}`)
        .send({
          source: "ATMTC",
          syncType: "daily_operating",
          recordCount: 3,
          yearMonth: "2026-03",
        });

      expect(res.status).toBe(201);
      expect(res.body.syncType).toBe("daily_operating");
    });
  });
});

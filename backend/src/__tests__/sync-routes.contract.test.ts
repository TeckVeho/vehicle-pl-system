import jwt from "jsonwebtoken";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";

const JWT_SECRET = process.env.JWT_SECRET ?? "dev-secret-change-in-production";

function signToken(role: string, userId: string) {
  return jwt.sign(
    { userId, email: `${userId}@test.local`, role },
    JWT_SECRET,
    { expiresIn: "7d" }
  );
}

const { prismaMock } = vi.hoisted(() => ({
  prismaMock: {
    user: { findUnique: vi.fn() },
    location: { findMany: vi.fn(), findUnique: vi.fn() },
    vehicle: {
      findFirst: vi.fn(),
      findUnique: vi.fn(),
      upsert: vi.fn(),
      update: vi.fn(),
    },
    course: {
      findFirst: vi.fn(),
      findUnique: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      aggregate: vi.fn(),
    },
    driver: {
      findFirst: vi.fn(),
      findUnique: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
    },
    accountItem: { findMany: vi.fn() },
    driverMonthlyAmount: { upsert: vi.fn() },
    dailyDriverAssignment: {
      findMany: vi.fn(),
      deleteMany: vi.fn(),
      createMany: vi.fn(),
      upsert: vi.fn(),
    },
    dailyOperatingRecord: { upsert: vi.fn() },
    vehicleMonthlyCost: { findUnique: vi.fn(), upsert: vi.fn() },
    locationMonthlyExpense: { findMany: vi.fn() },
    monthlyRecord: { findUnique: vi.fn(), upsert: vi.fn() },
    dataSyncLog: { create: vi.fn() },
    $executeRawUnsafe: vi.fn(),
  },
}));

vi.mock("../lib/prisma.js", () => ({
  prisma: prismaMock,
}));

// Mock allocation modules called by routes after sync
vi.mock("../lib/driver-allocation.js", () => ({
  runDriverAllocation: vi.fn().mockResolvedValue({ vehiclesUpdated: 0, recordsUpdated: 0 }),
}));
vi.mock("../lib/salary-run-count-allocation.js", () => ({
  runSalaryRunCountAllocation: vi.fn().mockResolvedValue({ vehiclesUpdated: 0, recordsUpdated: 0 }),
}));
vi.mock("../lib/location-expense-allocation.js", () => ({
  runLocationExpenseAllocation: vi.fn().mockResolvedValue({ vehiclesUpdated: 0, recordsUpdated: 0 }),
}));

import { createApp } from "../app.js";

const app = createApp();

function masterToken() {
  return signToken("DX", "u1");
}

describe("sync route contracts", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue({
      id: "u1",
      email: "u1@test.local",
      name: "Test",
      role: "DX",
    });
    prismaMock.location.findMany.mockResolvedValue([]);
    prismaMock.location.findUnique.mockResolvedValue(null);
    prismaMock.dataSyncLog.create.mockResolvedValue({} as never);
  });

  // ── vehicles/sync ─────────────────────────────────

  describe("POST /api/vehicles/sync", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).post("/api/vehicles/sync").send({ vehicles: [] });
      expect(res.status).toBe(401);
    });

    it("returns 403 when role is not MASTER", async () => {
      prismaMock.user.findUnique.mockResolvedValue({
        id: "u2",
        email: "u2@test.local",
        name: "Crew",
        role: "CREW",
      });
      const token = signToken("CREW", "u2");
      const res = await request(app)
        .post("/api/vehicles/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ vehicles: [] });
      expect(res.status).toBe(403);
    });

    it("returns 400 when vehicles is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicles/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ vehicles: "nope" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "vehicles array is required" });
    });

    it("returns 200 with counts for empty vehicles array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicles/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ vehicles: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        synced: 0,
        skippedCount: 0,
        results: [],
      });
      expect(prismaMock.location.findMany).toHaveBeenCalled();
    });

    it("accepts Authorization: Bearer token (same as cookie)", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicles/sync")
        .set("Authorization", `Bearer ${token}`)
        .send({ vehicles: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({ synced: 0, skippedCount: 0, results: [] });
    });
  });

  // ── courses/sync ──────────────────────────────────

  describe("POST /api/courses/sync", () => {
    it("returns 400 when courses payload is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/courses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ courses: {} });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "courses array is required" });
    });

    it("returns 200 with synced count for empty courses array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/courses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ courses: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({ synced: 0, results: [] });
    });
  });

  // ── driver-assignments/sync ───────────────────────

  describe("POST /api/driver-assignments/sync", () => {
    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-assignments/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when records is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-assignments/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when records is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-assignments/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: "bad" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when yearMonth is not YYYY-MM", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-assignments/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "202603", records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth must be YYYY-MM format" });
    });
  });

  // ── drivers/sync ──────────────────────────────────

  describe("POST /api/drivers/sync", () => {
    it("returns 400 when drivers is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/drivers/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ drivers: "not-array" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "drivers array is required" });
    });

    it("returns 200 with empty results for empty drivers array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/drivers/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ drivers: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        success: true,
        results: [],
      });
    });

    it("returns 401 without auth", async () => {
      const res = await request(app)
        .post("/api/drivers/sync")
        .send({ drivers: [] });
      expect(res.status).toBe(401);
    });

    it("returns 403 when role is not MASTER", async () => {
      prismaMock.user.findUnique.mockResolvedValue({
        id: "u3",
        email: "u3@test.local",
        name: "Crew",
        role: "CREW",
      });
      const token = signToken("CREW", "u3");
      const res = await request(app)
        .post("/api/drivers/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ drivers: [] });
      expect(res.status).toBe(403);
    });
  });

  // ── driver-monthly-amounts/sync ───────────────────

  describe("POST /api/driver-monthly-amounts/sync", () => {
    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-monthly-amounts/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when records is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-monthly-amounts/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when records is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-monthly-amounts/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: "bad" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when yearMonth is not YYYY-MM format", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-monthly-amounts/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "March2026", records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth must be YYYY-MM format" });
    });

    it("returns 200 for empty records with valid yearMonth", async () => {
      prismaMock.accountItem.findMany.mockResolvedValue([]);
      const token = masterToken();
      const res = await request(app)
        .post("/api/driver-monthly-amounts/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        success: true,
        upserted: 0,
      });
      expect(res.body).toHaveProperty("allocation");
      expect(res.body).toHaveProperty("salaryAllocation");
    });
  });

  // ── daily-operating/sync ──────────────────────────

  describe("POST /api/daily-operating/sync", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app)
        .post("/api/daily-operating/sync")
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(401);
    });

    it("allows any authenticated role (route is not MASTER-gated)", async () => {
      prismaMock.user.findUnique.mockResolvedValue({
        id: "u3",
        email: "u3@test.local",
        name: "Crew",
        role: "CREW",
      });
      const token = signToken("CREW", "u3");
      const res = await request(app)
        .post("/api/daily-operating/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({ success: true, upserted: 0 });
    });

    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/daily-operating/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when records is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/daily-operating/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: "invalid" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when yearMonth is not YYYY-MM format", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/daily-operating/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "March", records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth must be YYYY-MM format" });
    });

    it("returns 200 for valid empty records", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/daily-operating/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        success: true,
        upserted: 0,
      });
      expect(res.body).toHaveProperty("salaryAllocation");
    });
  });

  // ── atmtc-transactions/sync ───────────────────────

  describe("POST /api/atmtc-transactions/sync", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app)
        .post("/api/atmtc-transactions/sync")
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(401);
    });

    it("returns 403 when role is not MASTER", async () => {
      prismaMock.user.findUnique.mockResolvedValue({
        id: "u3",
        email: "u3@test.local",
        name: "Crew",
        role: "CREW",
      });
      const token = signToken("CREW", "u3");
      const res = await request(app)
        .post("/api/atmtc-transactions/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(403);
    });

    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/atmtc-transactions/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({
        error: "yearMonth and records array are required",
      });
    });

    it("returns 400 when yearMonth is not YYYY-MM", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/atmtc-transactions/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "202603", records: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth must be YYYY-MM format" });
    });

    it("returns 200 for empty records", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/atmtc-transactions/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", records: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        success: true,
        assignmentsUpserted: 0,
        operatingUpserted: 0,
      });
      expect(res.body).toHaveProperty("salaryAllocation");
      expect(prismaMock.dataSyncLog.create).toHaveBeenCalledWith(
        expect.objectContaining({
          data: expect.objectContaining({
            syncType: "atmtc_transactions",
            source: "ATMTC",
          }),
        })
      );
    });
  });

  // ── location-monthly-expenses/sync ────────────────

  describe("POST /api/location-monthly-expenses/sync", () => {
    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/location-monthly-expenses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ expenses: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth (YYYY-MM) is required" });
    });

    it("returns 400 when yearMonth is invalid format", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/location-monthly-expenses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "202603", expenses: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth (YYYY-MM) is required" });
    });

    it("returns 400 when expenses is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/location-monthly-expenses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", expenses: "bad" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "expenses array is required" });
    });

    it("returns 200 for valid empty expenses", async () => {
      prismaMock.accountItem.findMany.mockResolvedValue([]);
      const token = masterToken();
      const res = await request(app)
        .post("/api/location-monthly-expenses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", expenses: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        success: true,
        upserted: 0,
      });
      expect(res.body).toHaveProperty("allocation");
    });

    it("returns 401 without auth", async () => {
      const res = await request(app)
        .post("/api/location-monthly-expenses/sync")
        .send({ yearMonth: "2026-03", expenses: [] });
      expect(res.status).toBe(401);
    });

    it("returns 403 when role is not MASTER", async () => {
      prismaMock.user.findUnique.mockResolvedValue({
        id: "u4",
        email: "u4@test.local",
        name: "Viewer",
        role: "CREW",
      });
      const token = signToken("CREW", "u4");
      const res = await request(app)
        .post("/api/location-monthly-expenses/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", expenses: [] });
      expect(res.status).toBe(403);
    });
  });

  // ── vehicle-monthly-costs/sync ────────────────────

  describe("POST /api/vehicle-monthly-costs/sync", () => {
    it("returns 400 when yearMonth is missing", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicle-monthly-costs/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ costs: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth (YYYY-MM) is required" });
    });

    it("returns 400 when yearMonth is invalid format", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicle-monthly-costs/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "bad", costs: [] });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "yearMonth (YYYY-MM) is required" });
    });

    it("returns 400 when costs is not an array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicle-monthly-costs/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", costs: "nope" });
      expect(res.status).toBe(400);
      expect(res.body).toMatchObject({ error: "costs array is required" });
    });

    it("returns 200 for valid empty costs array", async () => {
      const token = masterToken();
      const res = await request(app)
        .post("/api/vehicle-monthly-costs/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", costs: [] });
      expect(res.status).toBe(200);
      expect(res.body).toMatchObject({
        synced: 0,
        yearMonth: "2026-03",
        results: [],
      });
    });

    it("returns 401 without auth", async () => {
      const res = await request(app)
        .post("/api/vehicle-monthly-costs/sync")
        .send({ yearMonth: "2026-03", costs: [] });
      expect(res.status).toBe(401);
    });

    it("returns 403 when role is not MASTER", async () => {
      prismaMock.user.findUnique.mockResolvedValue({
        id: "u5",
        email: "u5@test.local",
        name: "Viewer",
        role: "CREW",
      });
      const token = signToken("CREW", "u5");
      const res = await request(app)
        .post("/api/vehicle-monthly-costs/sync")
        .set("Cookie", `auth-token=${token}`)
        .send({ yearMonth: "2026-03", costs: [] });
      expect(res.status).toBe(403);
    });
  });
});

import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { describe, expect, it } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";

const app = getTestApp();

describe("GET /api/health", () => {
  it("returns ok without authentication", async () => {
    const res = await request(app).get("/api/health");
    expect(res.status).toBe(200);
    expect(res.body).toEqual({ ok: true });
  });
});

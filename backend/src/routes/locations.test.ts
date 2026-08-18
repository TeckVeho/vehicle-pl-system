import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { crewToken, masterToken, mockUser } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const app = getTestApp();

const sampleLocations = [
  { id: "loc-1", code: "LOC002", name: "東京", spreadsheetId: null },
  { id: "loc-2", code: "LOC099", name: "大阪", spreadsheetId: "sheet-1" },
];

describe("locations routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
    prismaMock.location.findMany.mockResolvedValue(sampleLocations);
  });

  describe("GET /api/locations", () => {
    it("returns 401 without auth", async () => {
      const res = await request(app).get("/api/locations");
      expect(res.status).toBe(401);
    });

    it("returns all locations by default", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/locations")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body).toHaveLength(2);
    });

    it("filters to visible locations when visibleOnly=true", async () => {
      const token = masterToken();
      const res = await request(app)
        .get("/api/locations?visibleOnly=true")
        .set("Cookie", `auth-token=${token}`);

      expect(res.status).toBe(200);
      expect(res.body).toHaveLength(1);
      expect(res.body[0].id).toBe("loc-1");
    });
  });

  describe("PATCH /api/locations/:id", () => {
    it("returns 403 for non-MASTER role", async () => {
      prismaMock.user.findUnique.mockResolvedValue(mockUser("CREW", "u2", "Crew"));
      const token = crewToken();
      const res = await request(app)
        .patch("/api/locations/loc-1")
        .set("Cookie", `auth-token=${token}`)
        .send({ spreadsheetId: "new-sheet" });

      expect(res.status).toBe(403);
    });

    it("returns 404 when location not found", async () => {
      prismaMock.location.findUnique.mockResolvedValue(null);
      const token = masterToken();
      const res = await request(app)
        .patch("/api/locations/missing")
        .set("Cookie", `auth-token=${token}`)
        .send({ spreadsheetId: "new-sheet" });

      expect(res.status).toBe(404);
      expect(res.body).toMatchObject({ error: "拠点が見つかりません" });
    });

    it("updates spreadsheetId for MASTER", async () => {
      prismaMock.location.findUnique.mockResolvedValue(sampleLocations[0]);
      prismaMock.location.update.mockResolvedValue({
        ...sampleLocations[0],
        spreadsheetId: "new-sheet",
      });
      const token = masterToken();
      const res = await request(app)
        .patch("/api/locations/loc-1")
        .set("Cookie", `auth-token=${token}`)
        .send({ spreadsheetId: "new-sheet" });

      expect(res.status).toBe(200);
      expect(res.body.spreadsheetId).toBe("new-sheet");
      expect(prismaMock.location.update).toHaveBeenCalledWith({
        where: { id: "loc-1" },
        data: { spreadsheetId: "new-sheet" },
      });
    });
  });
});

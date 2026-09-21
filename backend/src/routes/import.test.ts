import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { masterToken, mockUser } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const app = getTestApp();

describe("import routes", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
  });

  it("returns 400 when file upload fields are missing", async () => {
    const token = masterToken();
    const res = await request(app)
      .post("/api/import")
      .set("Cookie", `auth-token=${token}`);

    expect(res.status).toBe(400);
    expect(res.body.error).toBe("file, locationId, yearMonth are required");
  });

  it("accepts CSV file upload via multer and rejects empty data", async () => {
    const token = masterToken();
    const csv = "vehicleNo,accountKey,amount\n";

    const res = await request(app)
      .post("/api/import")
      .set("Cookie", `auth-token=${token}`)
      .field("locationId", "loc-1")
      .field("yearMonth", "2026-07")
      .attach("file", Buffer.from(csv, "utf-8"), {
        filename: "import.csv",
        contentType: "text/csv",
      });

    expect(res.status).toBe(400);
    expect(res.body.errors).toEqual(["ファイルにデータがありません"]);
  });
});

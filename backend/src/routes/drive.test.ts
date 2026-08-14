import "../__tests__/helpers/setup-prisma-mock.js";
import request from "supertest";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { getTestApp } from "../__tests__/helpers/app.js";
import { masterToken, mockUser, signToken } from "../__tests__/helpers/auth.js";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

const { listSpreadsheetsInFolderMock } = vi.hoisted(() => ({
  listSpreadsheetsInFolderMock: vi.fn(),
}));

vi.mock("../lib/google-drive-client.js", async (importOriginal) => {
  const actual = await importOriginal<typeof import("../lib/google-drive-client.js")>();
  return {
    ...actual,
    listSpreadsheetsInFolder: listSpreadsheetsInFolderMock,
  };
});

const app = getTestApp();

describe("GET /api/drive/spreadsheets", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.user.findUnique.mockResolvedValue(mockUser("DX", "u1"));
  });

  it("returns 401 without auth", async () => {
    const res = await request(app).get("/api/drive/spreadsheets?folderId=abc");
    expect(res.status).toBe(401);
  });

  it("returns 403 when role is not MASTER", async () => {
    prismaMock.user.findUnique.mockResolvedValue(mockUser("CREW", "u2", "Crew"));
    const token = signToken("CREW", "u2");
    const res = await request(app)
      .get("/api/drive/spreadsheets?folderId=abc")
      .set("Cookie", `auth-token=${token}`);
    expect(res.status).toBe(403);
  });

  it("returns 400 when folderId is missing", async () => {
    const token = masterToken();
    const res = await request(app)
      .get("/api/drive/spreadsheets")
      .set("Cookie", `auth-token=${token}`);
    expect(res.status).toBe(400);
    expect(res.body).toMatchObject({ error: "folderId is required" });
    expect(listSpreadsheetsInFolderMock).not.toHaveBeenCalled();
  });

  it("returns 200 with spreadsheets on success", async () => {
    listSpreadsheetsInFolderMock.mockResolvedValueOnce([
      { id: "sheet-1", name: "Alpha" },
      { id: "sheet-2", name: "Beta" },
    ]);
    const token = masterToken();
    const res = await request(app)
      .get("/api/drive/spreadsheets?folderId=folder123")
      .set("Cookie", `auth-token=${token}`);

    expect(res.status).toBe(200);
    expect(res.body).toEqual({
      spreadsheets: [
        { id: "sheet-1", name: "Alpha" },
        { id: "sheet-2", name: "Beta" },
      ],
    });
    expect(listSpreadsheetsInFolderMock).toHaveBeenCalledWith("folder123");
  });

  it("returns 503 when service account is not configured", async () => {
    listSpreadsheetsInFolderMock.mockRejectedValueOnce(
      new Error("[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set")
    );
    const token = masterToken();
    const res = await request(app)
      .get("/api/drive/spreadsheets?folderId=folder123")
      .set("Cookie", `auth-token=${token}`);

    expect(res.status).toBe(503);
    expect(res.body).toMatchObject({
      error: "サービスアカウントが設定されていません",
    });
  });

  it("returns 403 when Drive denies access", async () => {
    listSpreadsheetsInFolderMock.mockRejectedValueOnce(
      Object.assign(new Error("Insufficient permissions"), { code: 403 })
    );
    const token = masterToken();
    const res = await request(app)
      .get("/api/drive/spreadsheets?folderId=folder123")
      .set("Cookie", `auth-token=${token}`);

    expect(res.status).toBe(403);
    expect(res.body).toMatchObject({
      error: "フォルダがサービスアカウントと共有されていない可能性があります",
    });
  });

  it("returns 404 when folder is not found", async () => {
    listSpreadsheetsInFolderMock.mockRejectedValueOnce(
      Object.assign(new Error("File not found"), { code: 404 })
    );
    const token = masterToken();
    const res = await request(app)
      .get("/api/drive/spreadsheets?folderId=missing")
      .set("Cookie", `auth-token=${token}`);

    expect(res.status).toBe(404);
    expect(res.body).toMatchObject({ error: "フォルダが見つかりません" });
  });

  it("returns 502 for other Drive errors", async () => {
    listSpreadsheetsInFolderMock.mockRejectedValueOnce(new Error("quota exceeded"));
    const token = masterToken();
    const res = await request(app)
      .get("/api/drive/spreadsheets?folderId=folder123")
      .set("Cookie", `auth-token=${token}`);

    expect(res.status).toBe(502);
    expect(res.body).toMatchObject({ error: "Drive API の取得に失敗しました" });
  });
});

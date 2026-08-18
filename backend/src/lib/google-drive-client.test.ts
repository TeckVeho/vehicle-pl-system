import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const { googleAuthCtorMock, authRequestMock } = vi.hoisted(() => {
  const authRequestMock = vi.fn();
  const googleAuthCtorMock = vi.fn(() => ({
    getClient: vi.fn(async () => ({ request: authRequestMock })),
  }));
  return { googleAuthCtorMock, authRequestMock };
});

vi.mock("google-auth-library", () => ({
  GoogleAuth: googleAuthCtorMock,
  OAuth2Client: class OAuth2Client {},
}));

import {
  downloadDriveFileAsXlsxBuffer,
  getDriveClient,
  getGoogleDriveFolderIdFromEnv,
  listSharedPlSpreadsheetFileRefs,
  listSpreadsheetsInFolder,
  MIME_GOOGLE_SHEETS,
  MIME_XLSX,
  resetGoogleDriveClientForTests,
} from "./google-drive-client.js";

const originalEnv = process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
const savedDriveFolderUpper = process.env.GOOGLE_DRIVE_FOLDER_ID;
const savedDriveFolderLower = process.env.google_drive_folder_id;

function restoreDriveFolderEnv(): void {
  if (savedDriveFolderUpper === undefined) {
    delete process.env.GOOGLE_DRIVE_FOLDER_ID;
  } else {
    process.env.GOOGLE_DRIVE_FOLDER_ID = savedDriveFolderUpper;
  }
  if (savedDriveFolderLower === undefined) {
    delete process.env.google_drive_folder_id;
  } else {
    process.env.google_drive_folder_id = savedDriveFolderLower;
  }
}

function clearDriveFolderEnv(): void {
  delete process.env.GOOGLE_DRIVE_FOLDER_ID;
  delete process.env.google_drive_folder_id;
}

const minimalServiceAccountJson = JSON.stringify({
  type: "service_account",
  project_id: "test-proj",
  private_key_id: "keyid",
  private_key:
    "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBg...\n-----END PRIVATE KEY-----\n",
  client_email: "svc@test-proj.iam.gserviceaccount.com",
  client_id: "123456789",
});

describe("getDriveClient", () => {
  beforeEach(() => {
    resetGoogleDriveClientForTests();
    vi.clearAllMocks();
    delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    clearDriveFolderEnv();
  });

  afterEach(() => {
    resetGoogleDriveClientForTests();
    if (originalEnv === undefined) {
      delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    } else {
      process.env.GOOGLE_SERVICE_ACCOUNT_JSON = originalEnv;
    }
    restoreDriveFolderEnv();
  });

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is not set", async () => {
    await expect(getDriveClient()).rejects.toThrow(
      "[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set"
    );
    expect(googleAuthCtorMock).not.toHaveBeenCalled();
  });

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is empty or whitespace-only", async () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = "   ";
    await expect(getDriveClient()).rejects.toThrow(
      "[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set"
    );
  });

  it("wraps JSON parse errors with a friendly prefix", async () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = "not-json";
    await expect(getDriveClient()).rejects.toThrow(
      /\[google-drive\] GOOGLE_SERVICE_ACCOUNT_JSON is not valid JSON:/
    );
    expect(googleAuthCtorMock).not.toHaveBeenCalled();
  });

  it("throws when a required service-account field is missing", async () => {
    const partial = JSON.parse(minimalServiceAccountJson) as Record<
      string,
      unknown
    >;
    delete partial.client_email;
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = JSON.stringify(partial);

    await expect(getDriveClient()).rejects.toThrow(
      "[google-drive] service account JSON missing field: client_email"
    );
    expect(googleAuthCtorMock).not.toHaveBeenCalled();
  });

  it("throws when credentials.type is not service_account", async () => {
    const wrongType = JSON.parse(minimalServiceAccountJson) as Record<
      string,
      unknown
    >;
    wrongType.type = "user";
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = JSON.stringify(wrongType);

    await expect(getDriveClient()).rejects.toThrow(
      '[google-drive] credentials.type must be "service_account"'
    );
  });

  it("returns the same singleton auth client with readonly scope", async () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = minimalServiceAccountJson;

    const client = await getDriveClient();
    expect(client).toBeDefined();
    expect(await getDriveClient()).toBe(client);

    expect(googleAuthCtorMock).toHaveBeenCalledTimes(1);
    expect(googleAuthCtorMock).toHaveBeenCalledWith({
      credentials: JSON.parse(minimalServiceAccountJson),
      scopes: ["https://www.googleapis.com/auth/drive.readonly"],
    });
  });
});

describe("downloadDriveFileAsXlsxBuffer", () => {
  beforeEach(() => {
    resetGoogleDriveClientForTests();
    vi.clearAllMocks();
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = minimalServiceAccountJson;
    clearDriveFolderEnv();
  });

  afterEach(() => {
    resetGoogleDriveClientForTests();
    if (originalEnv === undefined) {
      delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    } else {
      process.env.GOOGLE_SERVICE_ACCOUNT_JSON = originalEnv;
    }
    restoreDriveFolderEnv();
  });

  it("rejects empty fileId before hitting Drive", async () => {
    await expect(downloadDriveFileAsXlsxBuffer("  ")).rejects.toThrow(
      /empty fileId/
    );
    expect(authRequestMock).not.toHaveBeenCalled();
  });

  it("exports native Google Sheets to xlsx and returns a Buffer", async () => {
    const payload = new Uint8Array([1, 2, 3, 4, 5]).buffer;
    authRequestMock
      .mockResolvedValueOnce({
        data: { mimeType: MIME_GOOGLE_SHEETS, name: "sheet" },
      })
      .mockResolvedValueOnce({ data: payload });

    const buf = await downloadDriveFileAsXlsxBuffer("file-1");

    expect(authRequestMock).toHaveBeenNthCalledWith(1, {
      url: "https://www.googleapis.com/drive/v3/files/file-1",
      method: "GET",
      params: {
        fields: "mimeType, name",
        supportsAllDrives: true,
      },
      responseType: "json",
    });
    expect(authRequestMock).toHaveBeenNthCalledWith(2, {
      url: "https://www.googleapis.com/drive/v3/files/file-1/export",
      method: "GET",
      params: { mimeType: MIME_XLSX },
      responseType: "arraybuffer",
    });
    expect(Buffer.isBuffer(buf)).toBe(true);
    expect(buf.byteLength).toBe(5);
  });

  it("downloads non-Google-Workspace files via alt=media", async () => {
    const payload = new Uint8Array([9, 9, 9]).buffer;
    authRequestMock
      .mockResolvedValueOnce({
        data: { mimeType: MIME_XLSX, name: "report.xlsx" },
      })
      .mockResolvedValueOnce({ data: payload });

    const buf = await downloadDriveFileAsXlsxBuffer("file-2");

    expect(authRequestMock).toHaveBeenCalledTimes(2);
    expect(authRequestMock).toHaveBeenNthCalledWith(2, {
      url: "https://www.googleapis.com/drive/v3/files/file-2",
      method: "GET",
      params: { alt: "media", supportsAllDrives: true },
      responseType: "arraybuffer",
    });
    expect(Buffer.isBuffer(buf)).toBe(true);
    expect(buf.byteLength).toBe(3);
  });

  it("propagates Drive errors so callers can decide fallback", async () => {
    authRequestMock.mockRejectedValueOnce(new Error("drive boom"));

    await expect(downloadDriveFileAsXlsxBuffer("file-3")).rejects.toThrow(
      "drive boom"
    );
  });
});

describe("getGoogleDriveFolderIdFromEnv", () => {
  beforeEach(() => {
    clearDriveFolderEnv();
  });

  afterEach(() => {
    restoreDriveFolderEnv();
  });

  it("returns undefined when unset or whitespace-only", () => {
    expect(getGoogleDriveFolderIdFromEnv()).toBeUndefined();
    process.env.GOOGLE_DRIVE_FOLDER_ID = "   ";
    process.env.google_drive_folder_id = "   ";
    expect(getGoogleDriveFolderIdFromEnv()).toBeUndefined();
  });

  it("returns trimmed id from google_drive_folder_id when upper unset", () => {
    process.env.google_drive_folder_id = "  abcFolder123  ";
    expect(getGoogleDriveFolderIdFromEnv()).toBe("abcFolder123");
  });

  it("prefers GOOGLE_DRIVE_FOLDER_ID over google_drive_folder_id", () => {
    process.env.GOOGLE_DRIVE_FOLDER_ID = "upperId";
    process.env.google_drive_folder_id = "lowerId";
    expect(getGoogleDriveFolderIdFromEnv()).toBe("upperId");
  });
});

describe("listSharedPlSpreadsheetFileRefs", () => {
  beforeEach(() => {
    resetGoogleDriveClientForTests();
    vi.clearAllMocks();
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = minimalServiceAccountJson;
    clearDriveFolderEnv();
  });

  afterEach(() => {
    resetGoogleDriveClientForTests();
    if (originalEnv === undefined) {
      delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    } else {
      process.env.GOOGLE_SERVICE_ACCOUNT_JSON = originalEnv;
    }
    restoreDriveFolderEnv();
  });

  it("returns empty list and skips Drive when no folder id configured", async () => {
    const refs = await listSharedPlSpreadsheetFileRefs();

    expect(refs).toEqual([]);
    expect(authRequestMock).not.toHaveBeenCalled();
    expect(googleAuthCtorMock).not.toHaveBeenCalled();
  });

  it("lists direct children of folder when google_drive_folder_id is set", async () => {
    process.env.google_drive_folder_id = "folderABC";
    authRequestMock.mockResolvedValueOnce({
      data: { files: [{ id: "b", name: "損益計算資料_y" }], nextPageToken: undefined },
    });

    const refs = await listSharedPlSpreadsheetFileRefs();

    expect(refs).toEqual([{ id: "b", name: "損益計算資料_y" }]);
    expect(authRequestMock).toHaveBeenCalledWith({
      url: "https://www.googleapis.com/drive/v3/files",
      method: "GET",
      params: {
        q: "'folderABC' in parents and trashed = false and name contains '損益計算資料'",
        fields: "nextPageToken, files(id, name)",
        pageSize: 100,
        pageToken: undefined,
        supportsAllDrives: true,
        includeItemsFromAllDrives: true,
      },
      responseType: "json",
    });
  });
});

describe("listSpreadsheetsInFolder", () => {
  beforeEach(() => {
    resetGoogleDriveClientForTests();
    vi.clearAllMocks();
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = minimalServiceAccountJson;
    clearDriveFolderEnv();
  });

  afterEach(() => {
    resetGoogleDriveClientForTests();
    if (originalEnv === undefined) {
      delete process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
    } else {
      process.env.GOOGLE_SERVICE_ACCOUNT_JSON = originalEnv;
    }
    restoreDriveFolderEnv();
  });

  it("throws when folderId is empty", async () => {
    await expect(listSpreadsheetsInFolder("  ")).rejects.toThrow(
      "[google-drive] listSpreadsheetsInFolder: empty folderId"
    );
    expect(authRequestMock).not.toHaveBeenCalled();
  });

  it("queries direct children with native Sheets and xlsx mimeType filter", async () => {
    authRequestMock.mockResolvedValueOnce({
      data: {
        files: [{ id: "s2", name: "B sheet" }, { id: "s1", name: "A sheet" }],
        nextPageToken: undefined,
      },
    });

    const refs = await listSpreadsheetsInFolder("folderXYZ");

    expect(refs).toEqual([
      { id: "s1", name: "A sheet" },
      { id: "s2", name: "B sheet" },
    ]);
    expect(authRequestMock).toHaveBeenCalledWith({
      url: "https://www.googleapis.com/drive/v3/files",
      method: "GET",
      params: {
        q: `'folderXYZ' in parents and trashed = false and (mimeType = '${MIME_GOOGLE_SHEETS}' or mimeType = '${MIME_XLSX}')`,
        fields: "nextPageToken, files(id, name)",
        pageSize: 100,
        pageToken: undefined,
        supportsAllDrives: true,
        includeItemsFromAllDrives: true,
      },
      responseType: "json",
    });
  });

  it("paginates across multiple pages", async () => {
    authRequestMock
      .mockResolvedValueOnce({
        data: {
          files: [{ id: "p1", name: "Page 1" }],
          nextPageToken: "token-2",
        },
      })
      .mockResolvedValueOnce({
        data: {
          files: [{ id: "p2", name: "Page 2" }],
          nextPageToken: undefined,
        },
      });

    const refs = await listSpreadsheetsInFolder("folderPaginated");

    expect(refs).toEqual([
      { id: "p1", name: "Page 1" },
      { id: "p2", name: "Page 2" },
    ]);
    expect(authRequestMock).toHaveBeenCalledTimes(2);
    expect(authRequestMock).toHaveBeenNthCalledWith(
      2,
      expect.objectContaining({
        params: expect.objectContaining({ pageToken: "token-2" }),
      })
    );
  });

  it("propagates Drive API errors", async () => {
    authRequestMock.mockRejectedValueOnce(
      Object.assign(new Error("forbidden"), { code: 403 })
    );

    await expect(listSpreadsheetsInFolder("folderDenied")).rejects.toMatchObject({
      code: 403,
    });
  });
});

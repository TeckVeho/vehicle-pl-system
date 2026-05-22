import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const {
  googleAuthMock,
  driveFactoryMock,
  filesGetMock,
  filesExportMock,
  filesListMock,
} = vi.hoisted(() => {
  const googleAuthMock = vi.fn();
  const filesGetMock = vi.fn();
  const filesExportMock = vi.fn();
  const filesListMock = vi.fn();
  const driveFactoryMock = vi.fn(() => ({
    files: {
      get: filesGetMock,
      export: filesExportMock,
      list: filesListMock,
    },
  }));
  return {
    googleAuthMock,
    driveFactoryMock,
    filesGetMock,
    filesExportMock,
    filesListMock,
  };
});

vi.mock("googleapis", () => ({
  google: {
    auth: { GoogleAuth: googleAuthMock },
    drive: driveFactoryMock,
  },
  drive_v3: {},
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

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is not set", () => {
    expect(() => getDriveClient()).toThrow(
      "[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set"
    );
    expect(googleAuthMock).not.toHaveBeenCalled();
    expect(driveFactoryMock).not.toHaveBeenCalled();
  });

  it("throws when GOOGLE_SERVICE_ACCOUNT_JSON is empty or whitespace-only", () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = "   ";
    expect(() => getDriveClient()).toThrow(
      "[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set"
    );
  });

  it("wraps JSON parse errors with a friendly prefix", () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = "not-json";
    expect(() => getDriveClient()).toThrow(
      /\[google-drive\] GOOGLE_SERVICE_ACCOUNT_JSON is not valid JSON:/
    );
    expect(googleAuthMock).not.toHaveBeenCalled();
    expect(driveFactoryMock).not.toHaveBeenCalled();
  });

  it("throws when a required service-account field is missing", () => {
    const partial = JSON.parse(minimalServiceAccountJson) as Record<
      string,
      unknown
    >;
    delete partial.client_email;
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = JSON.stringify(partial);

    expect(() => getDriveClient()).toThrow(
      "[google-drive] service account JSON missing field: client_email"
    );
    expect(googleAuthMock).not.toHaveBeenCalled();
  });

  it("throws when credentials.type is not service_account", () => {
    const wrongType = JSON.parse(minimalServiceAccountJson) as Record<
      string,
      unknown
    >;
    wrongType.type = "user";
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = JSON.stringify(wrongType);

    expect(() => getDriveClient()).toThrow(
      '[google-drive] credentials.type must be "service_account"'
    );
  });

  it("returns the same singleton and builds drive v3 client with readonly scope", () => {
    process.env.GOOGLE_SERVICE_ACCOUNT_JSON = minimalServiceAccountJson;

    const client = getDriveClient();
    expect(client).toBeDefined();
    expect(getDriveClient()).toBe(client);

    expect(googleAuthMock).toHaveBeenCalledTimes(1);
    expect(googleAuthMock).toHaveBeenCalledWith({
      credentials: JSON.parse(minimalServiceAccountJson),
      scopes: ["https://www.googleapis.com/auth/drive.readonly"],
    });

    expect(driveFactoryMock).toHaveBeenCalledTimes(1);
    expect(driveFactoryMock).toHaveBeenCalledWith({
      version: "v3",
      auth: expect.anything(),
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
    expect(filesGetMock).not.toHaveBeenCalled();
    expect(filesExportMock).not.toHaveBeenCalled();
  });

  it("exports native Google Sheets to xlsx and returns a Buffer", async () => {
    const payload = new Uint8Array([1, 2, 3, 4, 5]).buffer;
    filesGetMock.mockResolvedValueOnce({
      data: { mimeType: MIME_GOOGLE_SHEETS, name: "sheet" },
    });
    filesExportMock.mockResolvedValueOnce({ data: payload });

    const buf = await downloadDriveFileAsXlsxBuffer("file-1");

    expect(filesGetMock).toHaveBeenNthCalledWith(1, {
      fileId: "file-1",
      fields: "mimeType, name",
      supportsAllDrives: true,
    });
    expect(filesExportMock).toHaveBeenCalledWith(
      { fileId: "file-1", mimeType: MIME_XLSX },
      { responseType: "arraybuffer" }
    );
    expect(filesGetMock).toHaveBeenCalledTimes(1);
    expect(Buffer.isBuffer(buf)).toBe(true);
    expect(buf.byteLength).toBe(5);
  });

  it("downloads non-Google-Workspace files via alt=media", async () => {
    const payload = new Uint8Array([9, 9, 9]).buffer;
    filesGetMock.mockResolvedValueOnce({
      data: { mimeType: MIME_XLSX, name: "report.xlsx" },
    });
    filesGetMock.mockResolvedValueOnce({ data: payload });

    const buf = await downloadDriveFileAsXlsxBuffer("file-2");

    expect(filesGetMock).toHaveBeenCalledTimes(2);
    expect(filesGetMock).toHaveBeenNthCalledWith(1, {
      fileId: "file-2",
      fields: "mimeType, name",
      supportsAllDrives: true,
    });
    expect(filesGetMock).toHaveBeenNthCalledWith(
      2,
      { fileId: "file-2", alt: "media", supportsAllDrives: true },
      { responseType: "arraybuffer" }
    );
    expect(filesExportMock).not.toHaveBeenCalled();
    expect(Buffer.isBuffer(buf)).toBe(true);
    expect(buf.byteLength).toBe(3);
  });

  it("propagates Drive errors so callers can decide fallback", async () => {
    filesGetMock.mockRejectedValueOnce(new Error("drive boom"));

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
    expect(filesListMock).not.toHaveBeenCalled();
    expect(driveFactoryMock).not.toHaveBeenCalled();
  });

  it("lists direct children of folder when google_drive_folder_id is set", async () => {
    process.env.google_drive_folder_id = "folderABC";
    filesListMock.mockResolvedValueOnce({
      data: { files: [{ id: "b", name: "損益計算資料_y" }], nextPageToken: null },
    });

    const refs = await listSharedPlSpreadsheetFileRefs();

    expect(refs).toEqual([{ id: "b", name: "損益計算資料_y" }]);
    expect(filesListMock).toHaveBeenCalledWith({
      q: "'folderABC' in parents and trashed = false and name contains '損益計算資料'",
      fields: "nextPageToken, files(id, name)",
      pageSize: 100,
      pageToken: undefined,
      supportsAllDrives: true,
      includeItemsFromAllDrives: true,
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
    expect(filesListMock).not.toHaveBeenCalled();
  });

  it("queries direct children with native Sheets and xlsx mimeType filter", async () => {
    filesListMock.mockResolvedValueOnce({
      data: {
        files: [{ id: "s2", name: "B sheet" }, { id: "s1", name: "A sheet" }],
        nextPageToken: null,
      },
    });

    const refs = await listSpreadsheetsInFolder("folderXYZ");

    expect(refs).toEqual([
      { id: "s1", name: "A sheet" },
      { id: "s2", name: "B sheet" },
    ]);
    expect(filesListMock).toHaveBeenCalledWith({
      q: `'folderXYZ' in parents and trashed = false and (mimeType = '${MIME_GOOGLE_SHEETS}' or mimeType = '${MIME_XLSX}')`,
      fields: "nextPageToken, files(id, name)",
      pageSize: 100,
      pageToken: undefined,
      supportsAllDrives: true,
      includeItemsFromAllDrives: true,
    });
  });

  it("paginates across multiple pages", async () => {
    filesListMock
      .mockResolvedValueOnce({
        data: {
          files: [{ id: "p1", name: "Page 1" }],
          nextPageToken: "token-2",
        },
      })
      .mockResolvedValueOnce({
        data: {
          files: [{ id: "p2", name: "Page 2" }],
          nextPageToken: null,
        },
      });

    const refs = await listSpreadsheetsInFolder("folderPaginated");

    expect(refs).toEqual([
      { id: "p1", name: "Page 1" },
      { id: "p2", name: "Page 2" },
    ]);
    expect(filesListMock).toHaveBeenCalledTimes(2);
    expect(filesListMock).toHaveBeenNthCalledWith(
      2,
      expect.objectContaining({ pageToken: "token-2" })
    );
  });

  it("propagates Drive API errors", async () => {
    filesListMock.mockRejectedValueOnce(Object.assign(new Error("forbidden"), { code: 403 }));

    await expect(listSpreadsheetsInFolder("folderDenied")).rejects.toMatchObject({
      code: 403,
    });
  });
});

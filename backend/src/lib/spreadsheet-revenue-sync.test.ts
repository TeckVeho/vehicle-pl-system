import { beforeEach, describe, expect, it, vi } from "vitest";

const txApi = vi.hoisted(() => ({
  driveSpreadsheetRevenueLine: {
    deleteMany: vi.fn(),
    createMany: vi.fn(),
  },
  driveSpreadsheetRevenueCourseLine: {
    deleteMany: vi.fn(),
    createMany: vi.fn(),
  },
  locationDriveSyncMeta: {
    upsert: vi.fn(),
  },
}));

const prismaMock = vi.hoisted(() => ({
  location: { findUnique: vi.fn() },
  vehicle: { findMany: vi.fn() },
  accountItem: { findMany: vi.fn() },
  course: { findMany: vi.fn().mockResolvedValue([]) },
  locationDriveSyncMeta: { upsert: vi.fn() },
  $transaction: vi.fn(async (fn: (t: typeof txApi) => Promise<void>) => {
    await fn(txApi);
  }),
  dataSyncLog: { create: vi.fn() },
}));

vi.mock("./google-drive-client.js", () => ({
  downloadDriveFileAsXlsxBuffer: vi.fn(),
  listSharedPlSpreadsheetFileRefs: vi.fn().mockResolvedValue([]),
  getDriveFileMeta: vi.fn(),
}));

vi.mock("read-excel-file/node", () => ({
  default: vi.fn(),
  readSheetNames: vi.fn(),
}));

vi.mock("./course-allocation-trigger.js", () => ({
  runCourseAllocationScope: vi.fn().mockResolvedValue([]),
}));

vi.mock("./prisma.js", () => ({
  prisma: prismaMock,
}));

import readXlsxFile, { readSheetNames } from "read-excel-file/node";
import {
  downloadDriveFileAsXlsxBuffer,
  getDriveFileMeta,
  listSharedPlSpreadsheetFileRefs,
} from "./google-drive-client.js";
import { syncSpreadsheetRevenueForLocationYear, clearDrivePlFileCacheForTests } from "./spreadsheet-revenue.js";

describe("syncSpreadsheetRevenueForLocationYear", () => {
  beforeEach(() => {
    vi.resetAllMocks();
    clearDrivePlFileCacheForTests();
    txApi.driveSpreadsheetRevenueLine.deleteMany.mockResolvedValue({ count: 0 });
    txApi.driveSpreadsheetRevenueLine.createMany.mockResolvedValue({ count: 1 });
    txApi.locationDriveSyncMeta.upsert.mockResolvedValue({});
    prismaMock.locationDriveSyncMeta.upsert.mockResolvedValue({});
    prismaMock.dataSyncLog.create.mockResolvedValue({});
    vi.mocked(listSharedPlSpreadsheetFileRefs).mockResolvedValue([]);
    vi.mocked(getDriveFileMeta).mockResolvedValue({
      id: "file-1",
      name: "損益計算資料2026.03.xlsx",
    });
  });

  it("fails without writing lines when Drive file month mismatches requested yearMonth", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "fixed-loc017",
      spreadsheetRevenueSheet: null,
      code: "LOC017",
      name: "浜松",
    });
    vi.mocked(getDriveFileMeta).mockResolvedValue({
      id: "fixed-loc017",
      name: "18(浜松)損益計算資料2026.02.xlsx",
    });

    const r = await syncSpreadsheetRevenueForLocationYear("loc-017", "2026-07");

    expect(r.ok).toBe(false);
    if (r.ok) throw new Error("expected failure");
    expect(r.error).toContain("does not match");
    expect(txApi.driveSpreadsheetRevenueLine.createMany).not.toHaveBeenCalled();
    expect(prismaMock.locationDriveSyncMeta.upsert).toHaveBeenCalledWith(
      expect.objectContaining({
        where: {
          locationId_yearMonth: { locationId: "loc-017", yearMonth: "2026-07" },
        },
        create: expect.objectContaining({ status: "failed" }),
      })
    );
  });

  it("prefers folder file over spreadsheetId and writes canonical yearMonth from filename", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "fixed-old",
      spreadsheetRevenueSheet: null,
      code: "LOC017",
      name: "浜松",
    });
    vi.mocked(listSharedPlSpreadsheetFileRefs).mockResolvedValue([
      { id: "folder-feb", name: "18(浜松)損益計算資料2026.02.xlsx" },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "veh1", vehicleNo: "017-001", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "acc-1", name: "山崎製パン" },
    ]);
    vi.mocked(downloadDriveFileAsXlsxBuffer).mockResolvedValue(Buffer.from([1]));
    vi.mocked(readSheetNames).mockResolvedValueOnce(["2026-02"]);
    vi.mocked(readXlsxFile).mockResolvedValueOnce([
      ["vehicleNo", "山崎製パン"],
      ["017-001", 5_136_000],
    ]);

    const r = await syncSpreadsheetRevenueForLocationYear("loc-017", "2026-02");

    expect(r.ok).toBe(true);
    expect(downloadDriveFileAsXlsxBuffer).toHaveBeenCalledWith("folder-feb");
    expect(getDriveFileMeta).not.toHaveBeenCalled();
    expect(txApi.driveSpreadsheetRevenueLine.createMany).toHaveBeenCalledWith(
      expect.objectContaining({
        data: [
          expect.objectContaining({
            yearMonth: "2026-02",
            amount: 5_136_000,
          }),
        ],
      })
    );
  });

  it("re-sync replaces existing snapshot for the same canonical yearMonth", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-1",
      spreadsheetRevenueSheet: "2026-03",
      code: "LOC001",
      name: "Test",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "veh1", vehicleNo: "V-1", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "acc-1", name: "売上A" },
    ]);

    vi.mocked(downloadDriveFileAsXlsxBuffer).mockResolvedValue(Buffer.from([1]));
    vi.mocked(readSheetNames).mockResolvedValue(["2026-03"]);
    vi.mocked(readXlsxFile).mockResolvedValueOnce([
      ["vehicleNo", "売上A"],
      ["V-1", 100],
    ]);

    await syncSpreadsheetRevenueForLocationYear("loc-a", "2026-03");
    vi.mocked(readXlsxFile).mockResolvedValueOnce([
      ["vehicleNo", "売上A"],
      ["V-1", 200],
    ]);
    await syncSpreadsheetRevenueForLocationYear("loc-a", "2026-03");

    expect(txApi.driveSpreadsheetRevenueLine.deleteMany).toHaveBeenCalledTimes(2);
    expect(txApi.driveSpreadsheetRevenueLine.deleteMany).toHaveBeenCalledWith({
      where: { locationId: "loc-a", yearMonth: "2026-03" },
    });
    expect(txApi.driveSpreadsheetRevenueLine.createMany).toHaveBeenLastCalledWith(
      expect.objectContaining({
        data: [expect.objectContaining({ amount: 200, yearMonth: "2026-03" })],
      })
    );
  });

  it("writes lines and DataSyncLog on successful canonical sheet parse", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-1",
      spreadsheetRevenueSheet: "2026-03",
      code: "LOC001",
      name: "Test",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "veh1", vehicleNo: "V-1", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "acc-1", name: "売上A" },
    ]);

    vi.mocked(downloadDriveFileAsXlsxBuffer).mockResolvedValue(Buffer.from([1]));
    vi.mocked(readSheetNames).mockResolvedValueOnce(["2026-03"]);
    vi.mocked(readXlsxFile).mockResolvedValueOnce([
      ["vehicleNo", "売上A"],
      ["V-1", 42],
    ]);

    const r = await syncSpreadsheetRevenueForLocationYear("loc-a", "2026-03");

    expect(r.ok).toBe(true);
    if (!r.ok) throw new Error("expected ok");
    expect(r.recordCount).toBe(1);
    expect(txApi.driveSpreadsheetRevenueLine.createMany).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.arrayContaining([
          expect.objectContaining({
            vehicleId: "veh1",
            accountItemId: "acc-1",
            amount: 42,
          }),
        ]),
      })
    );
    expect(prismaMock.dataSyncLog.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({
          syncType: "spreadsheet_revenue",
          source: "Google Drive",
        }),
      })
    );
  });
});

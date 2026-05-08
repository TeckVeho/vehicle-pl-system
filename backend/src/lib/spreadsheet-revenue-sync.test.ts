import { beforeEach, describe, expect, it, vi } from "vitest";

const txApi = vi.hoisted(() => ({
  driveSpreadsheetRevenueLine: {
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
  $transaction: vi.fn(async (fn: (t: typeof txApi) => Promise<void>) => {
    await fn(txApi);
  }),
  dataSyncLog: { create: vi.fn() },
}));

vi.mock("./google-drive-client.js", () => ({
  downloadDriveFileAsXlsxBuffer: vi.fn(),
  listSharedPlSpreadsheetFileRefs: vi.fn().mockResolvedValue([]),
}));

vi.mock("read-excel-file/node", () => ({
  default: vi.fn(),
  readSheetNames: vi.fn(),
}));

vi.mock("./prisma.js", () => ({
  prisma: prismaMock,
}));

import readXlsxFile, { readSheetNames } from "read-excel-file/node";
import { downloadDriveFileAsXlsxBuffer } from "./google-drive-client.js";
import { syncSpreadsheetRevenueForLocationYear } from "./spreadsheet-revenue.js";

describe("syncSpreadsheetRevenueForLocationYear", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    txApi.driveSpreadsheetRevenueLine.deleteMany.mockResolvedValue({ count: 0 });
    txApi.driveSpreadsheetRevenueLine.createMany.mockResolvedValue({ count: 1 });
    txApi.locationDriveSyncMeta.upsert.mockResolvedValue({});
    prismaMock.dataSyncLog.create.mockResolvedValue({});
  });

  it("writes lines and DataSyncLog on successful canonical sheet parse", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-1",
      spreadsheetRevenueSheet: null,
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

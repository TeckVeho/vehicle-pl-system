import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

const { downloadMock, readXlsxMock, readSheetNamesMock, listPlFilesMock, getDriveFileMetaMock, prismaMock } = vi.hoisted(
  () => ({
    downloadMock: vi.fn(),
    readXlsxMock: vi.fn(),
    readSheetNamesMock: vi.fn(),
    listPlFilesMock: vi.fn(),
    getDriveFileMetaMock: vi.fn(),
    prismaMock: {
      location: { findUnique: vi.fn() },
      vehicle: { findMany: vi.fn() },
      accountItem: { findMany: vi.fn() },
      course: { findMany: vi.fn().mockResolvedValue([]) },
      locationDriveSyncMeta: { findUnique: vi.fn() },
      driveSpreadsheetRevenueLine: { findMany: vi.fn() },
    },
  })
);

vi.mock("./google-drive-client.js", () => ({
  downloadDriveFileAsXlsxBuffer: downloadMock,
  listSharedPlSpreadsheetFileRefs: listPlFilesMock,
  getDriveFileMeta: getDriveFileMetaMock,
}));

vi.mock("read-excel-file/node", () => ({
  default: readXlsxMock,
  readSheetNames: readSheetNamesMock,
}));

vi.mock("./prisma.js", () => ({
  prisma: prismaMock,
}));

import {
  clearDrivePlFileCacheForTests,
  getRevenueFromSpreadsheets,
  parseYearMonthFromPlFileName,
  pickRevenueWorkbookSheetName,
} from "./spreadsheet-revenue.js";

describe("parseYearMonthFromPlFileName", () => {
  it("parses dot, dash, and compact patterns", () => {
    expect(parseYearMonthFromPlFileName("18(浜松)損益計算資料2026.02.xlsx")).toBe(
      "2026-02"
    );
    expect(parseYearMonthFromPlFileName("13(名古屋)損益計算資料2026-03.xlsx")).toBe(
      "2026-03"
    );
    expect(parseYearMonthFromPlFileName("損益計算資料202603.xlsx")).toBe("2026-03");
    expect(parseYearMonthFromPlFileName("損益計算資料2026.2.xlsx")).toBe("2026-02");
  });

  it("returns null when no year-month in filename", () => {
    expect(parseYearMonthFromPlFileName("損益計算資料.xlsx")).toBeNull();
  });
});

describe("pickRevenueWorkbookSheetName", () => {
  it("tries yearMonth tab before 売上明細 when no configured sheet", () => {
    expect(
      pickRevenueWorkbookSheetName(
        ["車両別損益", "売上明細", "2026-03"],
        "2026-03",
        undefined
      )
    ).toBe("2026-03");
  });
});

describe("getRevenueFromSpreadsheets", () => {
  const baseParams = {
    locationId: "loc-1",
    yearMonth: "2026-03",
    vehicleIds: ["v1", "v2"],
    revenueAccountItemIds: ["a1", "a2"],
  };

  beforeEach(() => {
    vi.resetAllMocks();
    clearDrivePlFileCacheForTests();
    listPlFilesMock.mockResolvedValue([]);
    getDriveFileMetaMock.mockImplementation(async (fileId: string) => ({
      id: fileId,
      name: "損益計算資料2026.03.xlsx",
    }));
    prismaMock.locationDriveSyncMeta.findUnique.mockResolvedValue(null);
    prismaMock.driveSpreadsheetRevenueLine.findMany.mockResolvedValue([]);
    prismaMock.course.findMany.mockResolvedValue([]);
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("returns empty Map when spreadsheetId unset and no shared 損益計算資料 file matches 拠点+年月", async () => {
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: null,
      spreadsheetRevenueSheet: null,
      code: null,
      name: "名古屋",
    });

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.size).toBe(0);
    expect(listPlFilesMock).toHaveBeenCalled();
    expect(warn).toHaveBeenCalled();
    expect(String(warn.mock.calls[0]?.[0] ?? "")).toContain(
      "no auto-matched"
    );
    expect(downloadMock).not.toHaveBeenCalled();

    warn.mockRestore();
  });

  it("returns empty Map when fallback spreadsheetId file month mismatches requested yearMonth", async () => {
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "fixed-file",
      spreadsheetRevenueSheet: null,
      code: null,
      name: "浜松",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "fixed-file",
      name: "18(浜松)損益計算資料2026.02.xlsx",
    });

    const map = await getRevenueFromSpreadsheets({
      ...baseParams,
      yearMonth: "2026-07",
    });

    expect(map.size).toBe(0);
    expect(downloadMock).not.toHaveBeenCalled();
    expect(String(warn.mock.calls[0]?.[0] ?? "")).toContain("does not match");

    warn.mockRestore();
  });

  it("prefers folder auto-resolve over fixed spreadsheetId when both exist", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "fixed-old",
      spreadsheetRevenueSheet: null,
      code: "LOC017",
      name: "浜松",
    });
    listPlFilesMock.mockResolvedValue([
      { id: "folder-file", name: "18(浜松)損益計算資料2026.03.xlsx" },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "017-001", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["2026-03"]);
    readXlsxMock.mockResolvedValueOnce([
      ["vehicleNo", "山崎製パン"],
      ["017-001", 999],
    ]);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(downloadMock).toHaveBeenCalledWith("folder-file");
    expect(getDriveFileMetaMock).not.toHaveBeenCalled();
    expect(map.get("v1-a1")).toBe(999);
  });

  it("returns empty Map and logs error when Drive fails; never throws", async () => {
    const errSpy = vi.spyOn(console, "error").mockImplementation(() => {});
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-xyz",
      spreadsheetRevenueSheet: null,
      code: null,
      name: "Test",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-xyz",
      name: "損益計算資料2026.03.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "001-001", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockRejectedValue(new Error("drive boom"));

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.size).toBe(0);
    expect(errSpy).toHaveBeenCalled();
    expect(String(errSpy.mock.calls[0]?.[0] ?? "")).toContain(
      "failed to read file-xyz"
    );

    errSpy.mockRestore();
  });

  it("warns and returns empty Map when tab yearMonth is missing", async () => {
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-tab",
      spreadsheetRevenueSheet: null,
      code: null,
      name: "Test",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-tab",
      name: "損益計算資料2026.03.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "001-001", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["2026-01"]);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.size).toBe(0);
    expect(
      warn.mock.calls.some((c) => String(c[0]).includes("2026-03"))
    ).toBe(true);

    warn.mockRestore();
  });

  it("maps vehicleNo → vehicleId and account name → accountItemId with numeric amounts", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-ok",
      spreadsheetRevenueSheet: "2026-03",
      code: null,
      name: "Test",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-ok",
      name: "損益計算資料2026.03.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "001-001", course: null },
      { id: "v2", vehicleNo: "002-002", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
      { id: "a2", name: "ヤマザキ物流" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["2026-03"]);
    readXlsxMock.mockResolvedValueOnce([
      ["vehicleNo", "山崎製パン", "ヤマザキ物流"],
      ["001-001", 1000, 200],
      ["002-002", 50, 300],
    ]);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.get("v1-a1")).toBe(1000);
    expect(map.get("v1-a2")).toBe(200);
    expect(map.get("v2-a1")).toBe(50);
    expect(map.get("v2-a2")).toBe(300);
  });

  it('parses comma amounts like "1,500,000"', async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-csv",
      spreadsheetRevenueSheet: "2026-03",
      code: null,
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "001-001", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["2026-03"]);
    readXlsxMock.mockResolvedValueOnce([
      ["vehicleNo", "山崎製パン"],
      ["001-001", "1,500,000"],
    ]);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.get("v1-a1")).toBe(1_500_000);
  });

  it("skips rows whose vehicleNo is not mapped in DB", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-skip",
      spreadsheetRevenueSheet: "2026-03",
      code: null,
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "001-001", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["2026-03"]);
    readXlsxMock.mockResolvedValueOnce([
      ["vehicleNo", "山崎製パン"],
      ["unknown-no", "500"],
      ["001-001", 42],
    ]);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.size).toBe(1);
    expect(map.get("v1-a1")).toBe(42);
  });

  it("returns empty Map when vehicleIds filter is empty", async () => {
    const map = await getRevenueFromSpreadsheets({
      ...baseParams,
      vehicleIds: [],
    });
    expect(map.size).toBe(0);
    expect(prismaMock.location.findUnique).not.toHaveBeenCalled();
  });

  it("uses spreadsheetRevenueSheet to select a YYYY.MM-style tab", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-dot",
      spreadsheetRevenueSheet: "2026.02",
      code: null,
      name: "Test",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-dot",
      name: "損益計算資料2026.02.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", vehicleNo: "18-16", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["2026.02"]);
    readXlsxMock.mockResolvedValueOnce([
      ["vehicleNo", "山崎製パン"],
      ["18-16", 777],
    ]);

    const map = await getRevenueFromSpreadsheets({
      ...baseParams,
      yearMonth: "2026-02",
      vehicleIds: ["v1"],
      revenueAccountItemIds: ["a1"],
    });

    expect(map.get("v1-a1")).toBe(777);
  });

  it("parses Izumi-style 車両別損益 pivot (4-digit vehicle row → YY-YY)", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-piv",
      spreadsheetRevenueSheet: "車両別損益",
      code: "LOC001",
      name: "Test",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-piv",
      name: "損益計算資料2026.02.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "vx", vehicleNo: "18-16", course: { name: "c1" } },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["車両別損益"]);
    readXlsxMock.mockResolvedValueOnce([
      ["名古屋営業所"],
      ["業態コース", 1816, null],
      ["車両No", 9654, "（％）"],
      ["山崎製パン", 100, 0.31],
    ]);

    const map = await getRevenueFromSpreadsheets({
      locationId: "loc-1",
      yearMonth: "2026-02",
      vehicleIds: ["vx"],
      revenueAccountItemIds: ["a1"],
    });

    expect(map.get("vx-a1")).toBe(100);
  });

  it("infers 015-034 from Izumi column 18-34 for LOC015 (seed vehicleNo convention)", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "f-loc",
      spreadsheetRevenueSheet: "車両別損益",
      code: "LOC015",
      name: "名古屋",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "f-loc",
      name: "損益計算資料2026.02.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v034", vehicleNo: "015-034", course: { name: "test" } },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "a1", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["車両別損益"]);
    readXlsxMock.mockResolvedValueOnce([
      ["名古屋営業所"],
      ["業態コース", "18-34", null],
      ["車両No", 7710, "（％）"],
      ["山崎製パン", 500_000, 1],
    ]);

    const map = await getRevenueFromSpreadsheets({
      locationId: "loc-nagoya",
      yearMonth: "2026-02",
      vehicleIds: ["v034"],
      revenueAccountItemIds: ["a1"],
    });

    expect(map.get("v034-a1")).toBe(500_000);
  });

  it("maps abbreviated サンロジ row to サンロジスティックス", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "f-alias",
      spreadsheetRevenueSheet: "車両別損益",
      code: "LOC015",
      name: "名古屋",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "f-alias",
      name: "損益計算資料2026.02.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v10", vehicleNo: "015-010", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "aSan", name: "サンロジスティックス" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["車両別損益"]);
    readXlsxMock.mockResolvedValueOnce([
      ["x"],
      ["業態", "18-10", null],
      ["車両No", 9654, "（％）"],
      ["サンロジ", 42, 0],
    ]);

    const map = await getRevenueFromSpreadsheets({
      locationId: "loc-1",
      yearMonth: "2026-02",
      vehicleIds: ["v10"],
      revenueAccountItemIds: ["aSan"],
    });

    expect(map.get("v10-aSan")).toBe(42);
  });

  it("parses 売上明細 sheet: 月額 columns under 山パン / エコー blocks", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-urimei",
      spreadsheetRevenueSheet: "売上明細",
      code: "LOC015",
      name: "名古屋",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-urimei",
      name: "損益計算資料2026.02.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v16", vehicleNo: "015-016", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "aYama", name: "山崎製パン" },
      { id: "aEco", name: "富士エコー" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["売上明細"]);
    readXlsxMock.mockResolvedValueOnce([
      [],
      [],
      [],
      [
        "山パン",
        "運賃",
        "日額損益用",
        "高速",
        "日数",
        "高速代",
        "月額",
        "",
        "エコー",
        "運賃",
        "日額損益用",
        "高速",
        "日数",
        "時間加算",
        "距離加算",
        "月額",
      ],
      ["18-16", 1, 0, 0, 0, 0, 900_000, "", 1, 0, 0, 0, 0, 0, 0, 50_000],
    ]);

    const map = await getRevenueFromSpreadsheets({
      locationId: "loc-1",
      yearMonth: "2026-02",
      vehicleIds: ["v16"],
      revenueAccountItemIds: ["aYama", "aEco"],
    });

    expect(map.get("v16-aYama")).toBe(900_000);
    expect(map.get("v16-aEco")).toBe(50_000);
  });

  it("auto-resolves Drive file when spreadsheetId unset and list matches 拠点名+年月", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: null,
      spreadsheetRevenueSheet: null,
      code: "LOC015",
      name: "名古屋",
    });
    listPlFilesMock.mockResolvedValue([
      { id: "auto-pl-file", name: "13(名古屋)損益計算資料2026.03.xlsx" },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v16", vehicleNo: "015-016", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "aYama", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["売上明細", "損益計算書"]);
    readXlsxMock.mockResolvedValueOnce([
      [],
      [],
      [],
      [
        "山パン",
        "運賃",
        "日額損益用",
        "高速",
        "日数",
        "高速代",
        "月額",
        "",
        "エコー",
        "運賃",
        "日額損益用",
        "高速",
        "日数",
        "時間加算",
        "距離加算",
        "月額",
      ],
      ["18-16", 1, 0, 0, 0, 0, 123_000, "", 1, 0, 0, 0, 0, 0, 0, 50_000],
    ]);

    const map = await getRevenueFromSpreadsheets({
      locationId: "loc-auto",
      yearMonth: "2026-03",
      vehicleIds: ["v16"],
      revenueAccountItemIds: ["aYama"],
    });

    expect(listPlFilesMock).toHaveBeenCalled();
    expect(downloadMock).toHaveBeenCalledWith("auto-pl-file");
    expect(map.get("v16-aYama")).toBe(123_000);
  });

  it("prefers 売上明細 over 車両別損益 when both exist (PM default sheet)", async () => {
    prismaMock.location.findUnique.mockResolvedValue({
      spreadsheetId: "file-both-tabs",
      spreadsheetRevenueSheet: null,
      code: "LOC015",
      name: "名古屋",
    });
    getDriveFileMetaMock.mockResolvedValue({
      id: "file-both-tabs",
      name: "損益計算資料2026.02.xlsx",
    });
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v16", vehicleNo: "015-016", course: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "aYama", name: "山崎製パン" },
    ]);
    downloadMock.mockResolvedValue(Buffer.from([1]));
    readSheetNamesMock.mockResolvedValueOnce(["車両別損益", "売上明細"]);
    readXlsxMock.mockResolvedValueOnce([
      [],
      [],
      [],
      [
        "山パン",
        "運賃",
        "日額損益用",
        "高速",
        "日数",
        "高速代",
        "月額",
        "",
        "エコー",
        "運賃",
        "日額損益用",
        "高速",
        "日数",
        "時間加算",
        "距離加算",
        "月額",
      ],
      ["18-16", 1, 0, 0, 0, 0, 444_444, "", 1, 0, 0, 0, 0, 0, 0, 1],
    ]);

    const map = await getRevenueFromSpreadsheets({
      locationId: "loc-both",
      yearMonth: "2026-02",
      vehicleIds: ["v16"],
      revenueAccountItemIds: ["aYama"],
    });

    expect(map.get("v16-aYama")).toBe(444_444);
  });

  it("uses DB snapshot when LocationDriveSyncMeta is success (no Drive download)", async () => {
    prismaMock.locationDriveSyncMeta.findUnique.mockResolvedValue({
      status: "success",
    });
    prismaMock.driveSpreadsheetRevenueLine.findMany.mockResolvedValue([
      {
        vehicleId: "v1",
        accountItemId: "a1",
        amount: 99,
      },
    ]);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.get("v1-a1")).toBe(99);
    expect(downloadMock).not.toHaveBeenCalled();
    expect(prismaMock.driveSpreadsheetRevenueLine.findMany).toHaveBeenCalled();
  });

  it("SPREADSHEET_REVENUE_SNAPSHOT_ONLY skips Drive when no success meta", async () => {
    vi.stubEnv("SPREADSHEET_REVENUE_SNAPSHOT_ONLY", "true");
    prismaMock.locationDriveSyncMeta.findUnique.mockResolvedValue(null);

    const map = await getRevenueFromSpreadsheets(baseParams);

    expect(map.size).toBe(0);
    expect(downloadMock).not.toHaveBeenCalled();
    vi.unstubAllEnvs();
  });
});

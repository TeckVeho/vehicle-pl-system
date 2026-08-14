import { describe, expect, it } from "vitest";
import {
  fileMatchesLocationPlTemplate,
  fileNameIncludesYearMonth,
  findSpreadsheetForLocation,
} from "./spreadsheet-file-match";

describe("fileNameIncludesYearMonth", () => {
  it("matches YYYY-MM in filename", () => {
    expect(fileNameIncludesYearMonth("損益計算資料_東京_2026-03.xlsx", "2026-03")).toBe(true);
  });

  it("matches unpadded month variant", () => {
    expect(fileNameIncludesYearMonth("損益計算資料_2026.3.xlsx", "2026-03")).toBe(true);
  });

  it("does not match partial year month (2026.02 vs 2026.20)", () => {
    expect(fileNameIncludesYearMonth("損益計算資料_2026.20.xlsx", "2026-02")).toBe(false);
  });

  it("returns false for invalid yearMonth", () => {
    expect(fileNameIncludesYearMonth("損益計算資料_2026.03.xlsx", "invalid")).toBe(false);
  });
});

describe("fileMatchesLocationPlTemplate", () => {
  it("requires marker, location name, and year month", () => {
    expect(
      fileMatchesLocationPlTemplate("損益計算資料_東京_2026-03", "東京", "2026-03")
    ).toBe(true);
    expect(fileMatchesLocationPlTemplate("東京_2026-03", "東京", "2026-03")).toBe(false);
  });
});

describe("findSpreadsheetForLocation", () => {
  it("returns first match sorted by Japanese locale", () => {
    const spreadsheets = [
      { id: "b", name: "損益計算資料_東京_2026-03_B" },
      { id: "a", name: "損益計算資料_東京_2026-03_A" },
    ];
    const match = findSpreadsheetForLocation(spreadsheets, "東京", "2026-03");
    expect(match?.id).toBe("a");
  });

  it("returns undefined when no match", () => {
    expect(findSpreadsheetForLocation([], "東京", "2026-03")).toBeUndefined();
  });
});

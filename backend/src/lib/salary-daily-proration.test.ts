import { describe, expect, it } from "vitest";
import {
  getPreviousYearMonth,
  getSalaryDailyAmount,
  isSalaryDailyProrationAccount,
} from "./salary-daily-proration.js";

describe("getPreviousYearMonth", () => {
  it("returns previous month in same year", () => {
    expect(getPreviousYearMonth("2026-03")).toBe("2026-02");
  });

  it("rolls from January to December of prior year", () => {
    expect(getPreviousYearMonth("2026-01")).toBe("2025-12");
  });
});

describe("getSalaryDailyAmount", () => {
  it("uses run-count split when totalRunCount > 0 and runCount > 0", () => {
    // monthly 30000, 10 runs in month, 3 runs this day -> 9000
    expect(getSalaryDailyAmount(30000, 10, 3, 31)).toBe(9000);
  });

  it("falls back to equal daily split when totalRunCount is 0", () => {
    expect(getSalaryDailyAmount(31000, 0, 0, 31)).toBe(1000);
  });

  it("returns 0 when monthly amount is 0", () => {
    expect(getSalaryDailyAmount(0, 10, 3, 31)).toBe(0);
  });

  it("rounds to 2 decimal places", () => {
    expect(getSalaryDailyAmount(100, 3, 1, 30)).toBeCloseTo(33.33, 2);
  });
});

describe("isSalaryDailyProrationAccount", () => {
  it("returns true for salary codes", () => {
    expect(isSalaryDailyProrationAccount("6138")).toBe(true);
    expect(isSalaryDailyProrationAccount("6147")).toBe(true);
  });

  it("returns false for other codes", () => {
    expect(isSalaryDailyProrationAccount("6139")).toBe(false);
    expect(isSalaryDailyProrationAccount("6150")).toBe(false);
  });
});

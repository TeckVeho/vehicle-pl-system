import { describe, expect, it } from "vitest";
import {
  getMaxMonth,
  getMonths,
  getYears,
  parseYearMonth,
  toYearMonth,
} from "@/stores/yearMonthStore";

describe("yearMonthStore helpers", () => {
  it("getYears returns descending list including current year", () => {
    const years = getYears();
    const currentYear = new Date().getFullYear();
    expect(years[0]).toBe(currentYear);
    expect(years).toHaveLength(5);
    expect(years[0]).toBeGreaterThan(years[1]!);
  });

  it("getMonths returns 12 months for a past year", () => {
    expect(getMonths(2020)).toHaveLength(12);
    expect(getMonths(2020)[0]).toBe(1);
    expect(getMonths(2020)[11]).toBe(12);
  });

  it("getMaxMonth caps months for current year", () => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    expect(getMaxMonth(currentYear)).toBe(Math.min(currentMonth + 1, 12));
    expect(getMaxMonth(2020)).toBe(12);
  });

  it("converts between yearMonth string and parts", () => {
    expect(toYearMonth(2026, 3)).toBe("2026-03");
    expect(parseYearMonth("2026-03")).toEqual({ year: 2026, month: 3 });
  });
});

import { describe, expect, it } from "vitest";
import {
  calcNetRevenue,
  calcSalesRatio,
  calcTotalExpense,
  getCategoryLabel,
  isExpenseItem,
  isRevenueItem,
  isSubtotalItem,
  REVENUE_CATEGORY,
} from "./calc.js";

describe("calcNetRevenue / calcTotalExpense", () => {
  it("sums only ids present in the set", () => {
    const m = new Map([
      ["a", 100],
      ["b", 50],
      ["c", 25],
    ]);
    expect(calcNetRevenue(m, new Set(["a", "c"]))).toBe(125);
    expect(calcTotalExpense(m, new Set(["b"]))).toBe(50);
  });

  it("returns 0 for empty map", () => {
    expect(calcNetRevenue(new Map(), new Set(["x"]))).toBe(0);
  });
});

describe("calcSalesRatio", () => {
  it("returns 0 when net revenue is 0", () => {
    expect(calcSalesRatio(100, 0)).toBe(0);
  });

  it("returns percentage of net revenue", () => {
    expect(calcSalesRatio(25, 100)).toBe(25);
  });
});

describe("category helpers", () => {
  it("detects revenue and expense items", () => {
    expect(isRevenueItem({ category: REVENUE_CATEGORY })).toBe(true);
    expect(isExpenseItem({ category: "expense" })).toBe(true);
  });

  it("detects subtotal rows", () => {
    expect(isSubtotalItem({ isSubtotal: true })).toBe(true);
  });

  it("getCategoryLabel returns Japanese labels", () => {
    expect(getCategoryLabel("revenue")).toBe("売上");
    expect(getCategoryLabel("expense")).toBe("原価");
    expect(getCategoryLabel("unknown")).toBe("");
  });
});

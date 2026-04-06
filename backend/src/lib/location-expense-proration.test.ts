import { describe, expect, it } from "vitest";
import { isLocationExpenseProrationAccount } from "./location-expense-proration.js";

describe("isLocationExpenseProrationAccount", () => {
  it("returns true for known location expense codes", () => {
    expect(isLocationExpenseProrationAccount("6150")).toBe(true);
    expect(isLocationExpenseProrationAccount("6173")).toBe(true);
  });

  it("returns false for codes outside the list", () => {
    expect(isLocationExpenseProrationAccount("6138")).toBe(false);
    expect(isLocationExpenseProrationAccount("9999")).toBe(false);
  });
});

import { describe, expect, it } from "vitest";
import { accountItemEffectiveWhere } from "./account-item-filter.js";

describe("accountItemEffectiveWhere", () => {
  it("returns OR of four effective-range patterns for a year-month string", () => {
    const w = accountItemEffectiveWhere("2026-03");
    expect(w).toEqual({
      OR: [
        { effectiveFrom: null, effectiveTo: null },
        { effectiveFrom: null, effectiveTo: { gte: "2026-03" } },
        { effectiveFrom: { lte: "2026-03" }, effectiveTo: null },
        {
          effectiveFrom: { lte: "2026-03" },
          effectiveTo: { gte: "2026-03" },
        },
      ],
    });
  });
});

import { describe, expect, it } from "vitest";
import { formatSyncType, SYNC_TYPE_LABELS } from "./sync-type-labels";

describe("formatSyncType", () => {
  it("maps known sync types to Japanese labels", () => {
    expect(SYNC_TYPE_LABELS.atmtc_transactions).toBe("ATMTC配送連携");
    expect(formatSyncType("atmtc_transactions")).toBe("ATMTC配送連携");
  });

  it("maps all defined sync types", () => {
    expect(formatSyncType("monthly_records")).toBe("月次損益データ");
    expect(formatSyncType("daily_operating")).toBe("日次稼働");
    expect(formatSyncType("driver_assignments")).toBe("乗務記録（タイムシート）");
    expect(formatSyncType("spreadsheet_revenue")).toBe("Drive 売上スナップショット");
    expect(formatSyncType("users")).toBe("ユーザー");
  });

  it("returns raw type for unknown keys", () => {
    expect(formatSyncType("unknown_type")).toBe("unknown_type");
  });
});

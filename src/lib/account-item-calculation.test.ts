import { describe, expect, it } from "vitest";
import { getCalculationLogic } from "./account-item-calculation";

describe("getCalculationLogic", () => {
  it("returns subtotal logic for subtotal items", () => {
    expect(
      getCalculationLogic({
        code: "SUBTOTAL_REV",
        name: "純売上高",
        category: "subtotal_revenue",
        isSubtotal: true,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "集計", detail: "売上科目（revenue）の合計" });
  });

  it("returns Izumi cloud linkage for vehicle cost codes", () => {
    expect(
      getCalculationLogic({
        code: "6191",
        name: "リース車償却",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "イズミクラウド連携" });
  });

  it("returns manual-only for each manual revenue name", () => {
    for (const name of ["その他", "不動産収入", "人材派遣収入"]) {
      expect(
        getCalculationLogic({
          code: "5010",
          name,
          category: "revenue",
          isSubtotal: false,
          isDriverRelated: false,
        }).method
      ).toBe("手入力のみ");
    }
  });

  it("returns spreadsheet reference for standard revenue", () => {
    expect(
      getCalculationLogic({
        code: "5010",
        name: "山崎製パン",
        category: "revenue",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "スプレッドシート参照" });
  });

  it("returns location expense proration for proration codes", () => {
    expect(
      getCalculationLogic({
        code: "6150",
        name: "旅費交通地",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "拠点別経費連携" });
  });

  it("returns driver allocation for driver-related items", () => {
    expect(
      getCalculationLogic({
        code: "6138",
        name: "乗務員給料",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: true,
      })
    ).toMatchObject({ method: "ドライバー配賦" });
  });

  it("returns manual input for imported salary items", () => {
    expect(
      getCalculationLogic({
        code: "6139",
        name: "業務給料",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "手動入力" });
  });

  it("returns ITP calculation for fuel and road usage", () => {
    expect(
      getCalculationLogic({
        code: "6175",
        name: "燃料費",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "ITP連携＋計算" });

    expect(
      getCalculationLogic({
        code: "6176",
        name: "道路使用料",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "ITP連携＋計算" });
  });

  it("returns default manual input for other expense items", () => {
    expect(
      getCalculationLogic({
        code: "6190",
        name: "車両修繕費",
        category: "expense",
        isSubtotal: false,
        isDriverRelated: false,
      })
    ).toMatchObject({ method: "手動入力" });
  });

  it("returns gross subtotal logic", () => {
    expect(
      getCalculationLogic({
        code: "SUBTOTAL_GROSS",
        name: "自車粗利益",
        category: "subtotal_gross",
        isSubtotal: true,
        isDriverRelated: false,
      })
    ).toMatchObject({ detail: "純売上高 − 自車原価計" });
  });
});

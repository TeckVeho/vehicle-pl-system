"use client";

import React, { useMemo, useRef, useState, useEffect } from "react";
import { ChevronRight } from "lucide-react";
import {
  isRevenueItem,
  isExpenseItem,
  calcNetRevenue,
  calcTotalExpense,
  calcSalesRatio,
  getCategoryLabel,
  REVENUE_CATEGORY,
  EXPENSE_CATEGORY,
} from "@/lib/calc";
import { formatCurrency, formatPercent } from "@/lib/utils";
import { EditableCell } from "./EditableCell";

interface AccountItem {
  id: string;
  code: string;
  name: string;
  category: string;
  sortOrder: number;
  isSubtotal: boolean;
}

interface Vehicle {
  id: string;
  vehicleNo: string;
  serviceType: string | null;
  location: { id: string; code: string; name: string };
  course?: { id: string; name: string; code: string } | null;
}

export type DisplayMode = "course" | "vehicle";

/** コース単位の集計グループ */
interface CourseGroup {
  id: string;
  name: string;
  vehicleIds: string[];
}

function formatVehicleDisplay(v: Vehicle) {
  const courseName = v.course?.name ?? v.vehicleNo;
  const digitsOnly = v.vehicleNo.replace(/\D/g, "");
  const last4 = digitsOnly.slice(-4) || v.vehicleNo;
  const serviceLine = v.serviceType ? `${v.serviceType}（${last4}）` : `（${last4}）`;
  return { courseName, serviceLine };
}

interface PLTableProps {
  accountItems: AccountItem[];
  vehicles: Vehicle[];
  records: Record<string, number>;
  yearMonth: string;
  displayMode: DisplayMode;
  editMode?: boolean;
  onUpdateRecord: (
    vehicleId: string,
    accountItemId: string,
    amount: number
  ) => Promise<void>;
}

export function PLTable({
  accountItems,
  vehicles,
  records,
  yearMonth,
  displayMode,
  editMode = false,
  onUpdateRecord,
}: PLTableProps) {
  const revenueItemIds = useMemo(
    () => new Set(accountItems.filter(isRevenueItem).map((a) => a.id)),
    [accountItems]
  );
  const expenseItemIds = useMemo(
    () => new Set(accountItems.filter(isExpenseItem).map((a) => a.id)),
    [accountItems]
  );

  const getAmount = (vehicleId: string, accountItemId: string) => {
    return records[`${vehicleId}-${accountItemId}`] ?? 0;
  };

  /** コースごとに車両をグループ化（コース未割当は「コースなし」に集約） */
  const courseGroups = useMemo((): CourseGroup[] => {
    const courseMap = new Map<string, CourseGroup>();
    const NO_COURSE_KEY = "__no_course__";
    for (const v of vehicles) {
      const key = v.course?.id ?? NO_COURSE_KEY;
      const name = v.course?.name ?? "コースなし";
      if (!courseMap.has(key)) {
        courseMap.set(key, { id: key, name, vehicleIds: [] });
      }
      courseMap.get(key)!.vehicleIds.push(v.id);
    }
    return Array.from(courseMap.values());
  }, [vehicles]);

  const getVehicleAmounts = (vehicleId: string) => {
    const amountByItem = new Map<string, number>();
    for (const item of accountItems) {
      const amt = getAmount(vehicleId, item.id);
      amountByItem.set(item.id, amt);
    }
    const netRevenue = calcNetRevenue(amountByItem, revenueItemIds);
    const totalExpense = calcTotalExpense(amountByItem, expenseItemIds);
    const grossProfit = netRevenue - totalExpense;
    return { netRevenue, totalExpense, grossProfit, amountByItem };
  };

  /** コースグループの合計金額 */
  const getCourseGroupAmount = (group: CourseGroup, accountItemId: string) => {
    return group.vehicleIds.reduce(
      (sum, vid) => sum + getAmount(vid, accountItemId),
      0
    );
  };

  /** コースグループの集計（amountByItem） */
  const getCourseGroupAmounts = (group: CourseGroup) => {
    const amountByItem = new Map<string, number>();
    for (const item of accountItems) {
      amountByItem.set(item.id, getCourseGroupAmount(group, item.id));
    }
    const netRevenue = calcNetRevenue(amountByItem, revenueItemIds);
    const totalExpense = calcTotalExpense(amountByItem, expenseItemIds);
    const grossProfit = netRevenue - totalExpense;
    return { netRevenue, totalExpense, grossProfit, amountByItem };
  };

  const getCellValue = (
    vehicleId: string,
    item: AccountItem,
    computed: { netRevenue: number; totalExpense: number; grossProfit: number }
  ): number => {
    if (item.category === "subtotal_revenue") return computed.netRevenue;
    if (item.category === "subtotal_expense") return computed.totalExpense;
    if (item.category === "subtotal_gross") return computed.grossProfit;
    if (item.category === "summary") {
      if (item.code === "SUMMARY_REV") return computed.netRevenue;
      if (item.code === "SUMMARY_EXP") return computed.totalExpense;
      if (item.code === "SUMMARY_GROSS") return computed.grossProfit;
    }
    return getAmount(vehicleId, item.id);
  };

  const getCourseCellValue = (
    group: CourseGroup,
    item: AccountItem,
    computed: { netRevenue: number; totalExpense: number; grossProfit: number }
  ): number => {
    if (item.category === "subtotal_revenue") return computed.netRevenue;
    if (item.category === "subtotal_expense") return computed.totalExpense;
    if (item.category === "subtotal_gross") return computed.grossProfit;
    if (item.category === "summary") {
      if (item.code === "SUMMARY_REV") return computed.netRevenue;
      if (item.code === "SUMMARY_EXP") return computed.totalExpense;
      if (item.code === "SUMMARY_GROSS") return computed.grossProfit;
    }
    return getCourseGroupAmount(group, item.id);
  };

  const isSubtotalRow = (item: AccountItem) => item.isSubtotal;

  const columns = displayMode === "course" ? courseGroups : vehicles;
  const canEdit = displayMode === "vehicle" && editMode;

  const scrollRef = useRef<HTMLDivElement>(null);
  const [canScrollRight, setCanScrollRight] = useState(false);

  const updateScrollState = () => {
    const el = scrollRef.current;
    if (!el) return;
    const right = el.scrollLeft + el.clientWidth < el.scrollWidth - 1;
    setCanScrollRight(right);
  };

  useEffect(() => {
    const el = scrollRef.current;
    if (!el) return;
    updateScrollState();
    el.addEventListener("scroll", updateScrollState);
    const ro = new ResizeObserver(updateScrollState);
    ro.observe(el);
    return () => {
      el.removeEventListener("scroll", updateScrollState);
      ro.disconnect();
    };
  }, [columns, accountItems]);

  const scrollRight = () => {
    scrollRef.current?.scrollBy({ left: 300, behavior: "smooth" });
  };

  return (
    <div className="relative">
      <div
        ref={scrollRef}
        className="overflow-auto max-h-[calc(100dvh-13rem)]"
      >
        <table className="w-full min-w-[800px] text-sm border-separate border-spacing-0 border border-excel-grid [&_th]:border-excel-grid [&_td]:border-excel-grid">
        <thead>
          <tr className="border-b border-excel-grid bg-muted">
            <th className="sticky left-0 top-0 z-30 bg-muted py-2 px-2 text-left text-xs font-medium text-foreground w-[72px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              区分
            </th>
            <th className="sticky left-[72px] top-0 z-30 bg-muted py-2 px-3 text-left text-xs font-medium text-foreground w-[80px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              Code
            </th>
            <th className="sticky left-[152px] top-0 z-30 bg-muted py-2 px-3 text-left text-xs font-medium text-foreground w-[200px] max-w-[200px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              勘定科目
            </th>
            {displayMode === "course"
              ? courseGroups.map((g) => {
                  const last4List = g.vehicleIds
                    .map((vid) => {
                      const v = vehicles.find((x) => x.id === vid);
                      const digits = v?.vehicleNo.replace(/\D/g, "") ?? "";
                      return (digits.slice(-4) || v?.vehicleNo) ?? "-";
                    })
                    .join(", ");
                  return (
                    <th
                      key={g.id}
                      colSpan={2}
                      className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px] border-r border-excel-grid"
                    >
                      <div className="flex flex-col items-center gap-0.5">
                        <span>{g.name}</span>
                        <span
                          className="text-muted-foreground font-normal text-[10px] cursor-help"
                          title={last4List}
                        >
                          {g.vehicleIds.length}台
                        </span>
                      </div>
                    </th>
                  );
                })
              : vehicles.map((v) => {
                  const { courseName, serviceLine } = formatVehicleDisplay(v);
                  return (
                    <th
                      key={v.id}
                      colSpan={2}
                      className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px] border-r border-excel-grid"
                    >
                      <div className="flex flex-col items-center gap-0.5">
                        <span>{courseName}</span>
                        <span className="text-muted-foreground font-normal text-[10px]">{serviceLine}</span>
                      </div>
                    </th>
                  );
                })}
            <th className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px]">
              合計
            </th>
          </tr>
          <tr className="border-b border-excel-grid bg-muted">
            <th className="sticky left-0 top-[3.25rem] z-30 bg-muted py-1 px-2 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
            <th className="sticky left-[72px] top-[3.25rem] z-30 bg-muted py-1 px-3 w-[80px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
            <th className="sticky left-[152px] top-[3.25rem] z-30 bg-muted py-1 px-3 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
            {displayMode === "course"
              ? courseGroups.flatMap((g) => [
                  <th
                    key={`${g.id}-m`}
                    className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground w-[90px] border-r border-excel-grid"
                  >
                    月間
                  </th>,
                  <th
                    key={`${g.id}-p`}
                    className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground w-[90px] border-r border-excel-grid"
                  >
                    売上比(%)
                  </th>,
                ])
              : vehicles.flatMap((v) => [
                  <th
                    key={`${v.id}-m`}
                    className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground w-[90px] border-r border-excel-grid"
                  >
                    月間
                  </th>,
                  <th
                    key={`${v.id}-p`}
                    className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground w-[90px] border-r border-excel-grid"
                  >
                    売上比(%)
                  </th>,
                ])}
            <th colSpan={2} className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground">
              月間 / 売上比(%)
            </th>
          </tr>
        </thead>
        <tbody>
          {accountItems.map((item) => {
            const subtotal = isSubtotalRow(item);
            const stickyBg = subtotal ? "bg-muted" : "bg-background";
            return (
              <tr
                key={item.id}
                className={`group border-b border-excel-grid transition-colors ${
                  subtotal
                    ? "bg-muted"
                    : "bg-background hover:bg-muted/50"
                }`}
              >
                <td className={`sticky left-0 z-20 py-2 px-2 text-sm overflow-hidden w-[72px] border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}>
                  <span
                    className={`inline-flex items-center rounded px-2 py-0.5 text-xs font-medium whitespace-nowrap ${
                      item.category === REVENUE_CATEGORY
                        ? "bg-emerald-100 text-emerald-800"
                        : item.category === EXPENSE_CATEGORY
                          ? "bg-amber-100 text-amber-800"
                          : "bg-slate-200 text-slate-700"
                    }`}
                  >
                    {getCategoryLabel(item.category)}
                  </span>
                </td>
                <td className={`sticky left-[72px] z-20 py-2 px-3 text-sm text-muted-foreground overflow-hidden w-[80px] border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}>
                  <span className="block truncate">{subtotal ? "-" : item.code}</span>
                </td>
                <td className={`sticky left-[152px] z-20 py-2 px-3 text-sm w-[200px] max-w-[200px] overflow-hidden border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)] ${subtotal ? "font-semibold" : "font-medium"}`}>
                  <span className="block truncate" title={item.name}>{item.name}</span>
                </td>
                {displayMode === "course"
                  ? courseGroups.map((g) => {
                      const computed = getCourseGroupAmounts(g);
                      const value = getCourseCellValue(g, item, computed);
                      const salesRatio = calcSalesRatio(value, computed.netRevenue);
                      return (
                        <React.Fragment key={g.id}>
                          <td className={`py-0 px-0 w-[90px] border-r border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                            <div className={`py-2 px-3 text-right ${subtotal ? "font-semibold" : "font-medium"}`}>
                              {formatCurrency(value)}
                            </div>
                          </td>
                          <td className={`py-2 px-3 text-right w-[90px] text-muted-foreground border-r border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                            {formatPercent(salesRatio)}
                          </td>
                        </React.Fragment>
                      );
                    })
                  : vehicles.map((v) => {
                      const computed = getVehicleAmounts(v.id);
                      const value = getCellValue(v.id, item, computed);
                      const salesRatio = calcSalesRatio(value, computed.netRevenue);
                      return (
                        <React.Fragment key={v.id}>
                          <td className={`py-0 px-0 w-[90px] border-r border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                            {subtotal ? (
                              <div className="py-2 px-3 text-right font-semibold">
                                {formatCurrency(value)}
                              </div>
                            ) : (
                              <EditableCell
                                value={value}
                                editMode={canEdit}
                                onSave={(amt) =>
                                  onUpdateRecord(v.id, item.id, amt)
                                }
                              />
                            )}
                          </td>
                          <td className={`py-2 px-3 text-right w-[90px] text-muted-foreground border-r border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                            {formatPercent(salesRatio)}
                          </td>
                        </React.Fragment>
                      );
                    })}
                <td className={`py-2 px-3 text-right border-l border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                  {(() => {
                    let total = 0;
                    let totalNetRevenue = 0;
                    if (displayMode === "course") {
                      for (const g of courseGroups) {
                        const computed = getCourseGroupAmounts(g);
                        const val = getCourseCellValue(g, item, computed);
                        total += val;
                        totalNetRevenue += computed.netRevenue;
                      }
                    } else {
                      for (const v of vehicles) {
                        const computed = getVehicleAmounts(v.id);
                        const val = getCellValue(v.id, item, computed);
                        total += val;
                        totalNetRevenue += computed.netRevenue;
                      }
                    }
                    const totalRatio = calcSalesRatio(total, totalNetRevenue);
                    return (
                      <>
                        <div className={subtotal ? "font-semibold" : "font-medium"}>
                          {formatCurrency(total)}
                        </div>
                        <div className="text-xs text-muted-foreground">
                          {formatPercent(totalRatio)}
                        </div>
                      </>
                    );
                  })()}
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>
      </div>
      {canScrollRight && (
        <div className="absolute right-0 top-0 bottom-0 w-16 flex items-center justify-end pointer-events-none">
          <div className="absolute inset-0 bg-gradient-to-l from-background/60 to-transparent" />
          <button
            type="button"
            onClick={scrollRight}
            className="relative z-10 pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-background/80 shadow-md backdrop-blur hover:bg-background/90 transition-colors"
            aria-label="右にスクロール"
          >
            <ChevronRight className="h-5 w-5 text-foreground" />
          </button>
        </div>
      )}
    </div>
  );
}

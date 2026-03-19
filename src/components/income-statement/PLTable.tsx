"use client";

import React, { useMemo, useRef, useState, useEffect } from "react";
import { ChevronRight, CheckCircle2, Circle } from "lucide-react";
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
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { EditableCell, type ArrowDirection } from "./EditableCell";

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
  const courseName = v.course?.name ?? "コースなし";
  const digitsOnly = v.vehicleNo.replace(/\D/g, "");
  const last4 = digitsOnly.slice(-4) || v.vehicleNo;
  const serviceLine = v.serviceType ? `${v.serviceType}（${last4}）` : `（${last4}）`;
  const courseCount = v.course ? 1 : 0;
  return { courseName, serviceLine, courseCount };
}

interface PLTableProps {
  accountItems: AccountItem[];
  vehicles: Vehicle[];
  records: Record<string, number>;
  yearMonth: string;
  displayMode: DisplayMode;
  editMode?: boolean;
  /** 勘定科目ごとの登録状況（accountItemId -> 登録済み） */
  importStatus?: Record<string, boolean>;
  onUpdateRecord: (
    vehicleId: string,
    accountItemId: string,
    amount: number
  ) => Promise<void>;
  onUpdateCourseRecord?: (
    vehicleIds: string[],
    accountItemId: string,
    totalAmount: number
  ) => Promise<void>;
}

function PLTableInner({
  accountItems,
  vehicles,
  records,
  yearMonth,
  displayMode,
  editMode = false,
  importStatus = {},
  onUpdateRecord,
  onUpdateCourseRecord,
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

  /** 車両ID→車両のO(1)ルックアップ用Map */
  const vehicleMap = useMemo(
    () => new Map(vehicles.map((v) => [v.id, v])),
    [vehicles]
  );

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

  type ComputedAmounts = {
    netRevenue: number;
    totalExpense: number;
    grossProfit: number;
    amountByItem: Map<string, number>;
  };

  /** 車両・コース・合計列の計算を事前にメモ化（vehicles/accountItems/records変更時のみ再計算） */
  const { vehicleAmountsMap, courseGroupAmountsMap, totalByItemMap } = useMemo(() => {
    const vehicleMap = new Map<string, ComputedAmounts>();
    for (const v of vehicles) {
      const amountByItem = new Map<string, number>();
      for (const item of accountItems) {
        amountByItem.set(item.id, getAmount(v.id, item.id));
      }
      const netRevenue = calcNetRevenue(amountByItem, revenueItemIds);
      const totalExpense = calcTotalExpense(amountByItem, expenseItemIds);
      vehicleMap.set(v.id, {
        netRevenue,
        totalExpense,
        grossProfit: netRevenue - totalExpense,
        amountByItem,
      });
    }

    const courseMap = new Map<string, ComputedAmounts>();
    for (const g of courseGroups) {
      const amountByItem = new Map<string, number>();
      for (const item of accountItems) {
        const sum = g.vehicleIds.reduce(
          (s, vid) => s + (records[`${vid}-${item.id}`] ?? 0),
          0
        );
        amountByItem.set(item.id, sum);
      }
      const netRevenue = calcNetRevenue(amountByItem, revenueItemIds);
      const totalExpense = calcTotalExpense(amountByItem, expenseItemIds);
      courseMap.set(g.id, {
        netRevenue,
        totalExpense,
        grossProfit: netRevenue - totalExpense,
        amountByItem,
      });
    }

    const totalMap = new Map<string, { total: number; totalNetRevenue: number }>();
    for (const item of accountItems) {
      let total = 0;
      let totalNetRevenue = 0;
      if (displayMode === "course") {
        for (const g of courseGroups) {
          const computed = courseMap.get(g.id)!;
          const val = getCourseCellValueFromComputed(g, item, computed);
          total += val;
          totalNetRevenue += computed.netRevenue;
        }
      } else {
        for (const v of vehicles) {
          const computed = vehicleMap.get(v.id)!;
          const val = getCellValueFromComputed(v.id, item, computed);
          total += val;
          totalNetRevenue += computed.netRevenue;
        }
      }
      totalMap.set(item.id, { total, totalNetRevenue });
    }

    return {
      vehicleAmountsMap: vehicleMap,
      courseGroupAmountsMap: courseMap,
      totalByItemMap: totalMap,
    };
  }, [vehicles, accountItems, records, courseGroups, displayMode, revenueItemIds, expenseItemIds]);

  function getCellValueFromComputed(
    vehicleId: string,
    item: AccountItem,
    computed: ComputedAmounts
  ): number {
    if (item.category === "subtotal_revenue") return computed.netRevenue;
    if (item.category === "subtotal_expense") return computed.totalExpense;
    if (item.category === "subtotal_gross") return computed.grossProfit;
    if (item.category === "summary") {
      if (item.code === "SUMMARY_REV") return computed.netRevenue;
      if (item.code === "SUMMARY_EXP") return computed.totalExpense;
      if (item.code === "SUMMARY_GROSS") return computed.grossProfit;
    }
    return computed.amountByItem.get(item.id) ?? 0;
  }

  function getCourseCellValueFromComputed(
    group: CourseGroup,
    item: AccountItem,
    computed: ComputedAmounts
  ): number {
    if (item.category === "subtotal_revenue") return computed.netRevenue;
    if (item.category === "subtotal_expense") return computed.totalExpense;
    if (item.category === "subtotal_gross") return computed.grossProfit;
    if (item.category === "summary") {
      if (item.code === "SUMMARY_REV") return computed.netRevenue;
      if (item.code === "SUMMARY_EXP") return computed.totalExpense;
      if (item.code === "SUMMARY_GROSS") return computed.grossProfit;
    }
    return computed.amountByItem.get(item.id) ?? 0;
  }

  const isSubtotalRow = (item: AccountItem) => item.isSubtotal;

  const columns = displayMode === "course" ? courseGroups : vehicles;
  const canEdit = editMode;

  const scrollRef = useRef<HTMLDivElement>(null);
  const [canScrollRight, setCanScrollRight] = useState(false);
  const cellFocusRefs = useRef<Map<string, () => void>>(new Map());

  /** 矢印キーで次のセルへ移動 */
  const getNextCellKey = (
    rowIdx: number,
    colIdx: number,
    direction: ArrowDirection
  ): string | null => {
    const editableRowIndices = accountItems
      .map((item, i) => (item.isSubtotal ? -1 : i))
      .filter((i) => i >= 0);
    const numCols = columns.length;

    if (direction === "up") {
      const idx = editableRowIndices.indexOf(rowIdx);
      if (idx <= 0) return null;
      return `${editableRowIndices[idx - 1]}-${colIdx}`;
    }
    if (direction === "down") {
      const idx = editableRowIndices.indexOf(rowIdx);
      if (idx < 0 || idx >= editableRowIndices.length - 1) return null;
      return `${editableRowIndices[idx + 1]}-${colIdx}`;
    }
    if (direction === "left") {
      if (colIdx <= 0) return null;
      return `${rowIdx}-${colIdx - 1}`;
    }
    if (direction === "right") {
      if (colIdx >= numCols - 1) return null;
      return `${rowIdx}-${colIdx + 1}`;
    }
    return null;
  };

  const handleArrowKey = (
    rowIdx: number,
    colIdx: number,
    direction: ArrowDirection
  ) => {
    const nextKey = getNextCellKey(rowIdx, colIdx, direction);
    if (nextKey) {
      cellFocusRefs.current.get(nextKey)?.();
    }
  };

  const registerCellFocus = (key: string, fn: (() => void) | null) => {
    if (fn) {
      cellFocusRefs.current.set(key, fn);
    } else {
      cellFocusRefs.current.delete(key);
    }
  };

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

  const tableWidth = Math.max(800, 392 + 180 * columns.length + 180);

  return (
    <div className="relative">
      <div
        ref={scrollRef}
        className="overflow-auto max-h-[calc(100dvh-13rem)]"
      >
        <table
          className="text-sm border-separate border-spacing-0 border border-excel-grid [&_th]:border-excel-grid [&_td]:border-excel-grid"
          style={{
            width: tableWidth,
            minWidth: tableWidth,
            tableLayout: "fixed",
          }}
        >
        <colgroup>
          <col style={{ width: "40px" }} />
          <col style={{ width: "72px" }} />
          <col style={{ width: "80px" }} />
          <col style={{ width: "200px" }} />
          {columns.flatMap((c) => [
            <col key={`${c.id}-m`} style={{ width: "90px" }} />,
            <col key={`${c.id}-p`} style={{ width: "90px" }} />,
          ])}
          <col style={{ width: "180px" }} />
        </colgroup>
        <thead>
          <tr className="border-b border-excel-grid bg-muted">
            <th className="sticky left-0 top-0 z-40 bg-muted py-2 px-1 text-center text-xs font-medium text-foreground w-[40px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              {/* 登録 */}
            </th>
            <th className="sticky left-[40px] top-0 z-40 bg-muted py-2 px-2 text-left text-xs font-medium text-foreground w-[72px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              区分
            </th>
            <th className="sticky left-[112px] top-0 z-40 bg-muted py-2 px-3 text-left text-xs font-medium text-foreground w-[80px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              Code
            </th>
            <th className="sticky left-[192px] top-0 z-40 bg-muted py-2 px-3 text-left text-xs font-medium text-foreground w-[200px] min-w-[200px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
              勘定科目
            </th>
            {displayMode === "course"
              ? courseGroups.map((g) => {
                  const vehicleCount = g.vehicleIds.length;
                  const vehicleLabels = g.vehicleIds
                    .map((vid) => {
                      const v = vehicleMap.get(vid);
                      const digits = v?.vehicleNo.replace(/\D/g, "") ?? "";
                      const last4 = digits.slice(-4) || v?.vehicleNo || "-";
                      return v?.serviceType ? `${v.serviceType}（${last4}）` : `（${last4}）`;
                    })
                    .join(", ");
                  const tooltipText = vehicleLabels || "車両番号の情報がありません";
                  return (
                    <th
                      key={g.id}
                      colSpan={2}
                      className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px] border-r border-excel-grid"
                    >
                      <div className="flex flex-col items-center gap-0.5">
                        <span>{g.name}</span>
                        {vehicleCount === 0 ? (
                          <span className="text-muted-foreground font-normal text-[10px]">0台</span>
                        ) : vehicleCount === 1 ? (
                          <span className="text-muted-foreground font-normal text-[10px]">
                            {vehicleLabels}
                          </span>
                        ) : (
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <span className="text-muted-foreground font-normal text-[10px] cursor-help">
                                {vehicleCount}台
                              </span>
                            </TooltipTrigger>
                            <TooltipContent side="bottom" className="max-w-[280px]">
                              {tooltipText}
                            </TooltipContent>
                          </Tooltip>
                        )}
                      </div>
                    </th>
                  );
                })
              : vehicles.map((v) => {
                  const { courseName, serviceLine, courseCount } = formatVehicleDisplay(v);
                  return (
                    <th
                      key={v.id}
                      colSpan={2}
                      className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px] border-r border-excel-grid"
                    >
                      <div className="flex flex-col items-center gap-0.5">
                        <span>{serviceLine}</span>
                        {courseCount === 0 ? (
                          <span className="text-muted-foreground font-normal text-[10px]">コースなし</span>
                        ) : (
                          <span className="text-muted-foreground font-normal text-[10px]">
                            {courseName}
                          </span>
                        )}
                      </div>
                    </th>
                  );
                })}
            <th className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px]">
              合計
            </th>
          </tr>
          <tr className="border-b border-excel-grid bg-muted">
            <th className="sticky left-0 top-[3.25rem] z-40 bg-muted py-1 px-1 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
            <th className="sticky left-[40px] top-[3.25rem] z-40 bg-muted py-1 px-2 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
            <th className="sticky left-[112px] top-[3.25rem] z-40 bg-muted py-1 px-3 w-[80px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
            <th className="sticky left-[192px] top-[3.25rem] z-40 bg-muted py-1 px-3 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]"></th>
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
          {accountItems.map((item, rowIdx) => {
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
                <td className={`sticky left-0 z-30 py-2 px-1 text-center w-[40px] border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}>
                  {!subtotal && (item.category === "revenue" || item.category === "expense") && (
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <span className="inline-flex cursor-help">
                          {importStatus[item.id] ? (
                            <CheckCircle2 className="h-4 w-4 text-emerald-600" aria-hidden />
                          ) : (
                            <Circle className="h-4 w-4 text-muted-foreground/60" aria-hidden />
                          )}
                        </span>
                      </TooltipTrigger>
                      <TooltipContent side="right">
                        {importStatus[item.id]
                          ? "登録済み（インポートまたは連携でデータあり）"
                          : "未登録"}
                      </TooltipContent>
                    </Tooltip>
                  )}
                </td>
                <td className={`sticky left-[40px] z-30 py-2 px-2 text-sm overflow-hidden w-[72px] border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}>
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
                <td className={`sticky left-[112px] z-30 py-2 px-3 text-sm text-muted-foreground overflow-hidden w-[80px] border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}>
                  <span className="block truncate">{subtotal ? "-" : item.code}</span>
                </td>
                <td className={`sticky left-[192px] z-30 py-2 px-3 text-sm w-[200px] min-w-[200px] overflow-hidden border-r border-excel-grid ${stickyBg} shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)] ${subtotal ? "font-semibold" : "font-medium"}`}>
                  <span className="block truncate" title={item.name}>{item.name}</span>
                </td>
                {displayMode === "course"
                  ? courseGroups.map((g, colIdx) => {
                      const computed = courseGroupAmountsMap.get(g.id)!;
                      const value = getCourseCellValueFromComputed(g, item, computed);
                      const salesRatio = calcSalesRatio(value, computed.netRevenue);
                      const isCourseEditable =
                        canEdit &&
                        !subtotal &&
                        onUpdateCourseRecord &&
                        g.vehicleIds.length > 0;
                      return (
                        <React.Fragment key={g.id}>
                          <td className={`py-0 px-0 w-[90px] border-r border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                            {subtotal ? (
                              <div className="py-2 px-3 text-right font-semibold">
                                {formatCurrency(value)}
                              </div>
                            ) : isCourseEditable ? (
                              <EditableCell
                                value={value}
                                editMode={canEdit}
                                onSave={(amt) =>
                                  onUpdateCourseRecord!(g.vehicleIds, item.id, amt)
                                }
                                cellKey={`${rowIdx}-${colIdx}`}
                                onArrowKey={(dir) =>
                                  handleArrowKey(rowIdx, colIdx, dir)
                                }
                                registerFocus={registerCellFocus}
                              />
                            ) : (
                              <div className="py-2 px-3 text-right font-medium">
                                {formatCurrency(value)}
                              </div>
                            )}
                          </td>
                          <td className={`py-2 px-3 text-right w-[90px] text-muted-foreground border-r border-excel-grid ${subtotal ? "bg-muted" : "bg-background"}`}>
                            {formatPercent(salesRatio)}
                          </td>
                        </React.Fragment>
                      );
                    })
                  : vehicles.map((v, colIdx) => {
                      const computed = vehicleAmountsMap.get(v.id)!;
                      const value = getCellValueFromComputed(v.id, item, computed);
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
                                cellKey={`${rowIdx}-${colIdx}`}
                                onArrowKey={(dir) =>
                                  handleArrowKey(rowIdx, colIdx, dir)
                                }
                                registerFocus={registerCellFocus}
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
                    const rowTotal = totalByItemMap.get(item.id);
                    if (!rowTotal) return <span className="text-muted-foreground">-</span>;
                    const { total, totalNetRevenue } = rowTotal;
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

export const PLTable = React.memo(PLTableInner);

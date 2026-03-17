"use client";

import { useRef, useState, useEffect } from "react";
import { ChevronRight } from "lucide-react";
import { formatCurrency } from "@/lib/utils";

interface Vehicle {
  id: string;
  vehicleNo: string;
  serviceType: string | null;
  location: { id: string; code: string; name: string };
  course?: { id: string; name: string; code: string } | null;
}

function formatVehicleDisplay(v: Vehicle) {
  const courseName = v.course?.name ?? v.vehicleNo;
  const digitsOnly = v.vehicleNo.replace(/\D/g, "");
  const last4 = digitsOnly.slice(-4) || v.vehicleNo;
  const serviceLine = v.serviceType ? `${v.serviceType}（${last4}）` : `（${last4}）`;
  return { courseName, serviceLine };
}

interface DailySummaryTableProps {
  vehicles: Vehicle[];
  /** 車両ID -> 日(1-31) -> その日の売上 */
  dailyAmountByVehicleByDay: Record<string, Record<number, number>>;
  monthlyTotalByVehicle: Record<string, number>;
  daysInMonth: number;
  yearMonth: string;
}

export function DailySummaryTable({
  vehicles,
  dailyAmountByVehicleByDay,
  monthlyTotalByVehicle,
  daysInMonth,
  yearMonth,
}: DailySummaryTableProps) {
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
  }, [vehicles]);

  const scrollRight = () => {
    scrollRef.current?.scrollBy({ left: 300, behavior: "smooth" });
  };

  const days = Array.from({ length: daysInMonth }, (_, i) => i + 1);

  return (
    <div className="relative">
      <div
        ref={scrollRef}
        className="overflow-auto max-h-[calc(100dvh-13rem)]"
      >
        <table className="w-full min-w-[600px] text-sm border-separate border-spacing-0 border border-excel-grid [&_th]:border-excel-grid [&_td]:border-excel-grid">
        <thead>
            <tr className="border-b border-excel-grid bg-muted">
              <th className="sticky left-0 top-0 z-30 bg-muted py-2 px-3 text-left text-xs font-medium text-foreground w-[100px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
                日付
              </th>
              {vehicles.map((v) => {
                const { courseName, serviceLine } = formatVehicleDisplay(v);
                return (
                  <th
                    key={v.id}
                    className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[140px] border-r border-excel-grid"
                  >
                    <div className="flex flex-col items-center gap-0.5">
                      <span>{courseName}</span>
                      <span className="text-muted-foreground font-normal text-[10px]">{serviceLine}</span>
                    </div>
                  </th>
                );
              })}
              <th className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[140px]">
                合計
              </th>
            </tr>
          </thead>
          <tbody>
            {days.map((day) => (
              <tr
                key={day}
                className="border-b border-excel-grid bg-background hover:bg-muted/50 transition-colors"
              >
                <td className="sticky left-0 z-20 py-2 px-3 text-sm font-medium bg-background border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
                  {day}日
                </td>
                {vehicles.map((v) => (
                  <td
                    key={v.id}
                    className="py-2 px-3 text-right text-muted-foreground border-r border-excel-grid"
                  >
                    {formatCurrency(
                      dailyAmountByVehicleByDay[v.id]?.[day] ?? 0
                    )}
                  </td>
                ))}
                <td className="py-2 px-3 text-right font-medium border-l border-excel-grid">
                  {formatCurrency(
                    vehicles.reduce(
                      (sum, v) =>
                        sum + (dailyAmountByVehicleByDay[v.id]?.[day] ?? 0),
                      0
                    )
                  )}
                </td>
              </tr>
            ))}
            <tr className="border-b-2 border-excel-grid bg-muted font-semibold">
              <td className="sticky left-0 z-20 py-2 px-3 text-sm bg-muted border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
                月計
              </td>
              {vehicles.map((v) => (
                <td key={v.id} className="py-2 px-3 text-right bg-muted border-r border-excel-grid">
                  {formatCurrency(monthlyTotalByVehicle[v.id] ?? 0)}
                </td>
              ))}
              <td className="py-2 px-3 text-right border-l border-excel-grid bg-muted">
                {formatCurrency(
                  vehicles.reduce(
                    (sum, v) => sum + (monthlyTotalByVehicle[v.id] ?? 0),
                    0
                  )
                )}
              </td>
            </tr>
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

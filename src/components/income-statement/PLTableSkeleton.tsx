"use client";

import React from "react";

/**
 * 車両損益計算書のテーブル構造を模したスケルトンUI。
 * データ読み込み中に即時表示し、ユーザーの待ち時間ストレスを軽減する。
 */
const SKELETON_ROW_COUNT = 28;
const SKELETON_VEHICLE_COUNT = 10;

export function PLTableSkeleton() {
  return (
    <div className="relative">
      <div className="overflow-auto max-h-[calc(100dvh-13rem)]">
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
              {Array.from({ length: SKELETON_VEHICLE_COUNT }).map((_, i) => (
                <th
                  key={i}
                  colSpan={2}
                  className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px] border-r border-excel-grid"
                >
                  <div className="flex flex-col items-center gap-1">
                    <div className="h-3 w-16 rounded bg-muted-foreground/30 animate-pulse" />
                    <div className="h-2.5 w-12 rounded bg-muted-foreground/20 animate-pulse" />
                  </div>
                </th>
              ))}
              <th className="sticky top-0 z-30 bg-muted py-2 px-3 text-center text-xs font-medium text-foreground min-w-[180px]">
                合計
              </th>
            </tr>
            <tr className="border-b border-excel-grid bg-muted">
              <th className="sticky left-0 top-[3.25rem] z-30 bg-muted py-1 px-2 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]" />
              <th className="sticky left-[72px] top-[3.25rem] z-30 bg-muted py-1 px-3 w-[80px] border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]" />
              <th className="sticky left-[152px] top-[3.25rem] z-30 bg-muted py-1 px-3 border-r border-excel-grid shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]" />
              {Array.from({ length: SKELETON_VEHICLE_COUNT * 2 }).map((_, i) => (
                <th
                  key={i}
                  className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground w-[90px] border-r border-excel-grid"
                >
                  {i % 2 === 0 ? "月間" : "売上比(%)"}
                </th>
              ))}
              <th colSpan={2} className="sticky top-[3.25rem] z-30 bg-muted py-1 px-3 text-xs font-normal text-muted-foreground">
                月間 / 売上比(%)
              </th>
            </tr>
          </thead>
          <tbody>
            {Array.from({ length: SKELETON_ROW_COUNT }).map((_, rowIndex) => {
              const isSubtotal =
                rowIndex === 5 || rowIndex === 12 || rowIndex === 18 || rowIndex === 26;
              return (
                <tr
                  key={rowIndex}
                  className={`group border-b border-excel-grid ${
                    isSubtotal ? "bg-muted" : "bg-background"
                  }`}
                >
                  <td
                    className={`sticky left-0 z-20 py-2 px-2 w-[72px] border-r border-excel-grid ${
                      isSubtotal ? "bg-muted" : "bg-background"
                    } shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}
                  >
                    <div
                      className={`h-5 rounded animate-pulse ${
                        isSubtotal
                          ? "w-12 bg-muted-foreground/30"
                          : "w-14 bg-muted-foreground/20"
                      }`}
                    />
                  </td>
                  <td
                    className={`sticky left-[72px] z-20 py-2 px-3 w-[80px] border-r border-excel-grid ${
                      isSubtotal ? "bg-muted" : "bg-background"
                    } shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}
                  >
                    <div
                      className={`h-4 rounded animate-pulse ${
                        isSubtotal ? "w-8 bg-muted-foreground/30" : "w-12 bg-muted-foreground/20"
                      }`}
                    />
                  </td>
                  <td
                    className={`sticky left-[152px] z-20 py-2 px-3 w-[200px] max-w-[200px] border-r border-excel-grid ${
                      isSubtotal ? "bg-muted" : "bg-background"
                    } shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]`}
                  >
                    <div
                      className={`h-4 rounded animate-pulse ${
                        isSubtotal
                          ? "w-24 bg-muted-foreground/30"
                          : "w-32 bg-muted-foreground/20"
                      }`}
                    />
                  </td>
                  {Array.from({ length: SKELETON_VEHICLE_COUNT }).map((_, colIndex) => (
                    <React.Fragment key={colIndex}>
                      <td
                        className={`py-0 px-0 w-[90px] border-r border-excel-grid ${
                          isSubtotal ? "bg-muted" : "bg-background"
                        }`}
                      >
                        <div className="py-2 px-3 flex justify-end">
                          <div
                            className={`h-4 rounded animate-pulse ${
                              isSubtotal
                                ? "w-16 bg-muted-foreground/30"
                                : "w-14 bg-muted-foreground/20"
                            }`}
                          />
                        </div>
                      </td>
                      <td
                        key={`${colIndex}-p`}
                        className={`py-2 px-3 text-right w-[90px] border-r border-excel-grid ${
                          isSubtotal ? "bg-muted" : "bg-background"
                        }`}
                      >
                        <div
                          className={`h-4 rounded animate-pulse inline-block ml-auto ${
                            isSubtotal
                              ? "w-10 bg-muted-foreground/20"
                              : "w-8 bg-muted-foreground/15"
                          }`}
                        />
                      </td>
                    </React.Fragment>
                  ))}
                  <td
                    className={`py-2 px-3 border-l border-excel-grid ${
                      isSubtotal ? "bg-muted" : "bg-background"
                    }`}
                  >
                    <div className="flex flex-col items-end gap-0.5">
                      <div
                        className={`h-4 rounded animate-pulse ${
                          isSubtotal
                            ? "w-20 bg-muted-foreground/30"
                            : "w-16 bg-muted-foreground/20"
                        }`}
                      />
                      <div className="h-3 w-12 rounded bg-muted-foreground/15 animate-pulse" />
                    </div>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}

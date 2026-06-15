/**
 * 年月の共有ストア — ダッシュボード・損益計算書など複数ページで年月選択を共有する。
 * ページ遷移しても選択状態を保持する（zustand はメモリ内ストアのため SPA 遷移で維持される）。
 */

import { create } from "zustand";

function currentYear(): number {
  return new Date().getFullYear();
}

function currentMonth(): number {
  return new Date().getMonth() + 1; // 1-indexed
}

/** 年の選択肢を生成（現在の年 以前 5年分） */
export function getYears(): number[] {
  const now = currentYear();
  const years: number[] = [];
  for (let y = now; y >= now - 4; y--) {
    years.push(y);
  }
  return years;
}

/** 月の選択肢（1〜12）。選択年が今年の場合は最大 currentMonth + 1 まで */
export function getMonths(selectedYear?: number): number[] {
  const now = new Date();
  const thisYear = now.getFullYear();
  const thisMonth = now.getMonth() + 1; // 1-indexed

  const maxMonth =
    selectedYear !== undefined && selectedYear === thisYear
      ? Math.min(thisMonth + 1, 12)
      : 12;

  const months: number[] = [];
  for (let m = 1; m <= maxMonth; m++) {
    months.push(m);
  }
  return months;
}

/** 選択年に対する最大月を返す */
export function getMaxMonth(selectedYear: number): number {
  const now = new Date();
  const thisYear = now.getFullYear();
  const thisMonth = now.getMonth() + 1;
  return selectedYear === thisYear ? Math.min(thisMonth + 1, 12) : 12;
}

/** 年 + 月 → "YYYY-MM" */
export function toYearMonth(year: number, month: number): string {
  return `${year}-${String(month).padStart(2, "0")}`;
}

/** "YYYY-MM" → { year, month } */
export function parseYearMonth(ym: string): { year: number; month: number } {
  const [y, m] = ym.split("-").map(Number);
  return { year: y, month: m };
}

interface YearMonthState {
  year: number;
  month: number;
  /** "YYYY-MM" 形式の結合値（computed） */
  yearMonth: string;
  setYear: (year: number) => void;
  setMonth: (month: number) => void;
  /** URL パラメータ等からの一括設定 */
  setYearMonth: (ym: string) => void;
}

export const useYearMonthStore = create<YearMonthState>((set) => ({
  year: currentYear(),
  month: currentMonth(),
  yearMonth: toYearMonth(currentYear(), currentMonth()),
  setYear: (year) =>
    set((state) => {
      const max = getMaxMonth(year);
      const month = state.month > max ? max : state.month;
      return {
        year,
        month,
        yearMonth: toYearMonth(year, month),
      };
    }),
  setMonth: (month) =>
    set((state) => ({
      month,
      yearMonth: toYearMonth(state.year, month),
    })),
  setYearMonth: (ym: string) => {
    const { year, month } = parseYearMonth(ym);
    set({ year, month, yearMonth: toYearMonth(year, month) });
  },
}));

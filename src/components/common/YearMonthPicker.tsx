"use client";

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useYearMonthStore, getYears, getMonths } from "@/stores/yearMonthStore";

interface YearMonthPickerProps {
  year?: number;
  month?: number;
  onYearChange?: (year: number) => void;
  onMonthChange?: (month: number) => void;
  className?: string;
}

export function YearMonthPicker({
  year: propYear,
  month: propMonth,
  onYearChange,
  onMonthChange,
  className = "flex items-center gap-2 shrink-0",
}: YearMonthPickerProps) {
  const store = useYearMonthStore();

  const year = propYear ?? store.year;
  const month = propMonth ?? store.month;

  const years = getYears();
  const months = getMonths(year);

  const handleYearChange = (v: string) => {
    const nextYear = Number(v);
    if (onYearChange) {
      onYearChange(nextYear);
    } else {
      store.setYear(nextYear);
    }
  };

  const handleMonthChange = (v: string) => {
    const nextMonth = Number(v);
    if (onMonthChange) {
      onMonthChange(nextMonth);
    } else {
      store.setMonth(nextMonth);
    }
  };

  return (
    <div className={className}>
      <Select value={String(year)} onValueChange={handleYearChange}>
        <SelectTrigger className="w-24">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {years.map((y) => (
            <SelectItem key={y} value={String(y)}>
              {y}年
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      <Select value={String(month)} onValueChange={handleMonthChange}>
        <SelectTrigger className="w-20">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {months.map((m) => (
            <SelectItem key={m} value={String(m)}>
              {m}月
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}

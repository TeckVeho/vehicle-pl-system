"use client";

import { Search } from "lucide-react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import type { DisplayMode } from "./PLTable";
import { getYears, getMonths, getMaxMonth, parseYearMonth } from "@/stores/yearMonthStore";

interface FilterBarProps {
  yearMonth: string;
  searchQuery: string;
  displayMode: DisplayMode;
  onYearMonthChange: (value: string) => void;
  onSearchChange: (value: string) => void;
  onDisplayModeChange: (value: DisplayMode) => void;
}

export function FilterBar({
  yearMonth,
  searchQuery,
  displayMode,
  onYearMonthChange,
  onSearchChange,
  onDisplayModeChange,
}: FilterBarProps) {
  const years = getYears();
  const { year, month } = parseYearMonth(yearMonth);
  const months = getMonths(year);

  const handleYearChange = (v: string) => {
    const newYear = Number(v);
    const max = getMaxMonth(newYear);
    const clampedMonth = month > max ? max : month;
    onYearMonthChange(`${newYear}-${String(clampedMonth).padStart(2, "0")}`);
  };

  const handleMonthChange = (v: string) => {
    const newMonth = Number(v);
    onYearMonthChange(`${year}-${String(newMonth).padStart(2, "0")}`);
  };

  return (
    <div className="flex flex-nowrap gap-5 items-center mb-6 overflow-x-auto">
      <div className="flex items-center gap-2 shrink-0">
        <span className="text-sm text-muted-foreground whitespace-nowrap">年</span>
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
        <span className="text-sm text-muted-foreground whitespace-nowrap">月</span>
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
      <div className="flex items-center gap-1 shrink-0">
        <span className="text-sm text-muted-foreground whitespace-nowrap">表示</span>
        <div className="flex rounded-md border border-input overflow-hidden">
          <Button
            type="button"
            variant={displayMode === "course" ? "default" : "ghost"}
            size="sm"
            className="rounded-none h-9 px-3"
            onClick={() => onDisplayModeChange("course")}
          >
            コース
          </Button>
          <Button
            type="button"
            variant={displayMode === "vehicle" ? "default" : "ghost"}
            size="sm"
            className="rounded-none h-9 px-3"
            onClick={() => onDisplayModeChange("vehicle")}
          >
            車両
          </Button>
        </div>
      </div>
      <div className="relative flex-1 min-w-[180px] max-w-xs">
        <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
        <Input
          type="search"
          placeholder="勘定科目・Code・区分で検索..."
          value={searchQuery}
          onChange={(e) => onSearchChange(e.target.value)}
          className="pl-8 h-9"
        />
      </div>
    </div>
  );
}

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

interface FilterBarProps {
  yearMonth: string;
  searchQuery: string;
  displayMode: DisplayMode;
  onYearMonthChange: (value: string) => void;
  onSearchChange: (value: string) => void;
  onDisplayModeChange: (value: DisplayMode) => void;
}

function getYearMonths(): string[] {
  const months: string[] = [];
  const now = new Date();
  for (let i = -12; i <= 12; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
    months.push(
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`
    );
  }
  return months.reverse();
}

export function FilterBar({
  yearMonth,
  searchQuery,
  displayMode,
  onYearMonthChange,
  onSearchChange,
  onDisplayModeChange,
}: FilterBarProps) {
  const yearMonths = getYearMonths();

  return (
    <div className="flex flex-nowrap gap-5 items-center mb-6 overflow-x-auto">
      <div className="flex items-center gap-2 shrink-0">
        <span className="text-sm text-muted-foreground whitespace-nowrap">年月</span>
        <Select value={yearMonth} onValueChange={onYearMonthChange}>
          <SelectTrigger className="w-36">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            {yearMonths.map((ym) => (
              <SelectItem key={ym} value={ym}>
                {ym.replace("-", "年")}月
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

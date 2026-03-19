"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { formatCurrency } from "@/lib/utils";
import { fetchApi } from "@/lib/api";
import {
  TrendingUp,
  TrendingDown,
  DollarSign,
  Car,
  MapPin,
  ChevronRight,
} from "lucide-react";
import { LoadingOverlay } from "@/components/income-statement/LoadingOverlay";

interface LocationSummary {
  locationId: string;
  locationCode: string;
  locationName: string;
  vehicleCount: number;
  netRevenue: number;
  totalExpense: number;
  grossProfit: number;
}

interface DashboardData {
  yearMonth: string;
  lastUpdatedAt: string | null;
  summary: {
    totalNetRevenue: number;
    totalExpense: number;
    totalGrossProfit: number;
    totalVehicleCount: number;
    locationCount: number;
  };
  locationSummaries: LocationSummary[];
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

export default function DashboardPage() {
  const [yearMonth, setYearMonth] = useState(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
  });
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      try {
        const res = await fetchApi(`/api/dashboard/summary?yearMonth=${yearMonth}`);
        const json = await res.json();
        if (!res.ok || !json.summary) {
          setData(null);
          return;
        }
        setData(json);
      } catch {
        setData(null);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [yearMonth]);

  const yearMonths = getYearMonths();

  if (loading && !data) {
    return <LoadingOverlay message="読み込み中" />;
  }

  if (!data?.summary) {
    return (
      <div className="pb-12">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-3xl font-bold tracking-tight">ダッシュボード</h1>
          <div className="flex items-center gap-2 shrink-0">
            <span className="text-sm text-muted-foreground whitespace-nowrap">年月</span>
            <Select value={yearMonth} onValueChange={setYearMonth}>
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
        </div>
        <p className="text-muted-foreground">
          データの取得に失敗しました。年月を変更するか、しばらくしてから再試行してください。
        </p>
      </div>
    );
  }

  return (
    <div className="pb-12 relative">
      {loading && <LoadingOverlay message="データを読み込み中" />}

      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">ダッシュボード</h1>
          {data?.lastUpdatedAt && (
            <p className="text-xs text-muted-foreground mt-1">
              最終更新:{" "}
              {new Date(data.lastUpdatedAt).toLocaleString("ja-JP", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
              })}
            </p>
          )}
        </div>
        <div className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-muted-foreground whitespace-nowrap">
            年月
          </span>
          <Select value={yearMonth} onValueChange={setYearMonth}>
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
      </div>

      {data && (
        <>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
            <div className="border border-excel-grid bg-card p-6">
              <div className="flex items-center gap-2 text-muted-foreground mb-2">
                <TrendingUp className="h-4 w-4" />
                <span className="text-sm font-medium">売上高</span>
              </div>
              <p className="text-2xl font-bold">
                {formatCurrency(data.summary.totalNetRevenue)}
              </p>
            </div>
            <div className="border border-excel-grid bg-card p-6">
              <div className="flex items-center gap-2 text-muted-foreground mb-2">
                <TrendingDown className="h-4 w-4" />
                <span className="text-sm font-medium">原価合計</span>
              </div>
              <p className="text-2xl font-bold">
                {formatCurrency(data.summary.totalExpense)}
              </p>
            </div>
            <div className="border border-excel-grid bg-card p-6">
              <div className="flex items-center gap-2 text-muted-foreground mb-2">
                <DollarSign className="h-4 w-4" />
                <span className="text-sm font-medium">粗利益</span>
              </div>
              <p
                className={`text-2xl font-bold ${
                  data.summary.totalGrossProfit >= 0
                    ? "text-foreground"
                    : "text-destructive"
                }`}
              >
                {formatCurrency(data.summary.totalGrossProfit)}
              </p>
            </div>
            <div className="border border-excel-grid bg-card p-6">
              <div className="flex items-center gap-2 text-muted-foreground mb-2">
                <Car className="h-4 w-4" />
                <span className="text-sm font-medium">車両数</span>
              </div>
              <p className="text-2xl font-bold">
                {data.summary.totalVehicleCount} 台
              </p>
            </div>
          </div>

          <div className="border border-excel-grid bg-card overflow-hidden">
            <div className="px-6 py-4 border-b border-excel-grid">
              <h2 className="font-semibold flex items-center gap-2">
                <MapPin className="h-4 w-4 text-muted-foreground" />
                拠点別サマリー
              </h2>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-excel-grid bg-muted">
                    <th className="text-left font-medium px-6 py-3 border-r border-excel-grid">拠点</th>
                    <th className="text-right font-medium px-6 py-3 border-r border-excel-grid">車両数</th>
                    <th className="text-right font-medium px-6 py-3 border-r border-excel-grid">売上高</th>
                    <th className="text-right font-medium px-6 py-3 border-r border-excel-grid">原価</th>
                    <th className="text-right font-medium px-6 py-3 border-r border-excel-grid">粗利益</th>
                    <th className="w-12"></th>
                  </tr>
                </thead>
                <tbody>
                  {data.locationSummaries.map((loc) => (
                    <tr key={loc.locationId} className="border-b border-excel-grid last:border-b-0">
                      <td className="px-6 py-3">
                        <span className="font-medium">{loc.locationName}</span>
                      </td>
                      <td className="text-right px-6 py-3">
                        {loc.vehicleCount} 台
                      </td>
                      <td className="text-right px-6 py-3 tabular-nums">
                        {formatCurrency(loc.netRevenue)}
                      </td>
                      <td className="text-right px-6 py-3 tabular-nums">
                        {formatCurrency(loc.totalExpense)}
                      </td>
                      <td
                        className={`text-right px-6 py-3 tabular-nums font-medium ${
                          loc.grossProfit >= 0
                            ? "text-foreground"
                            : "text-destructive"
                        }`}
                      >
                        {formatCurrency(loc.grossProfit)}
                      </td>
                      <td className="px-2 py-3">
                        <Link
                          href={`/income-statement?yearMonth=${yearMonth}&locationId=${loc.locationId}`}
                          className="inline-flex items-center justify-center p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                          title="詳細を見る"
                        >
                          <ChevronRight className="h-4 w-4" />
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {!data && !loading && (
        <div className="border border-dashed border-excel-grid p-12 text-center text-muted-foreground">
          データを取得できませんでした。年月を変更するか、後でもう一度お試しください。
        </div>
      )}
    </div>
  );
}

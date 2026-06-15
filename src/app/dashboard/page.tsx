"use client";

import { useState, useEffect, useCallback } from "react";
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
  RefreshCw,
} from "lucide-react";
import { LoadingOverlay } from "@/components/income-statement/LoadingOverlay";
import {
  useYearMonthStore,
} from "@/stores/yearMonthStore";
import { YearMonthPicker } from "@/components/common/YearMonthPicker";

import { useAuthStore, canManageMaster } from "@/stores/authStore";

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
  lastSyncedAt: string | null;
  summary: {
    totalNetRevenue: number;
    totalExpense: number;
    totalGrossProfit: number;
    totalVehicleCount: number;
    locationCount: number;
  };
  locationSummaries: LocationSummary[];
}

export default function DashboardPage() {
  const { year, month, yearMonth, setYear, setMonth } = useYearMonthStore();
  const user = useAuthStore((s) => s.user);
  const isMaster = user ? canManageMaster(user.role) : false;

  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [syncResult, setSyncResult] = useState<{
    type: "success" | "info" | "error";
    message: string;
    details?: string[];
  } | null>(null);

  const fetchData = useCallback(async () => {
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
  }, [yearMonth]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleSync = async () => {
    setSyncing(true);
    setSyncResult(null);
    try {
      const res = await fetchApi("/api/dashboard/sync", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ yearMonth }),
      });
      const json = await res.json();
      if (res.ok && json.success) {
        const totalLocations = json.totalLocations ?? (json.okCount + json.failCount);
        const message = `同期完了: ${totalLocations}拠点中 ${json.okCount}拠点のデータを同期しました` +
          (json.failCount > 0 ? `（${json.failCount}拠点はファイル未検出のためスキップ）` : "");

        // Build detail lines for synced locations
        const details: string[] = [];
        if (json.results && Array.isArray(json.results)) {
          const synced = json.results.filter((r: { ok: boolean }) => r.ok);
          const skipped = json.results.filter((r: { ok: boolean }) => !r.ok);

          if (synced.length > 0) {
            details.push("【同期済み】");
            for (const r of synced) {
              details.push(`  ✓ ${r.locationCode ?? ""} ${r.locationName ?? ""} — ${r.recordCount ?? 0}件`);
            }
          }
          if (skipped.length > 0) {
            details.push("【スキップ】");
            for (const r of skipped) {
              const reason = r.error?.includes("no spreadsheetId")
                ? "対応ファイル未検出"
                : r.error?.includes("no_matching_sheet_tab")
                  ? "対応シート未検出"
                  : r.error ?? "不明";
              details.push(`  − ${r.locationCode ?? ""} ${r.locationName ?? ""} — ${reason}`);
            }
          }
        }

        setSyncResult({
          type: json.failCount > 0 ? "info" : "success",
          message,
          details: details.length > 0 ? details : undefined,
        });
        // リロードしてデータを更新
        await fetchData();
      } else {
        setSyncResult({
          type: "error",
          message: `同期失敗: ${json.error || "不明なエラー"}`,
        });
      }
    } catch {
      setSyncResult({
        type: "error",
        message: "同期に失敗しました。ネットワークを確認してください。",
      });
    } finally {
      setSyncing(false);
      // 10秒後にメッセージを消す（詳細あるため長めに）
      setTimeout(() => setSyncResult(null), 10000);
    }
  };



  if (loading && !data) {
    return <LoadingOverlay message="読み込み中" />;
  }

  if (!data?.summary) {
    return (
      <div className="pb-12">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-3xl font-bold tracking-tight">ダッシュボード</h1>
          <div className="flex items-center gap-2 shrink-0">
            <YearMonthPicker />
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
          <div className="flex items-center gap-3 mt-1">
            {data?.lastUpdatedAt && (
              <p className="text-xs text-muted-foreground">
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
            {data?.lastSyncedAt && (
              <p className="text-xs text-muted-foreground">
                最終同期:{" "}
                {new Date(data.lastSyncedAt).toLocaleString("ja-JP", {
                  year: "numeric",
                  month: "2-digit",
                  day: "2-digit",
                  hour: "2-digit",
                  minute: "2-digit",
                })}
              </p>
            )}
          </div>
        </div>
        <div className="flex items-center gap-2 shrink-0">
          {/* 手動同期ボタン（MASTER 権限のみ表示） */}
          {isMaster && (
            <button
              type="button"
              onClick={handleSync}
              disabled={syncing}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              title="Google Drive からスプレッドシート売上データを同期"
            >
              <RefreshCw
                className={`h-4 w-4 ${syncing ? "animate-spin" : ""}`}
              />
              {syncing ? "同期中..." : "データ同期"}
            </button>
          )}

          <YearMonthPicker />
        </div>
      </div>

      {/* 同期結果メッセージ */}
      {syncResult && (
        <div
          className={`mb-4 px-4 py-3 text-sm border ${
            syncResult.type === "error"
              ? "border-destructive/50 bg-destructive/10 text-destructive"
              : syncResult.type === "info"
                ? "border-amber-500/50 bg-amber-500/10 text-amber-700 dark:text-amber-400"
                : "border-green-500/50 bg-green-500/10 text-green-700 dark:text-green-400"
          }`}
        >
          <div className="font-medium">{syncResult.message}</div>
          {syncResult.details && syncResult.details.length > 0 && (
            <details className="mt-2">
              <summary className="cursor-pointer text-xs opacity-80 hover:opacity-100">
                詳細を表示
              </summary>
              <pre className="mt-1 text-xs whitespace-pre-wrap leading-relaxed opacity-90">
                {syncResult.details.join("\n")}
              </pre>
            </details>
          )}
        </div>
      )}

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

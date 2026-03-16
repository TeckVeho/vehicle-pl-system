"use client";

import { useState, useEffect } from "react";
import { fetchApi } from "@/lib/api";
import { RefreshCw, Link2 } from "lucide-react";
import { Button } from "@/components/ui/button";

interface SyncLog {
  id: string;
  source: string;
  syncType: string;
  recordCount: number;
  yearMonth: string | null;
  locationId: string | null;
  locationName: string | null;
  createdAt: string;
}

const SYNC_TYPE_LABELS: Record<string, string> = {
  monthly_records: "月次損益データ",
  daily_revenue: "日次売上",
  account_items: "勘定科目マスタ",
  users: "ユーザー",
};

function formatSyncType(type: string): string {
  return SYNC_TYPE_LABELS[type] ?? type;
}

function formatDateTime(iso: string): string {
  const d = new Date(iso);
  return d.toLocaleString("ja-JP", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
}

export default function SyncLogsPage() {
  const [logs, setLogs] = useState<SyncLog[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchLogs = async () => {
    setLoading(true);
    try {
      const res = await fetchApi("/api/sync-logs");
      const data = await res.json();
      setLogs(Array.isArray(data) ? data : []);
    } catch {
      setLogs([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, []);

  return (
    <div className="pb-12">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
          <Link2 className="h-8 w-8" />
          データ連携記録
        </h1>
        <Button variant="outline" size="sm" onClick={fetchLogs} disabled={loading}>
          <RefreshCw className={`h-4 w-4 mr-1.5 ${loading ? "animate-spin" : ""}`} />
          更新
        </Button>
      </div>

      <p className="text-sm text-muted-foreground mb-6">
        外部システムからのデータ連携の成功記録を一覧表示します。
      </p>

      {loading && logs.length === 0 ? (
        <div className="flex items-center justify-center min-h-[40vh]">
          <span className="text-muted-foreground">読み込み中...</span>
        </div>
      ) : logs.length === 0 ? (
        <div className="rounded-lg border bg-card p-12 text-center text-muted-foreground">
          連携記録はまだありません。データインポートや外部システムからの連携が成功すると、ここに記録されます。
        </div>
      ) : (
        <div className="rounded-lg border bg-card overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b bg-muted/50">
                  <th className="text-left p-3 font-medium">連携日時</th>
                  <th className="text-left p-3 font-medium">連携元</th>
                  <th className="text-left p-3 font-medium">連携種別</th>
                  <th className="text-right p-3 font-medium">件数</th>
                  <th className="text-left p-3 font-medium">対象年月</th>
                  <th className="text-left p-3 font-medium">拠点</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((log) => (
                  <tr key={log.id}>
                    <td className="p-3 whitespace-nowrap">
                      {formatDateTime(log.createdAt)}
                    </td>
                    <td className="p-3">{log.source}</td>
                    <td className="p-3">{formatSyncType(log.syncType)}</td>
                    <td className="p-3 text-right font-medium">
                      {log.recordCount.toLocaleString()} 件
                    </td>
                    <td className="p-3">
                      {log.yearMonth
                        ? log.yearMonth.replace("-", "年") + "月"
                        : "-"}
                    </td>
                    <td className="p-3 text-muted-foreground">
                      {log.locationName ?? "-"}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}

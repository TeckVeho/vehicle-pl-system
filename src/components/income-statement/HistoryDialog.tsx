"use client";

import { useState, useEffect } from "react";
import { fetchApi } from "@/lib/api";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { History, ArrowRight } from "lucide-react";
import { formatCurrency } from "@/lib/utils";

interface HistoryEntry {
  id: string;
  yearMonth: string;
  vehicleNo: string;
  vehicleName: string | null;
  locationName: string;
  accountItemCode: string;
  accountItemName: string;
  oldAmount: number;
  newAmount: number;
  createdAt: string;
  createdByName: string | null;
}

interface HistoryDialogProps {
  open: boolean;
  yearMonth: string;
  onClose: () => void;
}

export function HistoryDialog({ open, yearMonth, onClose }: HistoryDialogProps) {
  const [histories, setHistories] = useState<HistoryEntry[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!open) return;
    setLoading(true);
    fetchApi(`/api/income-statement/history?yearMonth=${yearMonth}`)
      .then((r) => r.json())
      .then((data) => setHistories(data))
      .catch(() => setHistories([]))
      .finally(() => setLoading(false));
  }, [open, yearMonth]);

  const formatDate = (iso: string) => {
    const d = new Date(iso);
    return `${d.getFullYear()}/${String(d.getMonth() + 1).padStart(2, "0")}/${String(d.getDate()).padStart(2, "0")} ${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
  };

  return (
    <Dialog open={open} onOpenChange={(v) => !v && onClose()}>
      <DialogContent className="max-w-3xl max-h-[80vh] flex flex-col">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <History className="h-4 w-4" />
            編集履歴 — {yearMonth.replace("-", "年")}月
          </DialogTitle>
        </DialogHeader>

        <div className="flex-1 overflow-auto min-h-0">
          {loading ? (
            <div className="flex items-center justify-center py-12 text-sm text-muted-foreground">
              読み込み中...
            </div>
          ) : histories.length === 0 ? (
            <div className="flex items-center justify-center py-12 text-sm text-muted-foreground">
              この月の編集履歴はありません
            </div>
          ) : (
            <table className="w-full text-sm border-collapse">
              <thead className="sticky top-0 bg-background z-10">
                <tr className="border-b text-xs text-muted-foreground">
                  <th className="py-2 px-3 text-left font-medium">日時</th>
                  <th className="py-2 px-3 text-left font-medium">変更者</th>
                  <th className="py-2 px-3 text-left font-medium">拠点</th>
                  <th className="py-2 px-3 text-left font-medium">車両</th>
                  <th className="py-2 px-3 text-left font-medium">勘定科目</th>
                  <th className="py-2 px-3 text-right font-medium">変更前</th>
                  <th className="py-2 px-1 text-center font-medium w-6"></th>
                  <th className="py-2 px-3 text-right font-medium">変更後</th>
                </tr>
              </thead>
              <tbody>
                {histories.map((h) => {
                  const diff = h.newAmount - h.oldAmount;
                  return (
                    <tr key={h.id} className="border-b border-border/30 hover:bg-muted/30 transition-colors">
                      <td className="py-2 px-3 text-xs text-muted-foreground whitespace-nowrap">
                        {formatDate(h.createdAt)}
                      </td>
                      <td className="py-2 px-3 text-xs text-muted-foreground">
                        {h.createdByName ?? "—"}
                      </td>
                      <td className="py-2 px-3 text-xs text-muted-foreground">
                        {h.locationName}
                      </td>
                      <td className="py-2 px-3">
                        <span className="font-medium">{h.vehicleName ?? h.vehicleNo}</span>
                      </td>
                      <td className="py-2 px-3">
                        <span className="text-xs text-muted-foreground mr-1">{h.accountItemCode}</span>
                        {h.accountItemName}
                      </td>
                      <td className="py-2 px-3 text-right font-mono text-muted-foreground">
                        {formatCurrency(h.oldAmount)}
                      </td>
                      <td className="py-2 px-1 text-center">
                        <ArrowRight className="h-3 w-3 text-muted-foreground mx-auto" />
                      </td>
                      <td className={`py-2 px-3 text-right font-mono font-medium ${
                        diff > 0 ? "text-emerald-600" : diff < 0 ? "text-red-500" : ""
                      }`}>
                        {formatCurrency(h.newAmount)}
                        {diff !== 0 && (
                          <span className="text-xs ml-1">
                            ({diff > 0 ? "+" : ""}{formatCurrency(diff)})
                          </span>
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
}

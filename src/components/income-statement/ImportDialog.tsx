"use client";

import { useState } from "react";
import { fetchApi } from "@/lib/api";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Upload, FileSpreadsheet, X } from "lucide-react";

interface ImportDialogProps {
  open: boolean;
  yearMonth: string;
  locationId: string;
  onClose: () => void;
  onSuccess: () => void;
}

export function ImportDialog({
  open,
  yearMonth,
  locationId,
  onClose,
  onSuccess,
}: ImportDialogProps) {
  const [file, setFile] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<{
    success: number;
    errors: string[];
  } | null>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const f = e.target.files?.[0] ?? null;
    setFile(f);
    setResult(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!file) return;
    setLoading(true);
    setResult(null);

    const formData = new FormData();
    formData.append("file", file);
    formData.append("locationId", locationId === "all" ? "" : locationId);
    formData.append("yearMonth", yearMonth);

    try {
      const res = await fetchApi("/api/import", { method: "POST", body: formData });
      const data = await res.json();
      setResult(data);
      if (data.success > 0) {
        onSuccess();
        if (data.errors.length === 0) {
          setTimeout(() => {
            setFile(null);
            setResult(null);
            onClose();
          }, 1500);
        }
      }
    } catch {
      setResult({ success: 0, errors: ["アップロードに失敗しました"] });
    } finally {
      setLoading(false);
    }
  };

  const handleClose = () => {
    setFile(null);
    setResult(null);
    onClose();
  };

  return (
    <Dialog open={open} onOpenChange={(v) => !v && handleClose()}>
      <DialogContent className="max-w-lg">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <FileSpreadsheet className="h-4 w-4" />
            データインポート — {yearMonth.replace("-", "年")}月
          </DialogTitle>
        </DialogHeader>

        <div className="space-y-4">
          <div className="rounded-md bg-muted/50 p-4 text-xs text-muted-foreground space-y-1">
            <p className="font-medium text-foreground mb-1.5">対応フォーマット（CSV / Excel）</p>
            <pre className="bg-background/80 rounded px-3 py-2 overflow-x-auto">
{`コース名,勘定科目名,金額
001-001,山崎製パン,100000
001-001,乗務員給料,50000`}
            </pre>
            <ul className="list-disc list-inside space-y-0.5 mt-2">
              <li>1行目: ヘッダー行（スキップされます）</li>
              <li>コース名・勘定科目名・金額 の順</li>
              <li>Excelの場合は1シート目が対象</li>
              {locationId === "all" && (
                <li className="text-amber-600">拠点「全体」の場合は最初にタブで拠点を絞り込んでください</li>
              )}
            </ul>
          </div>

          <form onSubmit={handleSubmit} className="space-y-3">
            <div className="flex items-center gap-2">
              <label className="flex-1 relative cursor-pointer">
                <input
                  type="file"
                  accept=".csv,.xlsx,.xls"
                  onChange={handleFileChange}
                  className="absolute inset-0 opacity-0 w-full cursor-pointer"
                />
                <div className="flex items-center gap-2 rounded-md border border-dashed border-border px-3 py-2 text-sm text-muted-foreground hover:border-foreground/40 hover:text-foreground transition-colors">
                  <Upload className="h-4 w-4 shrink-0" />
                  {file ? (
                    <span className="truncate text-foreground font-medium">{file.name}</span>
                  ) : (
                    <span>CSV または Excel ファイルを選択</span>
                  )}
                </div>
              </label>
              {file && (
                <button
                  type="button"
                  onClick={() => { setFile(null); setResult(null); }}
                  className="p-1 rounded hover:bg-muted"
                >
                  <X className="h-4 w-4 text-muted-foreground" />
                </button>
              )}
            </div>

            <div className="flex justify-end gap-2">
              <Button type="button" variant="ghost" size="sm" onClick={handleClose}>
                キャンセル
              </Button>
              <Button type="submit" size="sm" disabled={!file || loading || locationId === "all"}>
                <Upload className="h-4 w-4 mr-1.5" />
                {loading ? "インポート中..." : "インポート実行"}
              </Button>
            </div>
          </form>

          {result && (
            <div
              className={`rounded-md p-3 text-sm ${
                result.errors.length > 0 && result.success === 0
                  ? "bg-destructive/5 text-destructive"
                  : result.errors.length > 0
                    ? "bg-amber-50 text-amber-800 border border-amber-200"
                    : "bg-muted/60 text-foreground"
              }`}
            >
              <p className="font-medium">
                {result.success} 件インポートしました
                {result.errors.length > 0 && `（${result.errors.length} 件エラー）`}
              </p>
              {result.errors.length > 0 && (
                <ul className="mt-2 text-xs list-disc list-inside space-y-0.5 max-h-32 overflow-y-auto">
                  {result.errors.map((err, i) => (
                    <li key={i}>{err}</li>
                  ))}
                </ul>
              )}
            </div>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
}

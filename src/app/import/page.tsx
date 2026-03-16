"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { useAuthStore, canEditPL } from "@/stores/authStore";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Label } from "@/components/ui/label";
import { Upload, FileSpreadsheet } from "lucide-react";

interface Location {
  id: string;
  code: string;
  name: string;
}

export default function ImportPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const [locations, setLocations] = useState<Location[]>([]);
  const [locationId, setLocationId] = useState("");
  const [yearMonth, setYearMonth] = useState(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
  });
  const [file, setFile] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<{ success: number; errors: string[] } | null>(null);

  const fetchLocations = async () => {
    const res = await fetchApi("/api/locations");
    const data = await res.json();
    setLocations(data);
    if (data.length > 0 && !locationId) {
      setLocationId(data[0].id);
    }
  };

  useEffect(() => {
    if (!loadingAuth && user && !canEditPL(user.role)) {
      router.replace("/forbidden");
      return;
    }
  }, [user, loadingAuth, router]);

  useEffect(() => {
    fetchLocations();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const yearMonths: string[] = [];
  const now = new Date();
  for (let i = -6; i <= 6; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
    yearMonths.push(
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`
    );
  }
  yearMonths.reverse();

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const f = e.target.files?.[0];
    setFile(f || null);
    setResult(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!file || !locationId || !yearMonth) return;

    setLoading(true);
    setResult(null);

    const formData = new FormData();
    formData.append("file", file);
    formData.append("locationId", locationId);
    formData.append("yearMonth", yearMonth);

    try {
      const res = await fetchApi("/api/import", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();
      setResult(data);
      if (data.success > 0) {
        setFile(null);
      }
    } catch (err) {
      setResult({ success: 0, errors: ["アップロードに失敗しました"] });
    } finally {
      setLoading(false);
    }
  };

  if (!loadingAuth && user && !canEditPL(user.role)) {
    return null;
  }

  return (
    <div>
      <h1 className="text-3xl font-bold tracking-tight mb-8">データインポート</h1>

      <div className="max-w-2xl space-y-8">
        <div className="border border-excel-grid bg-muted/50 p-6">
          <h2 className="font-semibold mb-3 flex items-center gap-2 text-sm">
            <FileSpreadsheet className="h-4 w-4 text-muted-foreground" />
            対応フォーマット（CSV / Excel）
          </h2>
          <p className="text-sm text-muted-foreground mb-3">
            以下の形式でファイルを用意してください（CSVまたは.xlsx/.xls）：
          </p>
          <pre className="text-xs bg-background/80 p-4 border border-excel-grid overflow-x-auto">
{`コース名,勘定科目名,金額
001-001,山崎製パン,100000
001-001,乗務員給料,50000
001-002,山崎製パン,200000`}
          </pre>
          <ul className="text-sm text-muted-foreground mt-3 space-y-1 list-disc list-inside">
            <li>1行目: ヘッダー行（スキップされます）</li>
            <li>コース名: 拠点内のコース名（例: 001-001）</li>
            <li>勘定科目名: 山崎製パン、乗務員給料 など</li>
            <li>金額: 数値（カンマ区切り可）</li>
            <li>Excelの場合は1シート目が対象</li>
          </ul>
        </div>

        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="grid gap-1.5">
            <Label className="text-sm text-muted-foreground">拠点</Label>
            <Select
              value={locationId}
              onValueChange={setLocationId}
              required
            >
              <SelectTrigger onFocus={fetchLocations}>
                <SelectValue placeholder="拠点を選択" />
              </SelectTrigger>
              <SelectContent>
                {locations.map((loc) => (
                  <SelectItem key={loc.id} value={loc.id}>
                    {loc.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="grid gap-1.5">
            <Label className="text-sm text-muted-foreground">対象年月</Label>
            <Select value={yearMonth} onValueChange={setYearMonth} required>
              <SelectTrigger>
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

          <div className="grid gap-1.5">
            <Label className="text-sm text-muted-foreground">CSV / Excelファイル</Label>
            <input
              type="file"
              accept=".csv,.xlsx,.xls"
              onChange={handleFileChange}
              className="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1.5 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
            />
          </div>

          <Button type="submit" disabled={!file || loading} size="sm">
            <Upload className="h-4 w-4 mr-1.5" />
            {loading ? "インポート中..." : "インポート"}
          </Button>
        </form>

        {result && (
          <div
            className={`p-4 rounded-sm text-sm ${
              result.errors.length > 0
                ? "bg-destructive/5 text-destructive"
                : "bg-muted/60 text-foreground"
            }`}
          >
            <p className="font-medium">
              {result.success} 件のデータをインポートしました
            </p>
            {result.errors.length > 0 && (
              <ul className="mt-2 text-sm list-disc list-inside">
                {result.errors.map((err, i) => (
                  <li key={i}>{err}</li>
                ))}
              </ul>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

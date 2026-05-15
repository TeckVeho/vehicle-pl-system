"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { useAuthStore, canManageMaster } from "@/stores/authStore";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Database, Loader2, CheckCircle2, CircleDashed, AlertCircle } from "lucide-react";
import { YearMonthPicker } from "@/components/common/YearMonthPicker";

interface Location {
  id: string;
  code: string;
  name: string;
  spreadsheetId: string | null;
}

type SaveState = "idle" | "saving" | "success" | "error";

interface RowState {
  value: string;
  saveState: SaveState;
  errorMessage: string | null;
}

function extractSheetId(input: string): string {
  const trimmed = input.trim();
  // URLからIDを抽出 (https://docs.google.com/spreadsheets/d/{id}/...)
  const match = trimmed.match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  if (match) return match[1];
  return trimmed;
}

export default function LocationsPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const [locations, setLocations] = useState<Location[]>([]);
  const [loading, setLoading] = useState(true);
  const [rowStates, setRowStates] = useState<Record<string, RowState>>({});

  useEffect(() => {
    if (!loadingAuth && user && !canManageMaster(user.role)) {
      router.replace("/forbidden");
    }
  }, [user, loadingAuth, router]);

  const fetchLocations = useCallback(async () => {
    setLoading(true);
    try {
      const res = await fetchApi("/api/locations");
      const data: Location[] = await res.json();
      setLocations(data);
      const states: Record<string, RowState> = {};
      for (const loc of data) {
        states[loc.id] = {
          value: loc.spreadsheetId ?? "",
          saveState: "idle",
          errorMessage: null,
        };
      }
      setRowStates(states);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchLocations();
  }, [fetchLocations]);

  const handleValueChange = (locationId: string, value: string) => {
    setRowStates((prev) => ({
      ...prev,
      [locationId]: { ...prev[locationId], value, saveState: "idle", errorMessage: null },
    }));
  };

  const handleSave = async (locationId: string) => {
    if (!canEdit) return;
    const row = rowStates[locationId];
    if (!row) return;

    const sheetId = extractSheetId(row.value) || null;

    setRowStates((prev) => ({
      ...prev,
      [locationId]: { ...prev[locationId], saveState: "saving", errorMessage: null },
    }));

    try {
      const res = await fetchApi(`/api/locations/${locationId}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ spreadsheetId: sheetId }),
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error((err as { error?: string }).error ?? "保存に失敗しました");
      }

      const updated: Location = await res.json();
      setLocations((prev) =>
        prev.map((loc) => (loc.id === locationId ? updated : loc))
      );
      setRowStates((prev) => ({
        ...prev,
        [locationId]: {
          value: updated.spreadsheetId ?? "",
          saveState: "success",
          errorMessage: null,
        },
      }));

      // 3秒後にsuccess状態をリセット
      setTimeout(() => {
        setRowStates((prev) => ({
          ...prev,
          [locationId]: { ...prev[locationId], saveState: "idle" },
        }));
      }, 3000);
    } catch (e) {
      setRowStates((prev) => ({
        ...prev,
        [locationId]: {
          ...prev[locationId],
          saveState: "error",
          errorMessage: e instanceof Error ? e.message : "保存に失敗しました",
        },
      }));
    }
  };

  const handleClear = async (locationId: string) => {
    if (!canEdit) return;
    setRowStates((prev) => ({
      ...prev,
      [locationId]: { value: "", saveState: "saving", errorMessage: null },
    }));

    try {
      const res = await fetchApi(`/api/locations/${locationId}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ spreadsheetId: null }),
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error((err as { error?: string }).error ?? "クリアに失敗しました");
      }

      const updated: Location = await res.json();
      setLocations((prev) =>
        prev.map((loc) => (loc.id === locationId ? updated : loc))
      );
      setRowStates((prev) => ({
        ...prev,
        [locationId]: { value: "", saveState: "idle", errorMessage: null },
      }));
    } catch (e) {
      setRowStates((prev) => ({
        ...prev,
        [locationId]: {
          ...prev[locationId],
          value: locations.find((l) => l.id === locationId)?.spreadsheetId ?? "",
          saveState: "error",
          errorMessage: e instanceof Error ? e.message : "クリアに失敗しました",
        },
      }));
    }
  };

  const isDirty = (locationId: string) => {
    const loc = locations.find((l) => l.id === locationId);
    if (!loc) return false;
    const row = rowStates[locationId];
    if (!row) return false;
    const currentExtracted = extractSheetId(row.value) || null;
    return currentExtracted !== (loc.spreadsheetId ?? null);
  };

  return (
    <div className="min-h-screen">
      <div className="mb-10">
        <div className="flex items-center justify-between gap-4 mb-2">
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <Database className="h-6 w-6" />
            </div>
            <h1 className="text-3xl font-semibold tracking-tight text-foreground">
              拠点スプレッドシート設定
            </h1>
          </div>
          <YearMonthPicker />
        </div>
        <p className="text-[15px] text-muted-foreground ml-[60px] leading-relaxed">
          各拠点の売上データを参照する Google Sheets ID を設定します。
          URL（<code className="text-xs bg-muted px-1 py-0.5 rounded">https://docs.google.com/spreadsheets/d/...</code>）または Sheets ID をそのまま入力できます。
        </p>
      </div>

      {loading ? (
        <div className="flex items-center gap-3 py-12 text-muted-foreground">
          <div className="flex gap-1">
            <span className="h-2 w-2 rounded-full bg-current animate-[loading-dot_1.2s_ease-in-out_infinite]" />
            <span className="h-2 w-2 rounded-full bg-current animate-[loading-dot_1.2s_ease-in-out_infinite] [animation-delay:0.2s]" />
            <span className="h-2 w-2 rounded-full bg-current animate-[loading-dot_1.2s_ease-in-out_infinite] [animation-delay:0.4s]" />
          </div>
          <span className="text-sm">読み込み中...</span>
        </div>
      ) : (
        <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-sm">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                <th className="px-5 py-4 text-left text-sm font-semibold text-foreground w-[160px]">
                  拠点
                </th>
                <th className="px-5 py-4 text-left text-sm font-semibold text-foreground">
                  Sheets ID / URL
                </th>
                <th className="px-5 py-4 text-left text-sm font-semibold text-foreground w-[120px]">
                  状態
                </th>
                {canEdit && (
                  <th className="px-5 py-4 text-left text-sm font-semibold text-foreground w-[140px]">
                    操作
                  </th>
                )}
              </tr>
            </thead>
            <tbody>
              {locations.map((loc) => {
                const row = rowStates[loc.id];
                const dirty = isDirty(loc.id);
                const configured = !!loc.spreadsheetId;

                return (
                  <tr
                    key={loc.id}
                    className="border-b border-border/60 hover:bg-muted/20 transition-colors"
                  >
                    <td className="px-5 py-3 font-medium text-foreground">
                      {loc.name}
                    </td>
                    <td className="px-5 py-3">
                      {canEdit ? (
                        <Input
                          type="text"
                          value={row?.value ?? ""}
                          onChange={(e) => handleValueChange(loc.id, e.target.value)}
                          placeholder="Sheets ID または Google Sheets URL を貼り付け"
                          className="font-mono text-sm"
                          disabled={row?.saveState === "saving"}
                        />
                      ) : (
                        <span className="font-mono text-sm text-foreground">
                          {loc.spreadsheetId ?? (
                            <span className="text-muted-foreground italic">未設定</span>
                          )}
                        </span>
                      )}
                      {row?.saveState === "error" && row.errorMessage && (
                        <p className="text-xs text-destructive mt-1 flex items-center gap-1">
                          <AlertCircle className="h-3 w-3 shrink-0" />
                          {row.errorMessage}
                        </p>
                      )}
                    </td>
                    <td className="px-5 py-3">
                      {row?.saveState === "saving" ? (
                        <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                          <Loader2 className="h-3.5 w-3.5 animate-spin" />
                          保存中...
                        </span>
                      ) : row?.saveState === "success" ? (
                        <span className="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                          <CheckCircle2 className="h-3.5 w-3.5" />
                          保存済
                        </span>
                      ) : configured ? (
                        <span className="inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5 font-medium">
                          <CheckCircle2 className="h-3 w-3" />
                          設定済
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground bg-muted border border-border rounded-full px-2 py-0.5">
                          <CircleDashed className="h-3 w-3" />
                          未設定
                        </span>
                      )}
                    </td>
                    {canEdit && (
                      <td className="px-5 py-3">
                        <div className="flex items-center gap-2">
                          <Button
                            size="sm"
                            onClick={() => handleSave(loc.id)}
                            disabled={!dirty || row?.saveState === "saving"}
                            className="h-7 text-xs px-3"
                          >
                            保存
                          </Button>
                          {configured && (
                            <Button
                              size="sm"
                              variant="ghost"
                              onClick={() => handleClear(loc.id)}
                              disabled={row?.saveState === "saving"}
                              className="h-7 text-xs px-3 text-muted-foreground hover:text-destructive"
                            >
                              クリア
                            </Button>
                          )}
                        </div>
                      </td>
                    )}
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { extractFolderId } from "@/lib/google-drive-id";
import { useAuthStore, canManageMaster } from "@/stores/authStore";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Database,
  Loader2,
  CheckCircle2,
  CircleDashed,
  AlertCircle,
  FolderOpen,
} from "lucide-react";
import { YearMonthPicker } from "@/components/common/YearMonthPicker";

interface Location {
  id: string;
  code: string;
  name: string;
  spreadsheetId: string | null;
}

interface SpreadsheetRef {
  id: string;
  name: string;
}

type SaveState = "idle" | "saving" | "success" | "error";
type FolderFetchState = "idle" | "loading" | "success" | "error";

interface RowState {
  value: string;
  saveState: SaveState;
  errorMessage: string | null;
}

const SELECT_NONE = "__none__";

function extractSheetId(input: string): string {
  const trimmed = input.trim();
  const match = trimmed.match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  if (match) return match[1];
  return trimmed;
}

function findSpreadsheetByLocationName(
  spreadsheets: SpreadsheetRef[],
  locationName: string
): SpreadsheetRef | undefined {
  return spreadsheets.find((s) => s.name.includes(locationName));
}

export default function LocationsPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const [locations, setLocations] = useState<Location[]>([]);
  const [loading, setLoading] = useState(true);
  const [rowStates, setRowStates] = useState<Record<string, RowState>>({});

  const [folderInput, setFolderInput] = useState("");
  const [folderFetchState, setFolderFetchState] =
    useState<FolderFetchState>("idle");
  const [folderFetchError, setFolderFetchError] = useState<string | null>(null);
  const [availableSpreadsheets, setAvailableSpreadsheets] = useState<
    SpreadsheetRef[]
  >([]);
  const [manualInputRows, setManualInputRows] = useState<Record<string, boolean>>(
    {}
  );
  const [suggestSummary, setSuggestSummary] = useState<string | null>(null);

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

  const handleFetchSpreadsheets = async () => {
    if (!canEdit) return;
    const folderId = extractFolderId(folderInput);
    if (!folderId) {
      setFolderFetchState("error");
      setFolderFetchError("フォルダ URL またはフォルダ ID を入力してください");
      setAvailableSpreadsheets([]);
      return;
    }

    setFolderFetchState("loading");
    setFolderFetchError(null);
    setSuggestSummary(null);

    try {
      const res = await fetchApi(
        `/api/drive/spreadsheets?folderId=${encodeURIComponent(folderId)}`
      );
      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(
          (err as { error?: string }).error ?? "一覧の取得に失敗しました"
        );
      }
      const data = (await res.json()) as { spreadsheets: SpreadsheetRef[] };
      setAvailableSpreadsheets(data.spreadsheets ?? []);
      setFolderFetchState("success");
      setManualInputRows({});
    } catch (e) {
      setAvailableSpreadsheets([]);
      setFolderFetchState("error");
      setFolderFetchError(
        e instanceof Error ? e.message : "一覧の取得に失敗しました"
      );
    }
  };

  const handleSuggestByName = () => {
    if (availableSpreadsheets.length === 0) return;
    let suggested = 0;
    let skipped = 0;

    setRowStates((prev) => {
      const next = { ...prev };
      for (const loc of locations) {
        const match = findSpreadsheetByLocationName(
          availableSpreadsheets,
          loc.name
        );
        if (match) {
          next[loc.id] = {
            ...next[loc.id],
            value: match.id,
            saveState: "idle",
            errorMessage: null,
          };
          suggested++;
        } else {
          skipped++;
        }
      }
      return next;
    });

    setManualInputRows({});
    setSuggestSummary(
      `${suggested} 拠点を提案しました。${skipped} 拠点は手動での設定が必要です。内容を確認してから各行の「保存」を押してください。`
    );
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

  const shouldUseDropdown = (locationId: string) =>
    availableSpreadsheets.length > 0 && !manualInputRows[locationId];

  const getOtherLocationsUsingSheet = (
    locationId: string,
    spreadsheetId: string
  ): string[] => {
    if (!spreadsheetId) return [];
    return locations
      .filter((l) => {
        if (l.id === locationId) return false;
        const otherValue = rowStates[l.id]?.value ?? "";
        return extractSheetId(otherValue) === spreadsheetId;
      })
      .map((l) => l.name);
  };

  const renderSheetInput = (loc: Location) => {
    const row = rowStates[loc.id];
    const disabled = row?.saveState === "saving";

    if (!canEdit) {
      return (
        <span className="font-mono text-sm text-foreground">
          {loc.spreadsheetId ?? (
            <span className="text-muted-foreground italic">未設定</span>
          )}
        </span>
      );
    }

    if (shouldUseDropdown(loc.id)) {
      const currentId = extractSheetId(row?.value ?? "");
      const selectValue = currentId || SELECT_NONE;
      const orphanId =
        currentId &&
        !availableSpreadsheets.some((s) => s.id === currentId)
          ? currentId
          : null;

      return (
        <div className="space-y-1">
          <Select
            value={selectValue}
            onValueChange={(v) =>
              handleValueChange(loc.id, v === SELECT_NONE ? "" : v)
            }
            disabled={disabled}
          >
            <SelectTrigger className="font-mono text-sm">
              <SelectValue placeholder="スプレッドシートを選択" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value={SELECT_NONE}>未選択</SelectItem>
              {orphanId && (
                <SelectItem value={orphanId}>
                  （現在の設定）{orphanId}
                </SelectItem>
              )}
              {availableSpreadsheets.map((s) => {
                const usedBy = getOtherLocationsUsingSheet(loc.id, s.id);
                const suffix =
                  usedBy.length > 0
                    ? `（${usedBy.join("、")} で使用中）`
                    : "";
                return (
                  <SelectItem key={s.id} value={s.id}>
                    {s.name}
                    {suffix}
                  </SelectItem>
                );
              })}
            </SelectContent>
          </Select>
          <button
            type="button"
            className="text-xs text-primary hover:underline"
            onClick={() =>
              setManualInputRows((prev) => ({ ...prev, [loc.id]: true }))
            }
          >
            手入力で設定
          </button>
        </div>
      );
    }

    return (
      <div className="space-y-1">
        <Input
          type="text"
          value={row?.value ?? ""}
          onChange={(e) => handleValueChange(loc.id, e.target.value)}
          placeholder="Sheets ID または Google Sheets URL を貼り付け"
          className="font-mono text-sm"
          disabled={disabled}
        />
        {availableSpreadsheets.length > 0 && (
          <button
            type="button"
            className="text-xs text-primary hover:underline"
            onClick={() =>
              setManualInputRows((prev) => {
                const next = { ...prev };
                delete next[loc.id];
                return next;
              })
            }
          >
            一覧から選択
          </button>
        )}
      </div>
    );
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
          各拠点の売上データを参照する Google スプレッドシートを設定します。
          Google Drive のフォルダをサービスアカウント（閲覧者）に共有したうえで、フォルダから一覧を取得して紐付けできます。
          一覧を取得しない場合は、従来どおり Sheets の URL（
          <code className="text-xs bg-muted px-1 py-0.5 rounded">
            https://docs.google.com/spreadsheets/d/...
          </code>
          ）または file ID を直接入力できます。
        </p>
      </div>

      {canEdit && (
        <div className="mb-8 rounded-2xl border border-border bg-card p-5 shadow-sm">
          <div className="flex items-center gap-2 mb-3">
            <FolderOpen className="h-5 w-5 text-primary" />
            <h2 className="text-base font-semibold text-foreground">
              Drive フォルダから一覧取得
            </h2>
          </div>
          <p className="text-sm text-muted-foreground mb-4">
            フォルダ URL（
            <code className="text-xs bg-muted px-1 py-0.5 rounded">
              https://drive.google.com/drive/folders/...
            </code>
            ）またはフォルダ ID を入力し、フォルダ内のスプレッドシート（Google シートおよび .xlsx）一覧を取得します。
          </p>
          <div className="flex flex-wrap items-end gap-3">
            <div className="flex-1 min-w-[240px]">
              <Input
                type="text"
                value={folderInput}
                onChange={(e) => {
                  setFolderInput(e.target.value);
                  if (folderFetchState === "error") {
                    setFolderFetchState("idle");
                    setFolderFetchError(null);
                  }
                }}
                placeholder="Drive フォルダ URL またはフォルダ ID"
                className="font-mono text-sm"
                disabled={folderFetchState === "loading"}
              />
            </div>
            <Button
              onClick={handleFetchSpreadsheets}
              disabled={folderFetchState === "loading" || !folderInput.trim()}
            >
              {folderFetchState === "loading" ? (
                <>
                  <Loader2 className="h-4 w-4 animate-spin mr-2" />
                  取得中...
                </>
              ) : (
                "一覧取得"
              )}
            </Button>
            {folderFetchState === "success" && availableSpreadsheets.length > 0 && (
              <Button variant="outline" onClick={handleSuggestByName}>
                名前で自動提案
              </Button>
            )}
          </div>

          {folderFetchState === "error" && folderFetchError && (
            <p className="text-sm text-destructive mt-3 flex items-center gap-1.5">
              <AlertCircle className="h-4 w-4 shrink-0" />
              {folderFetchError}
            </p>
          )}

          {suggestSummary && (
            <p className="text-sm text-muted-foreground mt-3">{suggestSummary}</p>
          )}

          {folderFetchState === "success" && (
            <div className="mt-4 text-sm text-foreground">
              <p className="font-medium">
                {availableSpreadsheets.length} 件のスプレッドシートを取得しました
              </p>
              {availableSpreadsheets.length > 0 && (
                <details className="mt-2">
                  <summary className="cursor-pointer text-xs text-muted-foreground hover:text-foreground">
                    取得一覧を表示
                  </summary>
                  <ul className="mt-2 max-h-48 overflow-y-auto rounded-lg border border-border divide-y divide-border/60">
                    {availableSpreadsheets.map((s) => (
                      <li
                        key={s.id}
                        className="px-3 py-2 flex flex-wrap gap-x-3 gap-y-0.5"
                      >
                        <span className="font-medium">{s.name}</span>
                        <span className="font-mono text-xs text-muted-foreground">
                          {s.id}
                        </span>
                      </li>
                    ))}
                  </ul>
                </details>
              )}
            </div>
          )}
        </div>
      )}

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
                  スプレッドシート
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
                      {renderSheetInput(loc)}
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

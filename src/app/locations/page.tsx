"use client";

import { useState, useEffect, useCallback, useRef } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { extractFolderId } from "@/lib/google-drive-id";
import { useAuthStore, canManageMaster } from "@/stores/authStore";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Database,
  Loader2,
  CheckCircle2,
  CircleDashed,
  AlertCircle,
  FolderOpen,
} from "lucide-react";
import { YearMonthPicker } from "@/components/common/YearMonthPicker";
import { useYearMonthStore } from "@/stores/yearMonthStore";
import { findSpreadsheetForLocation } from "@/lib/spreadsheet-file-match";
import {
  loadLocationsDriveCache,
  saveLocationsDriveCache,
} from "@/lib/locations-drive-cache";

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

type SyncState = "idle" | "syncing" | "success" | "error" | "unmatched";

interface RowSyncState {
  status: SyncState;
  errorMessage: string | null;
  matchedFileName: string | null;
  matchedFileId: string | null;
}

type FolderFetchState = "idle" | "loading" | "success" | "error";

export default function LocationsPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const yearMonth = useYearMonthStore((s) => s.yearMonth);
  const [locations, setLocations] = useState<Location[]>([]);
  const [loading, setLoading] = useState(true);
  const [rowStates, setRowStates] = useState<Record<string, RowSyncState>>({});

  const [folderInput, setFolderInput] = useState("");
  const [folderFetchState, setFolderFetchState] =
    useState<FolderFetchState>("idle");
  const [folderFetchError, setFolderFetchError] = useState<string | null>(null);
  const [availableSpreadsheets, setAvailableSpreadsheets] = useState<
    SpreadsheetRef[]
  >([]);
  const [syncSummary, setSyncSummary] = useState<string | null>(null);

  useEffect(() => {
    if (!loadingAuth && user && !canManageMaster(user.role)) {
      router.replace("/forbidden");
    }
  }, [user, loadingAuth, router]);

  const initRowStates = useCallback((locs: Location[]) => {
    const states: Record<string, RowSyncState> = {};
    for (const loc of locs) {
      states[loc.id] = {
        status: "idle",
        errorMessage: null,
        matchedFileName: null,
        matchedFileId: null,
      };
    }
    setRowStates(states);
  }, []);

  const fetchLocations = useCallback(async () => {
    setLoading(true);
    try {
      const res = await fetchApi("/api/locations");
      const data: Location[] = await res.json();
      setLocations(data);
      initRowStates(data);
    } finally {
      setLoading(false);
    }
  }, [initRowStates]);

  useEffect(() => {
    fetchLocations();
  }, [fetchLocations]);

  const persistSpreadsheetId = async (
    locationId: string,
    sheetId: string | null
  ): Promise<Location> => {
    const res = await fetchApi(`/api/locations/${locationId}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ spreadsheetId: sheetId }),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error((err as { error?: string }).error ?? "保存に失敗しました");
    }
    return res.json() as Promise<Location>;
  };

  const matchForLocation = useCallback(
    (loc: Location, spreadsheets: SpreadsheetRef[]) =>
      findSpreadsheetForLocation(spreadsheets, loc.name, yearMonth),
    [yearMonth]
  );

  const applyPreviewForYearMonth = useCallback(
    (spreadsheets: SpreadsheetRef[]) => {
      setRowStates((prev) => {
        const next = { ...prev };
        for (const loc of locations) {
          const match = matchForLocation(loc, spreadsheets);
          next[loc.id] = {
            status: match ? "idle" : "unmatched",
            errorMessage: null,
            matchedFileName: match?.name ?? null,
            matchedFileId: match?.id ?? null,
          };
        }
        return next;
      });
    },
    [locations, matchForLocation]
  );

  const autoMapAndSaveAll = useCallback(async (spreadsheets: SpreadsheetRef[]) => {
    let saved = 0;
    let unchanged = 0;
    let skipped = 0;
    let failed = 0;

    for (const loc of locations) {
      const match = matchForLocation(loc, spreadsheets);
      if (!match) {
        skipped++;
        setRowStates((prev) => ({
          ...prev,
          [loc.id]: {
            status: "unmatched",
            errorMessage: null,
            matchedFileName: null,
            matchedFileId: null,
          },
        }));
        if (loc.spreadsheetId) {
          setRowStates((prev) => ({
            ...prev,
            [loc.id]: { ...prev[loc.id], status: "syncing" },
          }));
          try {
            const updated = await persistSpreadsheetId(loc.id, null);
            setLocations((prev) =>
              prev.map((l) => (l.id === loc.id ? updated : l))
            );
          } catch (e) {
            failed++;
            setRowStates((prev) => ({
              ...prev,
              [loc.id]: {
                status: "error",
                errorMessage:
                  e instanceof Error ? e.message : "クリアに失敗しました",
                matchedFileName: null,
                matchedFileId: null,
              },
            }));
          }
        }
        continue;
      }

      if (match.id === (loc.spreadsheetId ?? "")) {
        unchanged++;
        setRowStates((prev) => ({
          ...prev,
          [loc.id]: {
            status: "idle",
            errorMessage: null,
            matchedFileName: match.name,
            matchedFileId: match.id,
          },
        }));
        continue;
      }

      setRowStates((prev) => ({
        ...prev,
        [loc.id]: {
          status: "syncing",
          errorMessage: null,
          matchedFileName: match.name,
          matchedFileId: match.id,
        },
      }));

      try {
        const updated = await persistSpreadsheetId(loc.id, match.id);
        setLocations((prev) =>
          prev.map((l) => (l.id === loc.id ? updated : l))
        );
        setRowStates((prev) => ({
          ...prev,
          [loc.id]: {
            status: "success",
            errorMessage: null,
            matchedFileName: match.name,
            matchedFileId: match.id,
          },
        }));
        saved++;
        setTimeout(() => {
          setRowStates((prev) => ({
            ...prev,
            [loc.id]: { ...prev[loc.id], status: "idle" },
          }));
        }, 3000);
      } catch (e) {
        failed++;
        setRowStates((prev) => ({
          ...prev,
          [loc.id]: {
            status: "error",
            errorMessage: e instanceof Error ? e.message : "保存に失敗しました",
            matchedFileName: match.name,
            matchedFileId: match.id,
          },
        }));
      }
    }

    setSyncSummary(
      `${yearMonth} のファイル名で自動紐付けしました。保存 ${saved} 件、変更なし ${unchanged} 件、該当なし ${skipped} 件${failed > 0 ? `、失敗 ${failed} 件` : ""}。`
    );
  }, [locations, yearMonth, matchForLocation]);

  const prevYearMonthRef = useRef(yearMonth);
  const cacheRestoredRef = useRef(false);

  useEffect(() => {
    if (cacheRestoredRef.current) return;
    cacheRestoredRef.current = true;
    const cached = loadLocationsDriveCache();
    if (!cached) return;
    setFolderInput(cached.folderId);
    setAvailableSpreadsheets(cached.spreadsheets);
    setFolderFetchState("success");
  }, []);

  useEffect(() => {
    if (availableSpreadsheets.length === 0 || locations.length === 0) return;
    applyPreviewForYearMonth(availableSpreadsheets);
  }, [
    yearMonth,
    availableSpreadsheets,
    locations.length,
    applyPreviewForYearMonth,
  ]);

  useEffect(() => {
    if (prevYearMonthRef.current === yearMonth) return;
    prevYearMonthRef.current = yearMonth;

    if (availableSpreadsheets.length === 0 || locations.length === 0) return;
    if (!canEdit || folderFetchState === "loading") return;

    void (async () => {
      setSyncSummary(null);
      await autoMapAndSaveAll(availableSpreadsheets);
    })();
  }, [
    yearMonth,
    availableSpreadsheets,
    locations.length,
    canEdit,
    folderFetchState,
    autoMapAndSaveAll,
  ]);

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
    setSyncSummary(null);

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
      const spreadsheets = data.spreadsheets ?? [];
      setAvailableSpreadsheets(spreadsheets);
      setFolderFetchState("success");
      saveLocationsDriveCache({ folderId, spreadsheets });
      if (spreadsheets.length > 0 && locations.length > 0) {
        await autoMapAndSaveAll(spreadsheets);
      } else if (spreadsheets.length === 0) {
        setSyncSummary("フォルダ内にスプレッドシートがありませんでした。");
      }
    } catch (e) {
      setAvailableSpreadsheets([]);
      setFolderFetchState("error");
      setFolderFetchError(
        e instanceof Error ? e.message : "一覧の取得に失敗しました"
      );
    }
  };

  const renderSpreadsheetField = (loc: Location) => {
    const hasFolderList = availableSpreadsheets.length > 0;
    const matchForMonth = hasFolderList
      ? matchForLocation(loc, availableSpreadsheets)
      : null;

    const fileName = matchForMonth?.name ?? "";
    const fileId = matchForMonth?.id ?? "";
    const placeholder = !hasFolderList
      ? `「一覧取得」で ${yearMonth} のファイルを表示`
      : `該当ファイルなし（${yearMonth}）`;

    return (
      <div className="space-y-1.5 max-w-xl">
        {matchForMonth && (
          <p
            className="text-xs text-muted-foreground truncate"
            title={fileName}
          >
            {fileName}
          </p>
        )}
        <Input
          readOnly
          tabIndex={-1}
          value={fileId}
          placeholder={placeholder}
          className="font-mono text-sm bg-muted/40 border-border/80 cursor-default focus-visible:ring-0"
          aria-label={`${loc.name} の Google Drive ファイル ID`}
        />
      </div>
    );
  };

  const renderStatus = (loc: Location) => {
    const row = rowStates[loc.id];
    const hasFolderList = availableSpreadsheets.length > 0;
    const matchForMonth = hasFolderList
      ? matchForLocation(loc, availableSpreadsheets)
      : null;
    const configured = !!matchForMonth;

    if (row?.status === "syncing") {
      return (
        <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
          <Loader2 className="h-3.5 w-3.5 animate-spin" />
          保存中...
        </span>
      );
    }
    if (row?.status === "success") {
      return (
        <span className="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
          <CheckCircle2 className="h-3.5 w-3.5" />
          保存済
        </span>
      );
    }
    if (row?.status === "error") {
      return (
        <span className="inline-flex items-center gap-1.5 text-xs text-destructive font-medium">
          <AlertCircle className="h-3.5 w-3.5" />
          失敗
        </span>
      );
    }
    if (!hasFolderList) {
      return (
        <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground bg-muted border border-border rounded-full px-2 py-0.5">
          <CircleDashed className="h-3 w-3" />
          未設定
        </span>
      );
    }
    if (row?.status === "unmatched" || !matchForMonth) {
      return (
        <span className="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5">
          <CircleDashed className="h-3 w-3" />
          未紐付け
        </span>
      );
    }
    if (configured) {
      return (
        <span className="inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5 font-medium">
          <CheckCircle2 className="h-3 w-3" />
          設定済
        </span>
      );
    }
    return (
      <span className="inline-flex items-center gap-1.5 text-xs text-muted-foreground bg-muted border border-border rounded-full px-2 py-0.5">
        <CircleDashed className="h-3 w-3" />
        未設定
      </span>
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
          各拠点の売上データを参照する Google スプレッドシートを、Drive フォルダから一括で紐付けします。
          画面上部の年月と、ファイル名（拠点名・年月を含む「損益計算資料」）が一致するファイルを「一覧取得」で自動紐付け・DB 保存します。
          各拠点の Drive ファイル ID は表の入力欄に読み取り専用で表示されます。
          フォルダはサービスアカウント（閲覧者）に共有してください。
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
            ）またはフォルダ ID を入力し、「一覧取得」で全拠点を自動紐付けします（対象年月:{" "}
            <strong>{yearMonth}</strong>）。
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
                  取得・紐付け中...
                </>
              ) : (
                "一覧取得"
              )}
            </Button>
          </div>

          {folderFetchState === "error" && folderFetchError && (
            <p className="text-sm text-destructive mt-3 flex items-center gap-1.5">
              <AlertCircle className="h-4 w-4 shrink-0" />
              {folderFetchError}
            </p>
          )}

          {syncSummary && (
            <p className="text-sm text-muted-foreground mt-3">{syncSummary}</p>
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
                  スプレッドシート（Drive ファイル ID）
                </th>
                <th className="px-5 py-4 text-left text-sm font-semibold text-foreground w-[120px]">
                  状態
                </th>
              </tr>
            </thead>
            <tbody>
              {locations.map((loc) => {
                const row = rowStates[loc.id];

                return (
                  <tr
                    key={loc.id}
                    className="border-b border-border/60 hover:bg-muted/20 transition-colors"
                  >
                    <td className="px-5 py-3 font-medium text-foreground">
                      {loc.name}
                      <span className="block text-xs text-muted-foreground font-mono">
                        {loc.code}
                      </span>
                    </td>
                    <td className="px-5 py-3">
                      {renderSpreadsheetField(loc)}
                      {row?.status === "error" && row.errorMessage && (
                        <p className="text-xs text-destructive mt-1 flex items-center gap-1">
                          <AlertCircle className="h-3 w-3 shrink-0" />
                          {row.errorMessage}
                        </p>
                      )}
                    </td>
                    <td className="px-5 py-3">{renderStatus(loc)}</td>
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

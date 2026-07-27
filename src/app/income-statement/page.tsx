"use client";

import { Suspense, useState, useEffect, useMemo, useCallback } from "react";
import { useSearchParams } from "next/navigation";
import { FilterBar } from "@/components/income-statement/FilterBar";
import { LoadingOverlay } from "@/components/income-statement/LoadingOverlay";
import { LocationTabBar } from "@/components/income-statement/LocationTabBar";
import { PLTable, type DisplayMode } from "@/components/income-statement/PLTable";
import { PLTableSkeleton } from "@/components/income-statement/PLTableSkeleton";
import { HistoryDialog } from "@/components/income-statement/HistoryDialog";
import { Button } from "@/components/ui/button";
import { Download, History, Pencil, PencilOff } from "lucide-react";
import { getCategoryLabel } from "@/lib/calc";
import { fetchApi, getApiUrl } from "@/lib/api";
import { useAuthStore, canEditPL } from "@/stores/authStore";
import { useYearMonthStore } from "@/stores/yearMonthStore";

/** Bump when cache shape/API contract changes so stale empty payloads are dropped */
const CACHE_KEY_PREFIX = "income-statement:v6";
const METADATA_CACHE_KEY_PREFIX = "income-statement:metadata:v2";

interface PlCourseColumn {
  slotKey: string;
  id: string | null;
  name: string;
  code: string | null;
  vehicleIds?: string[];
  vehicleShares?: Array<{ vehicleId: string; sharePercent: number }>;
}

function getLocationCacheKey(yearMonth: string, locationId: string) {
  return `${CACHE_KEY_PREFIX}:${yearMonth}:${locationId}`;
}

function getMetadataCacheKey(yearMonth: string) {
  return `${METADATA_CACHE_KEY_PREFIX}:${yearMonth}`;
}

function readLocationCache(yearMonth: string, locationId: string) {
  if (typeof window === "undefined") return null;
  try {
    const raw = sessionStorage.getItem(getLocationCacheKey(yearMonth, locationId));
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function writeLocationCache(
  yearMonth: string,
  locationId: string,
  data: {
    vehicles: unknown[];
    records: Record<string, number>;
    courses?: PlCourseColumn[];
    courseRecords?: Record<string, number>;
    courseReadOnlyAccountItemIds?: string[];
    lastUpdatedAt: string | null;
  }
) {
  if (typeof window === "undefined") return;
  try {
    sessionStorage.setItem(
      getLocationCacheKey(yearMonth, locationId),
      JSON.stringify(data)
    );
  } catch {
    // sessionStorage full or unavailable
  }
}

function readMetadataCache(yearMonth: string) {
  if (typeof window === "undefined") return null;
  try {
    const raw = sessionStorage.getItem(getMetadataCacheKey(yearMonth));
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function writeMetadataCache(
  yearMonth: string,
  data: { accountItems: unknown[]; locations: unknown[] }
) {
  if (typeof window === "undefined") return;
  try {
    sessionStorage.setItem(
      getMetadataCacheKey(yearMonth),
      JSON.stringify(data)
    );
  } catch {
    // sessionStorage full or unavailable
  }
}

interface Location {
  id: string;
  code: string;
  name: string;
  spreadsheetId?: string | null;
  /** e.g. 売上明細 / 車両別損益 / YYYY-MM — backend default is 売上明細 → 車両別損益; set this to read another tab */
  spreadsheetRevenueSheet?: string | null;
}

interface Vehicle {
  id: string;
  vehicleNo: string;
  serviceType: string | null;
  location: Location;
  course?: { id: string; name: string; code: string } | null;
  atmtcCourseShares?: Array<{
    id: string | null;
    name: string;
    code: string | null;
    sharePercent: number;
  }>;
  atmtcHasUncourseRuns?: boolean;
}

interface AccountItem {
  id: string;
  code: string;
  name: string;
  category: string;
  sortOrder: number;
  isSubtotal: boolean;
  isVehicleRelated: boolean;
}

function IncomeStatementContent() {
  const searchParams = useSearchParams();
  const user = useAuthStore((s) => s.user);
  const canEdit = user ? canEditPL(user.role) : false;
  const [locations, setLocations] = useState<Location[]>([]);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [accountItems, setAccountItems] = useState<AccountItem[]>([]);
  const [records, setRecords] = useState<Record<string, number>>({});
  const [courses, setCourses] = useState<PlCourseColumn[]>([]);
  const [courseRecords, setCourseRecords] = useState<Record<string, number>>({});
  const [courseReadOnlyAccountItemIds, setCourseReadOnlyAccountItemIds] = useState<
    string[]
  >([]);
  const [lastUpdatedAt, setLastUpdatedAt] = useState<string | null>(null);

  // 年月は共有ストアを使用（ページ間で選択を保持）
  const { yearMonth: storeYearMonth, setYearMonth: setStoreYearMonth } = useYearMonthStore();
  const [yearMonth, setYearMonthLocal] = useState(() => {
    const fromUrl = searchParams.get("yearMonth");
    if (fromUrl) {
      // URL パラメータがあればストアも更新
      setStoreYearMonth(fromUrl);
      return fromUrl;
    }
    return storeYearMonth;
  });
  // yearMonth 変更時にストアも同期
  const setYearMonth = (ym: string) => {
    setYearMonthLocal(ym);
    setStoreYearMonth(ym);
  };

  const [locationId, setLocationId] = useState<string | null>(() => {
    return searchParams.get("locationId");
  });
  const plMode = searchParams.get("mode") === "vpl" ? "vpl" : "pl";
  const [searchQuery, setSearchQuery] = useState("");
  const [displayMode, setDisplayMode] = useState<DisplayMode>("course");
  const [metadataLoading, setMetadataLoading] = useState(true);
  const [dataLoading, setDataLoading] = useState(true);
  const [editMode, setEditMode] = useState(false);
  const [historyOpen, setHistoryOpen] = useState(false);
  const [apiError, setApiError] = useState<string | null>(null);

  const loading = metadataLoading || dataLoading;

  /** Legacy UI flag — backend now picks 売上明細 → 車両別損益 when no spreadsheetRevenueSheet */
  const revenueSheetTabUnset = false;

  const handleDisplayModeChange = (mode: DisplayMode) => {
    setDisplayMode(mode);
  };

  /** 勘定科目ごとの登録状況（revenue/expenseのみ、拠点内で1件以上レコードがあれば登録済み） */
  const importStatus = useMemo(() => {
    const status: Record<string, boolean> = {};
    const useCourseRevenue = courses.length > 0;
    for (const item of accountItems) {
      if (item.category !== "revenue" && item.category !== "expense") continue;
      let hasRecord = false;
      if (useCourseRevenue && item.category === "revenue") {
        hasRecord = courses.some(
          (c) => (courseRecords[`${c.slotKey}-${item.id}`] ?? 0) !== 0
        );
      } else {
        hasRecord = vehicles.some(
          (v) => (records[`${v.id}-${item.id}`] ?? 0) !== 0
        );
      }
      status[item.id] = hasRecord;
    }
    return status;
  }, [accountItems, vehicles, records, courses, courseRecords]);

  const filteredAccountItems = useMemo(() => {
    let base = accountItems;

    if (plMode === "vpl") {
      base = base.filter(
        (item) => item.isVehicleRelated || item.isSubtotal || item.category === "summary"
      );
    }

    if (!searchQuery.trim()) return base;
    const q = searchQuery.trim().toLowerCase();
    return base.filter((item) => {
      if (item.isSubtotal || item.category === "summary") return true;
      const nameMatch = item.name.toLowerCase().includes(q);
      const codeMatch = item.code.toLowerCase().includes(q);
      const categoryMatch = getCategoryLabel(item.category).toLowerCase().includes(q);
      return nameMatch || codeMatch || categoryMatch;
    });
  }, [accountItems, searchQuery, plMode]);

  const fetchMetadata = useCallback(async (ym: string) => {
    const cached = readMetadataCache(ym);
    if (cached?.locations?.length && cached?.accountItems?.length) {
      setLocations(cached.locations);
      setAccountItems(cached.accountItems);
    }

    const res = await fetchApi(`/api/income-statement/metadata?yearMonth=${ym}`);
    let data: { locations?: Location[]; accountItems?: AccountItem[]; error?: string };
    try {
      data = (await res.json()) as typeof data;
    } catch {
      setApiError("メタデータの応答を解析できませんでした。");
      return;
    }
    if (!res.ok) {
      setApiError(data.error ?? `メタデータ取得に失敗しました（${res.status}）`);
      return;
    }
    setApiError(null);
    setLocations(data.locations ?? []);
    setAccountItems(data.accountItems ?? []);
    writeMetadataCache(ym, {
      accountItems: data.accountItems ?? [],
      locations: data.locations ?? [],
    });
  }, []);

  const fetchLocationData = useCallback(
    async (
      ym: string,
      locId: string,
      isRevalidate = false
    ): Promise<{
      vehicles: Vehicle[];
      records: Record<string, number>;
      courses: PlCourseColumn[];
      courseRecords: Record<string, number>;
      courseReadOnlyAccountItemIds: string[];
      lastUpdatedAt: string | null;
    } | null> => {
      if (!isRevalidate) setDataLoading(true);
      try {
        const params = new URLSearchParams({ yearMonth: ym, locationId: locId });
        const res = await fetchApi(`/api/income-statement?${params}`);
        let data: {
          vehicles?: Vehicle[];
          courses?: PlCourseColumn[];
          records?: Record<string, number>;
          courseRecords?: Record<string, number>;
          courseReadOnlyAccountItemIds?: string[];
          lastUpdatedAt?: string | null;
          error?: string;
        };
        try {
          data = (await res.json()) as typeof data;
        } catch {
          setApiError("損益データの応答を解析できませんでした。");
          return {
            vehicles: [],
            records: {},
            courses: [],
            courseRecords: {},
            courseReadOnlyAccountItemIds: [],
            lastUpdatedAt: null,
          };
        }
        if (!res.ok) {
          setApiError(data.error ?? `損益データの取得に失敗しました（${res.status}）`);
          if (isRevalidate) return null;
          return {
            vehicles: [],
            records: {},
            courses: [],
            courseRecords: {},
            courseReadOnlyAccountItemIds: [],
            lastUpdatedAt: null,
          };
        }

        const result = {
          vehicles: data.vehicles ?? [],
          records: data.records || {},
          courses: data.courses ?? [],
          courseRecords: data.courseRecords ?? {},
          courseReadOnlyAccountItemIds: data.courseReadOnlyAccountItemIds ?? [],
          lastUpdatedAt: data.lastUpdatedAt ?? null,
        };

        writeLocationCache(ym, locId, {
          vehicles: result.vehicles,
          records: result.records,
          courses: result.courses,
          courseRecords: result.courseRecords,
          courseReadOnlyAccountItemIds: result.courseReadOnlyAccountItemIds,
          lastUpdatedAt: result.lastUpdatedAt,
        });

        setApiError(null);
        return result;
      } finally {
        if (!isRevalidate) setDataLoading(false);
      }
    },
    []
  );

  useEffect(() => {
    const ym = searchParams.get("yearMonth");
    const loc = searchParams.get("locationId");
    if (ym) setYearMonth(ym);
    if (loc) setLocationId(loc);
  }, [searchParams]);

  useEffect(() => {
    let cancelled = false;

    async function init() {
      setMetadataLoading(true);
      await fetchMetadata(yearMonth);
      if (cancelled) return;
      setMetadataLoading(false);
    }

    init();
    return () => {
      cancelled = true;
    };
  }, [yearMonth, fetchMetadata]);

  useEffect(() => {
    if (metadataLoading || locations.length === 0) return;

    setLocationId((prev) => {
      const urlLocId = searchParams.get("locationId");
      const validUrlLocId =
        urlLocId && locations.some((l) => l.id === urlLocId)
          ? urlLocId
          : null;
      const resolvedLocationId = validUrlLocId ?? locations[0].id;
      if (prev === null || !locations.some((l) => l.id === prev)) {
        return resolvedLocationId;
      }
      return prev;
    });
  }, [metadataLoading, locations, searchParams]);

  useEffect(() => {
    if (!locationId) return;

    let cancelled = false;

    const cached = readLocationCache(yearMonth, locationId);
    if (cached?.vehicles?.length) {
      setVehicles(cached.vehicles);
      setRecords(cached.records || {});
      setCourses(cached.courses ?? []);
      setCourseRecords(cached.courseRecords ?? {});
      setCourseReadOnlyAccountItemIds(cached.courseReadOnlyAccountItemIds ?? []);
      setLastUpdatedAt(cached.lastUpdatedAt ?? null);
      setDataLoading(false);
    } else {
      setDataLoading(true);
    }

    const isRevalidate = !!cached?.vehicles?.length;
    fetchLocationData(yearMonth, locationId, isRevalidate).then((result) => {
      if (cancelled || result === null) return;
      setVehicles(result.vehicles);
      setRecords(result.records);
      setCourses(result.courses);
      setCourseRecords(result.courseRecords);
      setCourseReadOnlyAccountItemIds(result.courseReadOnlyAccountItemIds);
      setLastUpdatedAt(result.lastUpdatedAt);
      setDataLoading(false);
    });

    return () => {
      cancelled = true;
    };
  }, [yearMonth, locationId, fetchLocationData]);

  const handleUpdateRecord = async (
    vehicleId: string,
    accountItemId: string,
    amount: number
  ) => {
    if (!locationId) return;
    await fetchApi("/api/income-statement/records", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ vehicleId, accountItemId, yearMonth, amount }),
    });
    const nextRecords = {
      ...records,
      [`${vehicleId}-${accountItemId}`]: amount,
    };
    setRecords(nextRecords);
    writeLocationCache(yearMonth, locationId, {
      vehicles,
      records: nextRecords,
      lastUpdatedAt: new Date().toISOString(),
    });
  };

  /** コース単位の編集：合計を車両数で均等配分して各車両を更新（bulk APIで1リクエスト） */
  const handleUpdateCourseRecord = async (
    vehicleIds: string[],
    accountItemId: string,
    totalAmount: number
  ) => {
    if (!locationId) return;
    const n = vehicleIds.length;
    if (n === 0) return;
    const perVehicle = Math.floor(totalAmount / n);
    const remainder = totalAmount - perVehicle * n;

    const recordsPayload = vehicleIds.map((vehicleId, i) => ({
      vehicleId,
      accountItemId,
      amount: perVehicle + (i === n - 1 ? remainder : 0),
    }));

    await fetchApi("/api/income-statement/records/bulk", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ yearMonth, records: recordsPayload }),
    });

    const nextRecords = { ...records };
    for (let i = 0; i < n; i++) {
      const amt = perVehicle + (i === n - 1 ? remainder : 0);
      nextRecords[`${vehicleIds[i]}-${accountItemId}`] = amt;
    }
    setRecords(nextRecords);
    writeLocationCache(yearMonth, locationId, {
      vehicles,
      records: nextRecords,
      lastUpdatedAt: new Date().toISOString(),
    });
  };

  const handleExport = () => {
    if (!locationId) return;
    const params = new URLSearchParams({ yearMonth, locationId });
    const base = getApiUrl();
    window.open(`${base}/api/income-statement/export?${params}`, "_blank");
  };

  return (
    <div className="pb-12 relative">
      <div className="flex items-center justify-between mb-6">
        <div>
          <div className="flex items-center gap-3">
            <h1 className="text-3xl font-bold tracking-tight">車両損益計算書</h1>
            {plMode === "vpl" ? (
              <span className="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 border border-blue-200">
                VPL版
              </span>
            ) : (
              <span className="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground border border-border">
                PL版
              </span>
            )}
            {loading && vehicles.length > 0 && (
              <span className="text-xs text-muted-foreground animate-pulse">
                更新中...
              </span>
            )}
          </div>
          {lastUpdatedAt && (
            <p className="text-xs text-muted-foreground mt-1">
              最終更新:{" "}
              {new Date(lastUpdatedAt).toLocaleString("ja-JP", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
              })}
            </p>
          )}
        </div>
        <div className="flex items-center gap-1.5">
          {canEdit && (
            <>
              <Button
                variant={editMode ? "default" : "ghost"}
                size="sm"
                onClick={() => setEditMode((v) => !v)}
                title={
                  editMode
                    ? "編集モードをオフにする"
                    : "編集モードをオンにする"
                }
              >
                {editMode ? (
                  <>
                    <PencilOff className="h-4 w-4 mr-1.5" />
                    編集モード終了
                  </>
                ) : (
                  <>
                    <Pencil className="h-4 w-4 mr-1.5" />
                    編集モード
                  </>
                )}
              </Button>
            </>
          )}
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setHistoryOpen(true)}
          >
            <History className="h-4 w-4 mr-1.5" />
            編集履歴
          </Button>
          <Button variant="ghost" size="sm" onClick={handleExport}>
            <Download className="h-4 w-4 mr-1.5" />
            エクスポート
          </Button>
        </div>
      </div>

      <FilterBar
        yearMonth={yearMonth}
        searchQuery={searchQuery}
        displayMode={displayMode}
        onYearMonthChange={setYearMonth}
        onSearchChange={setSearchQuery}
        onDisplayModeChange={handleDisplayModeChange}
      />

      {canEdit && editMode && (
        <div className="mb-3 flex items-center gap-2 rounded-md bg-amber-50 border border-amber-200 px-3 py-2 text-sm text-amber-800">
          <Pencil className="h-3.5 w-3.5 shrink-0" />
          <span>編集モード有効 — セルをクリックすると編集できます。Enterまたはタブキーで確定、Escapeでキャンセル。</span>
        </div>
      )}

      {apiError && (
        <div
          className="mb-3 rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive"
          role="alert"
        >
          {apiError}
        </div>
      )}

      {!apiError && revenueSheetTabUnset && locationId && (
        <div className="mb-3 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-950">
          ブックに <code className="rounded bg-sky-100/90 px-1">YYYY-MM</code> 形式のタブが無い場合（例: 「損益計算資料」テンプレの
          <code className="rounded bg-sky-100/90 px-1">売上明細</code>
          または <code className="rounded bg-sky-100/90 px-1">車両別損益</code>
          ）は、売上読み込み対象シートを
          <code className="rounded bg-sky-100/90 px-1 ml-1">spreadsheetRevenueSheet</code>
          で指定してください（Issue #1152 では <code className="rounded bg-sky-100/90 px-1">売上明細</code>）。
          <span className="block mt-1 text-xs text-sky-900/90">
            File không có sheet theo tháng (như{" "}
            <code className="rounded bg-sky-100 px-1">2026-02</code>): gọi{" "}
            <code className="rounded bg-sky-100 px-1">
              PATCH /api/locations/:id
            </code>{" "}
            kèm <code className="rounded bg-sky-100 px-1">spreadsheetId</code>{" "}
            và ví dụ{" "}
            <code className="rounded bg-sky-100 px-1">&quot;spreadsheetRevenueSheet&quot;: &quot;売上明細&quot;</code>
            （hoặc <code className="rounded bg-sky-100 px-1">車両別損益</code> tùy template）。
            Filter tháng vẫn dùng <code className="rounded bg-sky-100 px-1">2026-02</code> như trong app (Không nhất
            thiết trùng tên file *.2026.02).
          </span>
        </div>
      )}

      {!loading && !apiError && locationId && vehicles.length === 0 && locations.length > 0 && (
        <div className="mb-3 rounded-md border border-border bg-muted/40 px-3 py-2 text-sm text-muted-foreground">
          選択中の拠点に車両が登録されていません。車両マスタまたは連携データを確認してください。
        </div>
      )}

      {loading && vehicles.length === 0 ? (
        <PLTableSkeleton />
      ) : (
        <PLTable
          accountItems={filteredAccountItems}
          vehicles={vehicles}
          courses={courses}
          records={records}
          courseRecords={courseRecords}
          courseReadOnlyAccountItemIds={courseReadOnlyAccountItemIds}
          yearMonth={yearMonth}
          displayMode={displayMode}
          editMode={canEdit && editMode}
          importStatus={importStatus}
          onUpdateRecord={handleUpdateRecord}
          onUpdateCourseRecord={handleUpdateCourseRecord}
        />
      )}

      <LocationTabBar
        locationId={locationId ?? ""}
        locations={locations}
        onLocationChange={setLocationId}
      />

      <HistoryDialog
        open={historyOpen}
        yearMonth={yearMonth}
        onClose={() => setHistoryOpen(false)}
      />
    </div>
  );
}

export default function IncomeStatementPage() {
  return (
    <Suspense fallback={<LoadingOverlay message="読み込み中" />}>
      <IncomeStatementContent />
    </Suspense>
  );
}

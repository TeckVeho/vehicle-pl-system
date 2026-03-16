"use client";

import { Suspense, useState, useEffect, useMemo, useCallback } from "react";
import { useSearchParams } from "next/navigation";
import { FilterBar } from "@/components/income-statement/FilterBar";
import { LoadingOverlay } from "@/components/income-statement/LoadingOverlay";
import { LocationTabBar } from "@/components/income-statement/LocationTabBar";
import { PLTable, type DisplayMode } from "@/components/income-statement/PLTable";
import { PLTableSkeleton } from "@/components/income-statement/PLTableSkeleton";
import { ImportDialog } from "@/components/income-statement/ImportDialog";
import { HistoryDialog } from "@/components/income-statement/HistoryDialog";
import { Button } from "@/components/ui/button";
import { Download, Upload, History, Pencil, PencilOff } from "lucide-react";
import { getCategoryLabel } from "@/lib/calc";
import { fetchApi, getApiUrl } from "@/lib/api";
import { useAuthStore, canEditPL } from "@/stores/authStore";

const CACHE_KEY_PREFIX = "income-statement";
const METADATA_CACHE_KEY_PREFIX = "income-statement:metadata";

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
}

interface Vehicle {
  id: string;
  vehicleNo: string;
  serviceType: string | null;
  location: Location;
  course?: { id: string; name: string; code: string } | null;
}

interface AccountItem {
  id: string;
  code: string;
  name: string;
  category: string;
  sortOrder: number;
  isSubtotal: boolean;
}

function IncomeStatementContent() {
  const searchParams = useSearchParams();
  const user = useAuthStore((s) => s.user);
  const canEdit = user ? canEditPL(user.role) : false;
  const [locations, setLocations] = useState<Location[]>([]);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [accountItems, setAccountItems] = useState<AccountItem[]>([]);
  const [records, setRecords] = useState<Record<string, number>>({});
  const [lastUpdatedAt, setLastUpdatedAt] = useState<string | null>(null);
  const [yearMonth, setYearMonth] = useState(() => {
    const fromUrl = searchParams.get("yearMonth");
    if (fromUrl) return fromUrl;
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
  });
  const [locationId, setLocationId] = useState<string | null>(() => {
    return searchParams.get("locationId");
  });
  const [searchQuery, setSearchQuery] = useState("");
  const [displayMode, setDisplayMode] = useState<DisplayMode>("course");
  const [metadataLoading, setMetadataLoading] = useState(true);
  const [dataLoading, setDataLoading] = useState(true);
  const [editMode, setEditMode] = useState(false);
  const [importOpen, setImportOpen] = useState(false);
  const [historyOpen, setHistoryOpen] = useState(false);

  const loading = metadataLoading || dataLoading;

  const handleDisplayModeChange = (mode: DisplayMode) => {
    setDisplayMode(mode);
  };

  const filteredAccountItems = useMemo(() => {
    if (!searchQuery.trim()) return accountItems;
    const q = searchQuery.trim().toLowerCase();
    return accountItems.filter((item) => {
      if (item.isSubtotal || item.category === "summary") return true;
      const nameMatch = item.name.toLowerCase().includes(q);
      const codeMatch = item.code.toLowerCase().includes(q);
      const categoryMatch = getCategoryLabel(item.category).toLowerCase().includes(q);
      return nameMatch || codeMatch || categoryMatch;
    });
  }, [accountItems, searchQuery]);

  const fetchMetadata = useCallback(async (ym: string) => {
    const cached = readMetadataCache(ym);
    if (cached?.locations?.length && cached?.accountItems?.length) {
      setLocations(cached.locations);
      setAccountItems(cached.accountItems);
    }

    const res = await fetchApi(`/api/income-statement/metadata?yearMonth=${ym}`);
    const data = await res.json();
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
    ): Promise<{ vehicles: Vehicle[]; records: Record<string, number>; lastUpdatedAt: string | null }> => {
      if (!isRevalidate) setDataLoading(true);
      try {
        const params = new URLSearchParams({ yearMonth: ym, locationId: locId });
        const res = await fetchApi(`/api/income-statement?${params}`);
        const data = await res.json();

        const result = {
          vehicles: data.vehicles ?? [],
          records: data.records || {},
          lastUpdatedAt: data.lastUpdatedAt ?? null,
        };

        writeLocationCache(ym, locId, {
          vehicles: result.vehicles,
          records: result.records,
          lastUpdatedAt: result.lastUpdatedAt,
        });

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
      setLastUpdatedAt(cached.lastUpdatedAt ?? null);
      setDataLoading(false);
    } else {
      setDataLoading(true);
    }

    const isRevalidate = !!cached?.vehicles?.length;
    fetchLocationData(yearMonth, locationId, isRevalidate).then((result) => {
      if (cancelled) return;
      setVehicles(result.vehicles);
      setRecords(result.records);
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

  const refetchLocationData = useCallback(() => {
    if (locationId) {
      fetchLocationData(yearMonth, locationId, false).then((result) => {
        setVehicles(result.vehicles);
        setRecords(result.records);
        setLastUpdatedAt(result.lastUpdatedAt);
      });
    }
  }, [locationId, yearMonth, fetchLocationData]);

  return (
    <div className="pb-12 relative">
      <div className="flex items-center justify-between mb-6">
        <div>
          <div className="flex items-center gap-3">
            <h1 className="text-3xl font-bold tracking-tight">車両損益計算書</h1>
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
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setImportOpen(true)}
                disabled={!locationId}
                title={!locationId ? "拠点を読み込み中です" : "Excelインポート"}
              >
                <Upload className="h-4 w-4 mr-1.5" />
                インポート
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

      {loading && vehicles.length === 0 ? (
        <PLTableSkeleton />
      ) : (
        <PLTable
          accountItems={filteredAccountItems}
          vehicles={vehicles}
          records={records}
          yearMonth={yearMonth}
          displayMode={displayMode}
          editMode={canEdit && editMode}
          onUpdateRecord={handleUpdateRecord}
          onUpdateCourseRecord={handleUpdateCourseRecord}
        />
      )}

      <LocationTabBar
        locationId={locationId ?? ""}
        locations={locations}
        onLocationChange={setLocationId}
      />

      <ImportDialog
        open={importOpen}
        yearMonth={yearMonth}
        locationId={locationId ?? ""}
        onClose={() => setImportOpen(false)}
        onSuccess={refetchLocationData}
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

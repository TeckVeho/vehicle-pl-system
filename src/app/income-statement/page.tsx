"use client";

import { Suspense, useState, useEffect, useMemo, useRef } from "react";
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

function getCacheKey(yearMonth: string, locationId: string) {
  return `${CACHE_KEY_PREFIX}:${yearMonth}:${locationId}`;
}

function readCache(yearMonth: string, locationId: string) {
  if (typeof window === "undefined") return null;
  try {
    const raw = sessionStorage.getItem(getCacheKey(yearMonth, locationId));
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function writeCache(
  yearMonth: string,
  locationId: string,
  data: {
    vehicles: unknown[];
    records: Record<string, number>;
    lastUpdatedAt: string | null;
    accountItems?: unknown[];
    locations?: unknown[];
  }
) {
  if (typeof window === "undefined") return;
  try {
    sessionStorage.setItem(
      getCacheKey(yearMonth, locationId),
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
  const [locationId, setLocationId] = useState(() => {
    return searchParams.get("locationId") ?? "all";
  });
  const [searchQuery, setSearchQuery] = useState("");
  const [displayMode, setDisplayMode] = useState<DisplayMode>("course");
  const [loading, setLoading] = useState(true);
  const [editMode, setEditMode] = useState(false);
  const [importOpen, setImportOpen] = useState(false);
  const [historyOpen, setHistoryOpen] = useState(false);

  const handleDisplayModeChange = (mode: DisplayMode) => {
    setDisplayMode(mode);
    if (mode === "course" && editMode) setEditMode(false);
  };

  // 勘定科目・拠点は変化しないためキャッシュし、タブ切替時の再取得を省く
  const staticDataLoaded = useRef(false);

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

  const fetchData = async (isRevalidate = false) => {
    if (!isRevalidate) setLoading(true);
    try {
      const params = new URLSearchParams({ yearMonth });
      if (locationId !== "all") params.set("locationId", locationId);
      if (staticDataLoaded.current) params.set("skipStatic", "1");

      const res = await fetchApi(`/api/income-statement?${params}`);
      const data = await res.json();

      setVehicles(data.vehicles);
      setRecords(data.records || {});
      setLastUpdatedAt(data.lastUpdatedAt ?? null);

      if (!staticDataLoaded.current) {
        setAccountItems(data.accountItems ?? []);
        setLocations(data.locations ?? []);
        staticDataLoaded.current = true;
      }

      writeCache(yearMonth, locationId, {
        vehicles: data.vehicles,
        records: data.records || {},
        lastUpdatedAt: data.lastUpdatedAt ?? null,
        accountItems: data.accountItems,
        locations: data.locations,
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const ym = searchParams.get("yearMonth");
    const loc = searchParams.get("locationId");
    if (ym) setYearMonth(ym);
    if (loc) setLocationId(loc);
  }, [searchParams]);

  // 全拠点タブ削除に伴い、allの場合は最初の拠点にフォールバック
  useEffect(() => {
    if (locations.length > 0 && locationId === "all") {
      setLocationId(locations[0].id);
    }
  }, [locations, locationId]);

  useEffect(() => {
    const cached = readCache(yearMonth, locationId);
    if (cached?.vehicles?.length) {
      setVehicles(cached.vehicles);
      setRecords(cached.records || {});
      setLastUpdatedAt(cached.lastUpdatedAt ?? null);
      if (cached.accountItems?.length && !staticDataLoaded.current) {
        setAccountItems(cached.accountItems);
        staticDataLoaded.current = true;
      }
      if (cached.locations?.length) {
        setLocations(cached.locations);
      }
      setLoading(false);
      fetchData(true);
    } else {
      fetchData(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [yearMonth, locationId]);

  const handleUpdateRecord = async (
    vehicleId: string,
    accountItemId: string,
    amount: number
  ) => {
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
    writeCache(yearMonth, locationId, {
      vehicles,
      records: nextRecords,
      lastUpdatedAt: new Date().toISOString(),
      accountItems,
      locations,
    });
  };

  const handleExport = () => {
    const params = new URLSearchParams({ yearMonth });
    if (locationId !== "all") params.set("locationId", locationId);
    const base = getApiUrl();
    window.open(`${base}/api/income-statement/export?${params}`, "_blank");
  };

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
                disabled={displayMode === "course"}
                title={
                  displayMode === "course"
                    ? "編集は車両表示でのみ可能です"
                    : editMode
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
                disabled={locationId === "all"}
                title={locationId === "all" ? "拠点を選択してからインポートしてください" : "Excelインポート"}
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
        />
      )}

      <LocationTabBar
        locationId={locationId}
        locations={locations}
        onLocationChange={setLocationId}
      />

      <ImportDialog
        open={importOpen}
        yearMonth={yearMonth}
        locationId={locationId}
        onClose={() => setImportOpen(false)}
        onSuccess={fetchData}
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

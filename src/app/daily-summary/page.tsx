"use client";

import { Suspense, useState, useEffect } from "react";
import { fetchApi } from "@/lib/api";
import { useSearchParams } from "next/navigation";
import { DailySummaryTable } from "@/components/daily-summary/DailySummaryTable";
import { LocationTabBar } from "@/components/income-statement/LocationTabBar";
import { LoadingOverlay } from "@/components/income-statement/LoadingOverlay";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useYearMonthStore, getYears, getMonths, getMaxMonth } from "@/stores/yearMonthStore";

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



function DailySummaryContent() {
  const searchParams = useSearchParams();
  const [locations, setLocations] = useState<Location[]>([]);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [dailyAmountByVehicleByDay, setDailyAmountByVehicleByDay] = useState<
    Record<string, Record<number, number>>
  >({});
  const [monthlyTotalByVehicle, setMonthlyTotalByVehicle] = useState<
    Record<string, number>
  >({});
  const [daysInMonth, setDaysInMonth] = useState(31);

  // 年月は共有ストアを使用（ページ間で選択を保持）
  const { year, month, yearMonth: storeYearMonth, setYear, setMonth, setYearMonth: setStoreYearMonth } = useYearMonthStore();
  const [yearMonth, setYearMonthLocal] = useState(() => {
    const fromUrl = searchParams.get("yearMonth");
    if (fromUrl) {
      setStoreYearMonth(fromUrl);
      return fromUrl;
    }
    return storeYearMonth;
  });
  const setYearMonth = (ym: string) => {
    setYearMonthLocal(ym);
    setStoreYearMonth(ym);
  };
  const handleYearChange = (v: string) => {
    const newYear = Number(v);
    const max = getMaxMonth(newYear);
    const clampedMonth = month > max ? max : month;
    setYear(newYear);
    setYearMonthLocal(`${newYear}-${String(clampedMonth).padStart(2, "0")}`);
  };
  const handleMonthChange = (v: string) => {
    setMonth(Number(v));
    setYearMonthLocal(`${year}-${String(v).padStart(2, "0")}`);
  };

  const [locationId, setLocationId] = useState(() => {
    return searchParams.get("locationId") ?? "all";
  });
  const [loading, setLoading] = useState(true);

  const fetchLocations = async () => {
    const res = await fetchApi("/api/locations");
    const data = await res.json();
    setLocations(data);
  };

  const fetchData = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams({ yearMonth });
      if (locationId !== "all") params.set("locationId", locationId);

      const res = await fetchApi(`/api/daily-summary?${params}`);
      const data = await res.json();

      setVehicles(data.vehicles ?? []);
      setDailyAmountByVehicleByDay(data.dailyAmountByVehicleByDay ?? {});
      setMonthlyTotalByVehicle(data.monthlyTotalByVehicle ?? {});
      setDaysInMonth(data.daysInMonth ?? 31);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLocations();
  }, []);

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
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [yearMonth, locationId]);



  if (loading && vehicles.length === 0) {
    return <LoadingOverlay message="読み込み中" />;
  }

  return (
    <div className="pb-12 relative">
      {loading && <LoadingOverlay message="データを読み込み中" />}

      <div className="flex items-center justify-between mb-6">
        <h1 className="text-3xl font-bold tracking-tight">
          日次連携データ集計
        </h1>
      </div>

      <div className="flex flex-nowrap gap-5 items-center mb-6 overflow-x-auto">
      <div className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-muted-foreground whitespace-nowrap">年</span>
          <Select value={String(year)} onValueChange={handleYearChange}>
            <SelectTrigger className="w-24">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {getYears().map((y) => (
                <SelectItem key={y} value={String(y)}>
                  {y}年
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          <span className="text-sm text-muted-foreground whitespace-nowrap">月</span>
          <Select value={String(month)} onValueChange={handleMonthChange}>
            <SelectTrigger className="w-20">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {getMonths(year).map((m) => (
                <SelectItem key={m} value={String(m)}>
                  {m}月
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      <DailySummaryTable
        vehicles={vehicles}
        dailyAmountByVehicleByDay={dailyAmountByVehicleByDay}
        monthlyTotalByVehicle={monthlyTotalByVehicle}
        daysInMonth={daysInMonth}
        yearMonth={yearMonth}
      />

      <LocationTabBar
        locationId={locationId}
        locations={locations}
        onLocationChange={setLocationId}
      />
    </div>
  );
}

export default function DailySummaryPage() {
  return (
    <Suspense fallback={<LoadingOverlay message="読み込み中" />}>
      <DailySummaryContent />
    </Suspense>
  );
}

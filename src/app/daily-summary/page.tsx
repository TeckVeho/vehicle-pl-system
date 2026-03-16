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

function getYearMonths(): string[] {
  const months: string[] = [];
  const now = new Date();
  for (let i = -12; i <= 12; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
    months.push(
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`
    );
  }
  return months.reverse();
}

function DailySummaryContent() {
  const searchParams = useSearchParams();
  const [locations, setLocations] = useState<Location[]>([]);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [dailyAmountByVehicle, setDailyAmountByVehicle] = useState<
    Record<string, number>
  >({});
  const [monthlyTotalByVehicle, setMonthlyTotalByVehicle] = useState<
    Record<string, number>
  >({});
  const [daysInMonth, setDaysInMonth] = useState(31);
  const [yearMonth, setYearMonth] = useState(() => {
    const fromUrl = searchParams.get("yearMonth");
    if (fromUrl) return fromUrl;
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
  });
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
      setDailyAmountByVehicle(data.dailyAmountByVehicle ?? {});
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

  const yearMonths = getYearMonths();

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
          <span className="text-sm text-muted-foreground whitespace-nowrap">
            年月
          </span>
          <Select value={yearMonth} onValueChange={setYearMonth}>
            <SelectTrigger className="w-36">
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
      </div>

      <DailySummaryTable
        vehicles={vehicles}
        dailyAmountByVehicle={dailyAmountByVehicle}
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

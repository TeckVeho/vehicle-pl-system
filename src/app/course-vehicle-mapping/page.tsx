"use client";

import { useState, useEffect, useMemo } from "react";
import { fetchApi } from "@/lib/api";
import { Link2, MapPin, Car, ChevronDown } from "lucide-react";
import { cn } from "@/lib/utils";

interface Location {
  id: string;
  code: string;
  name: string;
}

interface Course {
  id: string;
  name: string;
  code: string;
}

interface MappingRow {
  id: string;
  vehicleNo: string;
  serviceType: string | null;
  location: Location;
  course: Course | null;
}

export default function CourseVehicleMappingPage() {
  const [rows, setRows] = useState<MappingRow[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [locationId, setLocationId] = useState<string>("");
  const [filterOpen, setFilterOpen] = useState(false);
  const [loading, setLoading] = useState(true);

  const fetchLocations = async () => {
    const res = await fetchApi("/api/locations");
    const data = await res.json();
    setLocations(data);
    if (data.length > 0 && !locationId) {
      setLocationId("all");
    }
  };

  const fetchMapping = async () => {
    const effectiveLocationId =
      locationId && locationId !== "all" ? locationId : undefined;
    const url = effectiveLocationId
      ? `/api/vehicles?locationId=${effectiveLocationId}`
      : "/api/vehicles";
    const res = await fetchApi(url);
    const data = await res.json();
    setRows(data);
  };

  useEffect(() => {
    fetchLocations();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    setLoading(true);
    fetchMapping().finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [locationId]);

  // 拠点 → コース → 車両 の階層でグルーピング（1コースに複数車両の可能性あり）
  const groupedByLocationAndCourse = useMemo(() => {
    const locMap = new Map<
      string,
      {
        location: Location;
        courses: Map<
          string,
          { course: Course | null; vehicles: MappingRow[] }
        >;
      }
    >();
    for (const row of rows) {
      const locKey = row.location.id;
      if (!locMap.has(locKey)) {
        locMap.set(locKey, {
          location: row.location,
          courses: new Map(),
        });
      }
      const loc = locMap.get(locKey)!;
      const courseKey = row.course?.id ?? "__no_course__";
      if (!loc.courses.has(courseKey)) {
        loc.courses.set(courseKey, {
          course: row.course,
          vehicles: [],
        });
      }
      loc.courses.get(courseKey)!.vehicles.push(row);
    }
    return Array.from(locMap.values()).map((loc) => ({
      ...loc,
      courses: Array.from(loc.courses.values()),
    }));
  }, [rows]);

  const selectedLocationName =
    locationId === "all"
      ? "すべての拠点"
      : locations.find((l) => l.id === locationId)?.name ?? "拠点を選択";

  return (
    <div className="min-h-screen">
      {/* タイトルエリア - Notion風 */}
      <div className="mb-10">
        <div className="flex items-center gap-3 mb-2">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <Link2 className="h-6 w-6" />
          </div>
          <h1 className="text-3xl font-semibold tracking-tight text-foreground">
            コース・車両マッピング
          </h1>
        </div>
        <p className="text-[15px] text-muted-foreground ml-[60px] leading-relaxed">
          コースと車両の紐づけを閲覧できます。<span className="text-foreground/80">閲覧のみ</span>
        </p>
      </div>

      {/* フィルター - Notion風ドロップダウン */}
      <div className="mb-8 relative">
        <button
          type="button"
          onClick={() => setFilterOpen((o) => !o)}
          className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-muted/50 hover:bg-muted text-foreground border border-transparent hover:border-border transition-colors"
        >
          <MapPin className="h-3.5 w-3.5 text-muted-foreground" />
          {selectedLocationName}
          <ChevronDown
            className={cn(
              "h-3.5 w-3.5 text-muted-foreground transition-transform",
              filterOpen && "rotate-180"
            )}
          />
        </button>
        {filterOpen && (
          <>
            <div
              className="fixed inset-0 z-40"
              onClick={() => setFilterOpen(false)}
              aria-hidden
            />
            <div className="absolute top-full left-0 mt-1 z-50 min-w-[200px] rounded-xl border border-border bg-popover shadow-lg py-1 overflow-hidden">
              <button
                type="button"
                onClick={() => {
                  setLocationId("all");
                  setFilterOpen(false);
                }}
                className={cn(
                  "w-full px-3 py-2 text-left text-sm hover:bg-muted/60 transition-colors",
                  locationId === "all" && "bg-muted/40 font-medium"
                )}
              >
                すべての拠点
              </button>
              {locations.map((loc) => (
                <button
                  key={loc.id}
                  type="button"
                  onClick={() => {
                    setLocationId(loc.id);
                    setFilterOpen(false);
                  }}
                  className={cn(
                    "w-full px-3 py-2 text-left text-sm hover:bg-muted/60 transition-colors",
                    locationId === loc.id && "bg-muted/40 font-medium"
                  )}
                >
                  {loc.name}
                </button>
              ))}
            </div>
          </>
        )}
      </div>

      {/* コンテンツ */}
      {loading ? (
        <div className="flex items-center gap-3 py-12 text-muted-foreground">
          <div className="flex gap-1">
            <span className="h-2 w-2 rounded-full bg-current animate-[loading-dot_1.2s_ease-in-out_infinite]" />
            <span className="h-2 w-2 rounded-full bg-current animate-[loading-dot_1.2s_ease-in-out_infinite] [animation-delay:0.2s]" />
            <span className="h-2 w-2 rounded-full bg-current animate-[loading-dot_1.2s_ease-in-out_infinite] [animation-delay:0.4s]" />
          </div>
          <span className="text-sm">読み込み中...</span>
        </div>
      ) : rows.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border bg-muted/20 py-16 px-8 text-center">
          <Car className="h-12 w-12 text-muted-foreground/50 mx-auto mb-4" />
          <p className="text-muted-foreground font-medium">データがありません</p>
          <p className="text-sm text-muted-foreground/80 mt-1">
            拠点を選択するか、データを登録してください
          </p>
        </div>
      ) : (
        <div className="space-y-6">
          {groupedByLocationAndCourse.map(({ location, courses }) => (
            <div
              key={location.id}
              className="rounded-2xl border border-border bg-card overflow-hidden shadow-sm hover:shadow-md transition-shadow"
            >
              {/* 拠点ヘッダー */}
              <div className="px-5 py-4 border-b border-border/60 bg-muted/30">
                <div className="flex items-center gap-2">
                  <MapPin className="h-4 w-4 text-primary" />
                  <h2 className="font-semibold text-foreground">
                    {location.name}
                  </h2>
                  <span className="text-xs text-muted-foreground font-medium px-2 py-0.5 rounded-md bg-muted">
                    {courses.reduce((s, c) => s + c.vehicles.length, 0)} 台 / {courses.length} コース
                  </span>
                </div>
              </div>
              {/* コースごとに車両一覧（1コースに複数車両） */}
              <div className="divide-y divide-border/40">
                {courses.map(({ course, vehicles }) => (
                  <div key={course?.id ?? "__no_course__"}>
                    {/* コースヘッダー（1コースに複数車両） */}
                    <div className="px-5 py-3 bg-muted/10 border-b border-border/30 flex items-center justify-between gap-4">
                      <div>
                        <div className="font-medium text-foreground">
                          {course?.name ?? "未紐づけ"}
                        </div>
                        <div className="text-xs text-muted-foreground mt-0.5 font-mono">
                          {course?.code ?? "—"}
                        </div>
                      </div>
                      <span className="text-xs text-muted-foreground shrink-0">
                        {vehicles.length} 台
                      </span>
                    </div>
                    {/* 車両一覧 */}
                    <div className="divide-y divide-border/30">
                      {vehicles.map((row) => (
                        <div
                          key={row.id}
                          className="px-5 py-2.5 pl-8 flex items-center gap-4 hover:bg-muted/20 transition-colors"
                        >
                          <div className="flex items-center gap-1.5">
                            <Car className="h-3.5 w-3.5 text-muted-foreground" />
                            <span className="font-mono text-sm font-medium">
                              {row.vehicleNo}
                            </span>
                          </div>
                          {row.serviceType && (
                            <span className="text-xs px-2 py-0.5 rounded-md bg-muted/60 text-muted-foreground">
                              {row.serviceType}
                            </span>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

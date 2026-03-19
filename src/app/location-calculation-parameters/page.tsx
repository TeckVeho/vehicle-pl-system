"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
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
import { Settings2, Loader2 } from "lucide-react";

interface Location {
  id: string;
  code: string;
  name: string;
}

interface ParamItem {
  id: string | null;
  locationId: string;
  location: { id: string; code: string; name: string };
  yearMonth: string;
  fuelUnitPrice: number;
  roadUsageDiscountRate: number;
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

export default function LocationCalculationParametersPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const [locations, setLocations] = useState<Location[]>([]);
  const [params, setParams] = useState<ParamItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [yearMonth, setYearMonth] = useState(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
  });
  const [localValues, setLocalValues] = useState<
    Record<string, { fuelUnitPrice: string; roadUsageDiscountRate: string }>
  >({});

  const fetchLocations = useCallback(async () => {
    const res = await fetchApi("/api/locations");
    const data = await res.json();
    setLocations(data);
  }, []);

  const fetchParams = useCallback(async () => {
    const res = await fetchApi(
      `/api/location-calculation-parameters?yearMonth=${yearMonth}`
    );
    const data = await res.json();
    setParams(data);
    return data as ParamItem[];
  }, [yearMonth]);

  const loadData = useCallback(async () => {
    setLoading(true);
    try {
      const [locsRes, paramsData] = await Promise.all([
        fetchApi("/api/locations").then((r) => r.json()),
        fetchParams(),
      ]);
      setLocations(locsRes);
      setParams(paramsData);
      const paramMap = new Map(
        paramsData.map((p) => [p.locationId, p])
      );
      const vals: Record<
        string,
        { fuelUnitPrice: string; roadUsageDiscountRate: string }
      > = {};
      for (const loc of locsRes) {
        const p = paramMap.get(loc.id);
        vals[loc.id] = p
          ? {
              fuelUnitPrice: String(p.fuelUnitPrice),
              roadUsageDiscountRate: String(p.roadUsageDiscountRate),
            }
          : { fuelUnitPrice: "0", roadUsageDiscountRate: "1" };
      }
      setLocalValues(vals);
    } finally {
      setLoading(false);
    }
  }, [fetchParams]);

  useEffect(() => {
    if (!loadingAuth && user && !canManageMaster(user.role)) {
      router.replace("/forbidden");
      return;
    }
  }, [user, loadingAuth, router]);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const handleValueChange = (
    locationId: string,
    field: "fuelUnitPrice" | "roadUsageDiscountRate",
    value: string
  ) => {
    setLocalValues((prev) => ({
      ...prev,
      [locationId]: {
        ...prev[locationId],
        [field]: value,
      },
    }));
  };

  const handleSave = async () => {
    if (!canEdit) return;
    setSaving(true);
    setSaveError(null);
    try {
      const toSave = locations.filter((loc) => {
        const v = localValues[loc.id];
        if (!v) return false;
        const fuel = parseFloat(v.fuelUnitPrice) || 0;
        const road = parseFloat(v.roadUsageDiscountRate);
        if (Number.isNaN(road)) return false;
        const roadVal = Math.min(1, Math.max(0, road));
        const existing = params.find((p) => p.locationId === loc.id);
        if (existing) {
          return (
            fuel !== existing.fuelUnitPrice ||
            roadVal !== existing.roadUsageDiscountRate
          );
        }
        return fuel > 0 || roadVal !== 1;
      });

      for (const loc of toSave) {
        const v = localValues[loc.id];
        if (!v) continue;
        const fuel = parseFloat(v.fuelUnitPrice) || 0;
        const road = parseFloat(v.roadUsageDiscountRate);
        const roadVal = Number.isNaN(road) ? 1 : Math.min(1, Math.max(0, road));
        const res = await fetchApi("/api/location-calculation-parameters", {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            locationId: loc.id,
            yearMonth,
            fuelUnitPrice: fuel,
            roadUsageDiscountRate: roadVal,
          }),
        });
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw new Error(err.error ?? "保存に失敗しました");
        }
      }
      await fetchParams();
    } catch (e) {
      setSaveError(e instanceof Error ? e.message : "保存に失敗しました");
    } finally {
      setSaving(false);
    }
  };

  const hasChanges = locations.some((loc) => {
    const v = localValues[loc.id];
    if (!v) return false;
    const fuel = parseFloat(v.fuelUnitPrice) || 0;
    const road = parseFloat(v.roadUsageDiscountRate);
    const roadVal = Number.isNaN(road) ? 1 : Math.min(1, Math.max(0, road));
    const existing = params.find((p) => p.locationId === loc.id);
    if (existing) {
      return (
        fuel !== existing.fuelUnitPrice ||
        roadVal !== existing.roadUsageDiscountRate
      );
    }
    return fuel > 0 || roadVal !== 1;
  });

  return (
    <div className="min-h-screen">
      <div className="mb-10">
        <div className="flex items-center gap-3 mb-2">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <Settings2 className="h-6 w-6" />
          </div>
          <h1 className="text-3xl font-semibold tracking-tight text-foreground">
            拠点別計算パラメータ
          </h1>
        </div>
        <p className="text-[15px] text-muted-foreground ml-[60px] leading-relaxed">
          燃料費・道路使用料の算出に使用する拠点×年月ごとのパラメータを設定します。
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
        <>
          <div className="flex items-center gap-4 mb-6">
            <div className="flex items-center gap-2">
              <span className="text-sm font-medium text-foreground">
                対象年月
              </span>
              <Select
                value={yearMonth}
                onValueChange={(v) => setYearMonth(v)}
                disabled={loading}
              >
                <SelectTrigger className="w-[140px]">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {getYearMonths().map((ym) => (
                    <SelectItem key={ym} value={ym}>
                      {ym}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-sm max-w-4xl">
            <table className="w-full">
              <thead>
                <tr className="border-b border-border bg-muted/30">
                  <th className="px-5 py-4 text-left text-sm font-semibold text-foreground">
                    拠点
                  </th>
                  <th className="px-5 py-4 text-left text-sm font-semibold text-foreground">
                    燃料単価（円/L）
                  </th>
                  <th className="px-5 py-4 text-left text-sm font-semibold text-foreground">
                    道路使用料割引率（0〜1）
                  </th>
                </tr>
              </thead>
              <tbody>
                {locations.map((loc) => (
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
                          type="number"
                          min={0}
                          step={0.01}
                          value={localValues[loc.id]?.fuelUnitPrice ?? ""}
                          onChange={(e) =>
                            handleValueChange(
                              loc.id,
                              "fuelUnitPrice",
                              e.target.value
                            )
                          }
                          className="w-32 font-mono"
                          placeholder="0"
                        />
                      ) : (
                        <span className="font-mono text-foreground">
                          {localValues[loc.id]?.fuelUnitPrice ?? "0"}
                        </span>
                      )}
                    </td>
                    <td className="px-5 py-3">
                      {canEdit ? (
                        <Input
                          type="number"
                          min={0}
                          max={1}
                          step={0.01}
                          value={
                            localValues[loc.id]?.roadUsageDiscountRate ?? 1
                          }
                          onChange={(e) =>
                            handleValueChange(
                              loc.id,
                              "roadUsageDiscountRate",
                              e.target.value
                            )
                          }
                          className="w-32 font-mono"
                          placeholder="1"
                        />
                      ) : (
                        <span className="font-mono text-foreground">
                          {localValues[loc.id]?.roadUsageDiscountRate ?? "1"}
                        </span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>

            {canEdit && (
              <div className="px-5 py-4 border-t border-border bg-muted/10 flex items-center gap-4">
                {saveError && (
                  <span className="text-sm text-destructive">{saveError}</span>
                )}
                <Button
                  onClick={handleSave}
                  disabled={saving || !hasChanges}
                  className="shrink-0"
                >
                  {saving ? (
                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                  ) : null}
                  保存
                </Button>
                {!hasChanges && (
                  <span className="text-sm text-muted-foreground">
                    変更がありません
                  </span>
                )}
              </div>
            )}
          </div>
        </>
      )}
    </div>
  );
}

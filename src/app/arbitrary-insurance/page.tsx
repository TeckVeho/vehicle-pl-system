"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { useAuthStore, canManageMaster } from "@/stores/authStore";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Shield, Loader2 } from "lucide-react";

interface ArbitraryInsuranceItem {
  id: string;
  tonnage: number;
  amount: number;
  sortOrder: number;
}

function formatCurrency(value: number): string {
  if (value === 0) return "0";
  return new Intl.NumberFormat("ja-JP").format(value);
}

export default function ArbitraryInsurancePage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const [items, setItems] = useState<ArbitraryInsuranceItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [localAmounts, setLocalAmounts] = useState<Record<string, string>>({});
  const [saveError, setSaveError] = useState<string | null>(null);

  const fetchItems = async () => {
    const res = await fetchApi("/api/arbitrary-insurance");
    const data = await res.json();
    setItems(data);
    const amounts: Record<string, string> = {};
    for (const i of data) {
      amounts[i.id] = String(i.amount);
    }
    setLocalAmounts(amounts);
  };

  useEffect(() => {
    if (!loadingAuth && user && !canManageMaster(user.role)) {
      router.replace("/forbidden");
      return;
    }
  }, [user, loadingAuth, router]);

  useEffect(() => {
    fetchItems().finally(() => setLoading(false));
  }, []);

  const handleAmountChange = (id: string, value: string) => {
    setLocalAmounts((prev) => ({ ...prev, [id]: value }));
  };

  const handleSave = async () => {
    if (!canEdit) return;
    setSaving(true);
    setSaveError(null);
    try {
      const updates = items.map((item) => ({
        id: item.id,
        amount: parseFloat(localAmounts[item.id] ?? "0") || 0,
      }));
      const res = await fetchApi("/api/arbitrary-insurance", {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ updates }),
      });
      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.error ?? "保存に失敗しました");
      }
      await fetchItems();
    } catch (e) {
      setSaveError(e instanceof Error ? e.message : "保存に失敗しました");
    } finally {
      setSaving(false);
    }
  };

  const hasChanges = items.some(
    (item) =>
      String(parseFloat(localAmounts[item.id] ?? "0") || 0) !== String(item.amount)
  );

  return (
    <div className="min-h-screen">
      <div className="mb-10">
        <div className="flex items-center gap-3 mb-2">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <Shield className="h-6 w-6" />
          </div>
          <h1 className="text-3xl font-semibold tracking-tight text-foreground">
            任意保険マスタ
          </h1>
        </div>
        <p className="text-[15px] text-muted-foreground ml-[60px] leading-relaxed">
          トン数別の月額保険料を設定します。損害保険料(任意保険)の計算に使用されます。
          <span className="text-foreground/80">編集のみ（新規追加・削除は不可）</span>
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
        <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-sm max-w-2xl">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                <th className="px-5 py-4 text-left text-sm font-semibold text-foreground">
                  トン数
                </th>
                <th className="px-5 py-4 text-left text-sm font-semibold text-foreground">
                  月額保険料（円）
                </th>
              </tr>
            </thead>
            <tbody>
              {items.map((item) => (
                <tr
                  key={item.id}
                  className="border-b border-border/60 hover:bg-muted/20 transition-colors"
                >
                  <td className="px-5 py-3 font-medium text-foreground">
                    {item.tonnage}t
                  </td>
                  <td className="px-5 py-3">
                    {canEdit ? (
                      <Input
                        type="number"
                        min={0}
                        step={1}
                        value={localAmounts[item.id] ?? ""}
                        onChange={(e) =>
                          handleAmountChange(item.id, e.target.value)
                        }
                        className="w-40 font-mono"
                      />
                    ) : (
                      <span className="font-mono text-foreground">
                        {formatCurrency(item.amount)}
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
      )}
    </div>
  );
}

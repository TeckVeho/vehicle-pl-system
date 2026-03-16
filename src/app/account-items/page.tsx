"use client";

import { useState, useEffect, useMemo } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { useAuthStore, canManageMaster } from "@/stores/authStore";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { Plus, Pencil, Trash2, BookOpen, ChevronDown, FileText } from "lucide-react";
import { cn } from "@/lib/utils";

interface AccountItem {
  id: string;
  code: string;
  name: string;
  category: string;
  sortOrder: number;
  isSubtotal: boolean;
  linkageMethod: string | null;
  effectiveFrom: string | null;
  effectiveTo: string | null;
}

function getYearMonths(): string[] {
  const months: string[] = [];
  const now = new Date();
  for (let i = -24; i <= 24; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
    months.push(
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`
    );
  }
  return months.reverse();
}

const CATEGORY_LABELS: Record<string, string> = {
  revenue: "売上",
  expense: "経費",
  subtotal_revenue: "売上小計",
  subtotal_expense: "経費小計",
  subtotal_gross: "粗利小計",
  summary: "合計",
};

const CATEGORY_ORDER = [
  "revenue",
  "subtotal_revenue",
  "expense",
  "subtotal_expense",
  "subtotal_gross",
  "summary",
];

export default function AccountItemsPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const [items, setItems] = useState<AccountItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [categoryFilter, setCategoryFilter] = useState<string>("all");
  const [filterOpen, setFilterOpen] = useState(false);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [form, setForm] = useState({
    code: "",
    name: "",
    category: "revenue",
    isSubtotal: false,
    linkageMethod: "" as string,
    effectiveFrom: "" as string,
    effectiveTo: "" as string,
  });
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<{ id: string; name: string } | null>(null);

  const fetchItems = async () => {
    const res = await fetchApi("/api/account-items");
    const data = await res.json();
    setItems(data);
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

  const groupedByCategory = useMemo(() => {
    const filtered =
      categoryFilter === "all"
        ? items
        : items.filter((i) => i.category === categoryFilter);
    const grouped = new Map<string, AccountItem[]>();
    for (const item of filtered) {
      const list = grouped.get(item.category) ?? [];
      list.push(item);
      grouped.set(item.category, list);
    }
    return CATEGORY_ORDER.filter((c) => grouped.has(c)).map((cat) => ({
      category: cat,
      label: CATEGORY_LABELS[cat] ?? cat,
      items: grouped.get(cat)!,
    }));
  }, [items, categoryFilter]);

  const selectedCategoryLabel =
    categoryFilter === "all"
      ? "すべての分類"
      : CATEGORY_LABELS[categoryFilter] ?? categoryFilter;

  const openCreate = () => {
    setEditingId(null);
    setForm({
      code: "",
      name: "",
      category: "revenue",
      isSubtotal: false,
      linkageMethod: "",
      effectiveFrom: "",
      effectiveTo: "",
    });
    setSubmitError(null);
    setDialogOpen(true);
  };

  const openEdit = (item: AccountItem) => {
    setEditingId(item.id);
    setForm({
      code: item.code,
      name: item.name,
      category: item.category,
      isSubtotal: item.isSubtotal,
      linkageMethod: item.linkageMethod ?? "",
      effectiveFrom: item.effectiveFrom ?? "",
      effectiveTo: item.effectiveTo ?? "",
    });
    setSubmitError(null);
    setDialogOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitError(null);
    try {
      if (editingId) {
        const res = await fetchApi(`/api/account-items/${editingId}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            ...form,
            linkageMethod: form.linkageMethod || null,
            effectiveFrom: form.effectiveFrom || null,
            effectiveTo: form.effectiveTo || null,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          setSubmitError(data.error ?? "更新に失敗しました");
          return;
        }
      } else {
        const res = await fetchApi("/api/account-items", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            ...form,
            linkageMethod: form.linkageMethod || null,
            effectiveFrom: form.effectiveFrom || null,
            effectiveTo: form.effectiveTo || null,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          setSubmitError(data.error ?? "登録に失敗しました");
          return;
        }
      }
      setDialogOpen(false);
      fetchItems();
    } catch {
      setSubmitError("通信エラーが発生しました");
    }
  };

  const handleDelete = async () => {
    if (!deleteConfirm) return;
    try {
      const res = await fetchApi(`/api/account-items/${deleteConfirm.id}`, {
        method: "DELETE",
      });
      const data = await res.json();
      if (!res.ok) {
        alert(data.error ?? "削除に失敗しました");
        return;
      }
      setDeleteConfirm(null);
      fetchItems();
    } catch {
      alert("通信エラーが発生しました");
    }
  };

  if (!loadingAuth && user && !canManageMaster(user.role)) {
    return null;
  }

  return (
    <div className="min-h-screen">
      {/* タイトルエリア - コース・車両マッピングに合わせた Notion 風 */}
      <div className="mb-10">
        <div className="flex items-center gap-3 mb-2">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <BookOpen className="h-6 w-6" />
          </div>
          <h1 className="text-3xl font-semibold tracking-tight text-foreground">
            勘定科目マスタ
          </h1>
          {canEdit && (
            <Button size="sm" onClick={openCreate} className="ml-2">
              <Plus className="h-4 w-4 mr-1.5" />
              新規追加
            </Button>
          )}
        </div>
        <p className="text-[15px] text-muted-foreground ml-[60px] leading-relaxed">
          損益計算書で使用する勘定科目を管理します。
          {canEdit ? (
            <span className="text-foreground/80">編集可能</span>
          ) : (
            <span className="text-foreground/80">閲覧のみ</span>
          )}
        </p>
      </div>

      {/* フィルター - Notion 風ドロップダウン */}
      <div className="mb-8 relative">
        <button
          type="button"
          onClick={() => setFilterOpen((o) => !o)}
          className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-muted/50 hover:bg-muted text-foreground border border-transparent hover:border-border transition-colors"
        >
          <FileText className="h-3.5 w-3.5 text-muted-foreground" />
          {selectedCategoryLabel}
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
                  setCategoryFilter("all");
                  setFilterOpen(false);
                }}
                className={cn(
                  "w-full px-3 py-2 text-left text-sm hover:bg-muted/60 transition-colors",
                  categoryFilter === "all" && "bg-muted/40 font-medium"
                )}
              >
                すべての分類
              </button>
              {CATEGORY_ORDER.map((cat) => (
                <button
                  key={cat}
                  type="button"
                  onClick={() => {
                    setCategoryFilter(cat);
                    setFilterOpen(false);
                  }}
                  className={cn(
                    "w-full px-3 py-2 text-left text-sm hover:bg-muted/60 transition-colors",
                    categoryFilter === cat && "bg-muted/40 font-medium"
                  )}
                >
                  {CATEGORY_LABELS[cat] ?? cat}
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
      ) : items.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border bg-muted/20 py-16 px-8 text-center">
          <BookOpen className="h-12 w-12 text-muted-foreground/50 mx-auto mb-4" />
          <p className="text-muted-foreground font-medium">データがありません</p>
          <p className="text-sm text-muted-foreground/80 mt-1">
            {canEdit ? "新規追加から勘定科目を登録してください" : "勘定科目が登録されていません"}
          </p>
        </div>
      ) : (
        <div className="space-y-6">
          {groupedByCategory.map(({ category, label, items: catItems }) => (
            <div
              key={category}
              className="rounded-2xl border border-border bg-card overflow-hidden shadow-sm hover:shadow-md transition-shadow"
            >
              {/* カテゴリヘッダー */}
              <div className="px-5 py-4 border-b border-border/60 bg-muted/30">
                <div className="flex items-center gap-2">
                  <FileText className="h-4 w-4 text-primary" />
                  <h2 className="font-semibold text-foreground">{label}</h2>
                  <span className="text-xs text-muted-foreground font-medium px-2 py-0.5 rounded-md bg-muted">
                    {catItems.length} 件
                  </span>
                </div>
              </div>
              {/* 勘定科目一覧 */}
              <div className="divide-y divide-border/40">
                {catItems.map((item) => (
                  <div
                    key={item.id}
                    className="px-5 py-3 flex items-center gap-4 hover:bg-muted/20 transition-colors"
                  >
                    <div className="flex-1 min-w-0">
                      <div className="font-medium text-foreground">{item.name}</div>
                      <div className="flex items-center gap-3 mt-0.5 text-xs text-muted-foreground">
                        <span className="font-mono">{item.code}</span>
                        {item.isSubtotal && (
                          <span className="px-1.5 py-0.5 rounded bg-muted/60">小計</span>
                        )}
                        {item.linkageMethod && (
                          <span className="truncate max-w-[200px]">
                            {item.linkageMethod}
                          </span>
                        )}
                        {(item.effectiveFrom || item.effectiveTo) && (
                          <span>
                            {item.effectiveFrom
                              ? item.effectiveFrom.replace("-", "年") + "月"
                              : "—"}
                            ～
                            {item.effectiveTo
                              ? item.effectiveTo.replace("-", "年") + "月"
                              : "—"}
                          </span>
                        )}
                      </div>
                    </div>
                    {canEdit && (
                      <div className="flex gap-1 shrink-0">
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-7 px-2"
                          onClick={() => openEdit(item)}
                        >
                          <Pencil className="h-3.5 w-3.5" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-7 px-2 text-destructive hover:text-destructive"
                          onClick={() =>
                            setDeleteConfirm({ id: item.id, name: item.name })
                          }
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </Button>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>
              {editingId ? "勘定科目を編集" : "勘定科目を追加"}
            </DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid gap-2">
              <Label htmlFor="code">コード</Label>
              <Input
                id="code"
                value={form.code}
                onChange={(e) => setForm((f) => ({ ...f, code: e.target.value }))}
                placeholder="例: 5010"
                required
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="name">勘定科目名</Label>
              <Input
                id="name"
                value={form.name}
                onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                placeholder="例: 山崎製パン"
                required
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="category">分類</Label>
              <Select
                value={form.category}
                onValueChange={(v) => setForm((f) => ({ ...f, category: v }))}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {Object.entries(CATEGORY_LABELS).map(([value, label]) => (
                    <SelectItem key={value} value={value}>
                      {label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="grid gap-2">
              <Label htmlFor="linkageMethod">連携方法</Label>
              <textarea
                id="linkageMethod"
                value={form.linkageMethod}
                onChange={(e) =>
                  setForm((f) => ({ ...f, linkageMethod: e.target.value }))
                }
                placeholder="例: CSVインポート、API連携、手動入力など"
                rows={3}
                className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              />
              <p className="text-xs text-muted-foreground">
                どのようにデータ連携するかを記録し、見える化します。
              </p>
            </div>
            <div className="flex items-center gap-2">
              <input
                type="checkbox"
                id="isSubtotal"
                checked={form.isSubtotal}
                onChange={(e) =>
                  setForm((f) => ({ ...f, isSubtotal: e.target.checked }))
                }
                className="rounded border-input"
              />
              <Label htmlFor="isSubtotal" className="font-normal">
                小計行として表示
              </Label>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="grid gap-2">
                <Label htmlFor="effectiveFrom">適用開始年月</Label>
                <Select
                  value={form.effectiveFrom || "none"}
                  onValueChange={(v) =>
                    setForm((f) => ({
                      ...f,
                      effectiveFrom: v === "none" ? "" : v,
                    }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue placeholder="指定なし" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="none">指定なし</SelectItem>
                    {getYearMonths().map((ym) => (
                      <SelectItem key={ym} value={ym}>
                        {ym.replace("-", "年")}月
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="grid gap-2">
                <Label htmlFor="effectiveTo">適用終了年月</Label>
                <Select
                  value={form.effectiveTo || "none"}
                  onValueChange={(v) =>
                    setForm((f) => ({
                      ...f,
                      effectiveTo: v === "none" ? "" : v,
                    }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue placeholder="指定なし" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="none">指定なし</SelectItem>
                    {getYearMonths().map((ym) => (
                      <SelectItem key={ym} value={ym}>
                        {ym.replace("-", "年")}月
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
            <p className="text-xs text-muted-foreground">
              指定なしの場合は全期間で有効です。適用開始・終了を設定すると、該当年月の損益計算書・インポートでのみ表示されます。
            </p>
            {submitError && (
              <p className="text-sm text-destructive">{submitError}</p>
            )}
            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setDialogOpen(false)}>
                キャンセル
              </Button>
              <Button type="submit">{editingId ? "更新" : "登録"}</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <Dialog open={!!deleteConfirm} onOpenChange={(o) => !o && setDeleteConfirm(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>削除の確認</DialogTitle>
          </DialogHeader>
          <p className="text-sm text-muted-foreground">
            「{deleteConfirm?.name}」を削除しますか？
          </p>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteConfirm(null)}>
              キャンセル
            </Button>
            <Button variant="destructive" onClick={handleDelete}>
              削除
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}

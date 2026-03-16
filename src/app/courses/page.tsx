"use client";

import { useState, useEffect } from "react";
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
import { Plus, Pencil, Trash2, MapPin } from "lucide-react";

interface Location {
  id: string;
  code: string;
  name: string;
}

interface Course {
  id: string;
  name: string;
  code: string;
  sortOrder: number;
  locationId: string;
  location: { id: string; code: string; name: string };
  _count: { vehicles: number };
}

export default function CoursesPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loadingAuth = useAuthStore((s) => s.loading);
  const canEdit = user ? canManageMaster(user.role) : false;
  const [courses, setCourses] = useState<Course[]>([]);
  const [locations, setLocations] = useState<Location[]>([]);
  const [locationId, setLocationId] = useState<string>("");
  const [loading, setLoading] = useState(true);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [form, setForm] = useState({ name: "", code: "" });
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<{ id: string; name: string } | null>(null);

  const fetchLocations = async () => {
    const res = await fetchApi("/api/locations");
    const data = await res.json();
    setLocations(data);
    if (data.length > 0 && !locationId) {
      setLocationId(data[0].id);
    }
  };

  const fetchCourses = async () => {
    if (!locationId) return;
    const res = await fetchApi(`/api/courses?locationId=${locationId}`);
    const data = await res.json();
    setCourses(data);
  };

  useEffect(() => {
    if (!loadingAuth && user && !canManageMaster(user.role)) {
      router.replace("/forbidden");
      return;
    }
  }, [user, loadingAuth, router]);

  useEffect(() => {
    fetchLocations();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (locationId) {
      setLoading(true);
      fetchCourses().finally(() => setLoading(false));
    } else {
      setCourses([]);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [locationId]);

  const openCreate = () => {
    setEditingId(null);
    setForm({ name: "", code: "" });
    setSubmitError(null);
    setDialogOpen(true);
  };

  const openEdit = (course: Course) => {
    setEditingId(course.id);
    setForm({ name: course.name, code: course.code });
    setSubmitError(null);
    setDialogOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitError(null);
    try {
      if (editingId) {
        const res = await fetchApi(`/api/courses/${editingId}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(form),
        });
        const data = await res.json();
        if (!res.ok) {
          setSubmitError(data.error ?? "更新に失敗しました");
          return;
        }
      } else {
        const res = await fetchApi("/api/courses", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ ...form, locationId }),
        });
        const data = await res.json();
        if (!res.ok) {
          setSubmitError(data.error ?? "登録に失敗しました");
          return;
        }
      }
      setDialogOpen(false);
      fetchCourses();
    } catch {
      setSubmitError("通信エラーが発生しました");
    }
  };

  const handleDelete = async () => {
    if (!deleteConfirm) return;
    try {
      const res = await fetchApi(`/api/courses/${deleteConfirm.id}`, {
        method: "DELETE",
      });
      const data = await res.json();
      if (!res.ok) {
        alert(data.error ?? "削除に失敗しました");
        return;
      }
      setDeleteConfirm(null);
      fetchCourses();
    } catch {
      alert("通信エラーが発生しました");
    }
  };

  if (!loadingAuth && user && !canManageMaster(user.role)) {
    return null;
  }

  return (
    <div>
      <h1 className="text-3xl font-bold tracking-tight mb-8 flex items-center gap-2">
        <MapPin className="h-8 w-8 text-muted-foreground" />
        コースマスタ
      </h1>

      <div className="mb-6 flex flex-wrap items-center gap-4">
        <div className="flex items-center gap-2">
          <Label className="text-sm text-muted-foreground whitespace-nowrap">拠点</Label>
          <Select value={locationId} onValueChange={setLocationId}>
            <SelectTrigger className="w-48">
              <SelectValue placeholder="拠点を選択" />
            </SelectTrigger>
            <SelectContent>
              {locations.map((loc) => (
                <SelectItem key={loc.id} value={loc.id}>
                  {loc.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        {canEdit && (
          <Button size="sm" onClick={openCreate} disabled={!locationId}>
            <Plus className="h-4 w-4 mr-1.5" />
            新規追加
          </Button>
        )}
      </div>

      {loading ? (
        <p className="text-sm text-muted-foreground">読み込み中...</p>
      ) : (
        <div className="border border-excel-grid overflow-hidden">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-muted/60 border-b border-excel-grid">
                <th className="text-left font-medium px-3 py-2 w-16">順序</th>
                <th className="text-left font-medium px-3 py-2 w-24">コード</th>
                <th className="text-left font-medium px-3 py-2">コース名</th>
                <th className="text-left font-medium px-3 py-2 w-20">車両数</th>
                <th className="text-right font-medium px-3 py-2 w-28">操作</th>
              </tr>
            </thead>
            <tbody>
              {courses.map((course) => (
                <tr
                  key={course.id}
                  className="border-b border-excel-grid last:border-b-0 hover:bg-muted/30"
                >
                  <td className="px-3 py-2">{course.sortOrder}</td>
                  <td className="px-3 py-2 font-mono">{course.code}</td>
                  <td className="px-3 py-2">{course.name}</td>
                  <td className="px-3 py-2 text-muted-foreground">{course._count.vehicles}</td>
                  <td className="px-3 py-2 text-right">
                    {canEdit ? (
                      <>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-7 px-2"
                          onClick={() => openEdit(course)}
                        >
                          <Pencil className="h-3.5 w-3.5" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-7 px-2 text-destructive hover:text-destructive"
                          onClick={() => setDeleteConfirm({ id: course.id, name: course.name })}
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </Button>
                      </>
                    ) : (
                      <span className="text-muted-foreground">—</span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>
              {editingId ? "コースを編集" : "コースを追加"}
            </DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid gap-2">
              <Label htmlFor="code">コード</Label>
              <Input
                id="code"
                value={form.code}
                onChange={(e) => setForm((f) => ({ ...f, code: e.target.value }))}
                placeholder="例: 001-001"
                required
                disabled={!!editingId}
                className={editingId ? "bg-muted" : ""}
              />
              {editingId && (
                <p className="text-xs text-muted-foreground">コードは編集できません</p>
              )}
            </div>
            <div className="grid gap-2">
              <Label htmlFor="name">コース名</Label>
              <Input
                id="name"
                value={form.name}
                onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                placeholder="例: 山崎製パンＡ便"
                required
              />
            </div>
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

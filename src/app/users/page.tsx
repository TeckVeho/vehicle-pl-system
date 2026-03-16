"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { useAuthStore, canManageUsers } from "@/stores/authStore";
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
import { Plus, Pencil, Trash2, Users } from "lucide-react";
import { VALID_ROLES } from "@/types/roles";

interface User {
  id: string;
  email: string;
  name: string;
  role: string;
  externalId: string | null;
  createdAt: string;
  updatedAt: string;
}

export default function UsersPage() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const loading = useAuthStore((s) => s.loading);
  const [users, setUsers] = useState<User[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [form, setForm] = useState({ email: "", name: "", role: "事務員", password: "" });
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<User | null>(null);

  useEffect(() => {
    if (!loading && user && !canManageUsers(user.role)) {
      router.replace("/forbidden");
      return;
    }
  }, [user, loading, router]);

  const fetchUsers = async () => {
    const res = await fetchApi("/api/users");
    const data = await res.json();
    if (res.status === 403) {
      router.replace("/forbidden");
      return;
    }
    setUsers(data);
  };

  useEffect(() => {
    if (user && canManageUsers(user.role)) {
      fetchUsers();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user]);

  const openCreate = () => {
    setEditingId(null);
    setForm({ email: "", name: "", role: "事務員", password: "" });
    setSubmitError(null);
    setDialogOpen(true);
  };

  const openEdit = (u: User) => {
    setEditingId(u.id);
    setForm({ email: u.email, name: u.name, role: u.role, password: "" });
    setSubmitError(null);
    setDialogOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitError(null);
    try {
      if (editingId) {
        const body: Record<string, unknown> = { name: form.name, role: form.role };
        if (form.password) body.password = form.password;
        const res = await fetchApi(`/api/users/${editingId}`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) {
          setSubmitError(data.error ?? "更新に失敗しました");
          return;
        }
      } else {
        const res = await fetchApi("/api/users", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            email: form.email,
            name: form.name,
            role: form.role,
            password: form.password || undefined,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          setSubmitError(data.error ?? "登録に失敗しました");
          return;
        }
      }
      setDialogOpen(false);
      fetchUsers();
    } catch {
      setSubmitError("通信エラーが発生しました");
    }
  };

  const handleDelete = async () => {
    if (!deleteConfirm) return;
    try {
      const res = await fetchApi(`/api/users/${deleteConfirm.id}`, {
        method: "DELETE",
      });
      if (!res.ok) {
        const data = await res.json();
        setSubmitError(data.error ?? "削除に失敗しました");
        return;
      }
      setDeleteConfirm(null);
      fetchUsers();
    } catch {
      setSubmitError("通信エラーが発生しました");
    }
  };

  if (loading || !user) {
    return (
      <div className="flex items-center justify-center min-h-[40vh]">
        <span className="text-muted-foreground">読み込み中...</span>
      </div>
    );
  }

  if (!canManageUsers(user.role)) {
    return null;
  }

  return (
    <div className="pb-12">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-3xl font-bold tracking-tight flex items-center gap-2">
          <Users className="h-8 w-8" />
          ユーザー管理
        </h1>
        <Button onClick={openCreate}>
          <Plus className="h-4 w-4 mr-2" />
          新規登録
        </Button>
      </div>

      <div className="rounded-lg border bg-card">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b bg-muted/50">
              <th className="text-left p-3 font-medium">メール</th>
              <th className="text-left p-3 font-medium">氏名</th>
              <th className="text-left p-3 font-medium">権限</th>
              <th className="text-left p-3 font-medium">外部ID</th>
              <th className="w-24 p-3" />
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id} className="border-b last:border-0 hover:bg-muted/30">
                <td className="p-3">{u.email}</td>
                <td className="p-3">{u.name}</td>
                <td className="p-3">{u.role}</td>
                <td className="p-3 text-muted-foreground">{u.externalId ?? "-"}</td>
                <td className="p-3">
                  <div className="flex gap-1">
                    <Button variant="ghost" size="sm" onClick={() => openEdit(u)}>
                      <Pencil className="h-3.5 w-3.5" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => setDeleteConfirm(u)}
                      className="text-destructive hover:text-destructive"
                    >
                      <Trash2 className="h-3.5 w-3.5" />
                    </Button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editingId ? "ユーザー編集" : "ユーザー登録"}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <Label>メールアドレス</Label>
              <Input
                value={form.email}
                onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
                placeholder="user@example.com"
                type="email"
                required
                disabled={!!editingId}
              />
            </div>
            <div>
              <Label>氏名</Label>
              <Input
                value={form.name}
                onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                placeholder="山田 太郎"
                required
              />
            </div>
            <div>
              <Label>権限</Label>
              <Select
                value={form.role}
                onValueChange={(v) => setForm((f) => ({ ...f, role: v }))}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {VALID_ROLES.map((r) => (
                    <SelectItem key={r} value={r}>
                      {r}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label>パスワード {editingId && "(変更時のみ)"}</Label>
              <Input
                value={form.password}
                onChange={(e) => setForm((f) => ({ ...f, password: e.target.value }))}
                placeholder={editingId ? "変更しない場合は空欄" : "パスワード"}
                type="password"
                required={!editingId}
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

      <Dialog open={!!deleteConfirm} onOpenChange={() => setDeleteConfirm(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>削除の確認</DialogTitle>
          </DialogHeader>
          <p>
            {deleteConfirm?.name} ({deleteConfirm?.email}) を削除しますか？
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

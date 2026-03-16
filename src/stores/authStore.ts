import { create } from "zustand";

export interface AuthUser {
  id: string;
  email: string;
  name: string;
  role: string;
}

// 権限グループ（バックエンドと一致）
export const ROLES = {
  EDIT_PL: [
    "現場MG", "本社MG", "経理財務", "部長", "執行役員", "取締役", "DX", "DX管理者",
  ],
  MASTER: ["DX", "DX管理者"],
  USER_ADMIN: ["DX管理者"],
} as const;

export function canEditPL(role: string): boolean {
  return (ROLES.EDIT_PL as readonly string[]).includes(role);
}

export function canManageMaster(role: string): boolean {
  return (ROLES.MASTER as readonly string[]).includes(role);
}

export function canManageUsers(role: string): boolean {
  return (ROLES.USER_ADMIN as readonly string[]).includes(role);
}

interface AuthState {
  user: AuthUser | null;
  loading: boolean;
  setUser: (user: AuthUser | null) => void;
  setLoading: (loading: boolean) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  loading: true,
  setUser: (user) => set({ user }),
  setLoading: (loading) => set({ loading }),
}));

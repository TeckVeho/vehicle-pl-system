"use client";

import { useEffect } from "react";
import { usePathname } from "next/navigation";
import { fetchApi } from "@/lib/api";
import { useAuthStore } from "@/stores/authStore";

const LOGIN_PATH = "/login";

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const { setUser, setLoading } = useAuthStore();

  useEffect(() => {
    if (pathname === LOGIN_PATH) {
      setUser(null);
      setLoading(false);
      return;
    }

    let cancelled = false;
    setLoading(true);

    fetchApi("/api/auth/me")
      .then((res) => {
        if (cancelled) return;
        if (res.ok) {
          return res.json().then((data) => {
            setUser({
              id: data.id,
              email: data.email,
              name: data.name,
              role: data.role,
            });
          });
        }
        setUser(null);
        // 401: stale web session flag without a valid API token — clear to avoid /login ↔ /dashboard loop.
        if (res.status === 401) {
          fetch("/api/auth/session", { method: "DELETE", credentials: "include" })
            .catch(() => undefined)
            .finally(() => {
              window.location.href = `/login?from=${encodeURIComponent(pathname)}`;
            });
        }
      })
      .catch(() => {
        if (!cancelled) setUser(null);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [pathname, setUser, setLoading]);

  return <>{children}</>;
}

"use client";

import { usePathname } from "next/navigation";
import { Header } from "./Header";

const LOGIN_PATH = "/login";

export function AppShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const isLoginPage = pathname === LOGIN_PATH;

  return (
    <div className="flex min-h-screen flex-col">
      {!isLoginPage && <Header />}
      <main
        className={
          isLoginPage
            ? "flex-1"
            : "flex-1 mx-auto w-full max-w-6xl px-4 py-6 bg-background"
        }
      >
        {children}
      </main>
    </div>
  );
}

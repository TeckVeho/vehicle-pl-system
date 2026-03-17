"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import {
  LayoutDashboard,
  FileSpreadsheet,
  Download,
  Calendar,
  LogOut,
  BookOpen,
  MapPin,
  Link2,
  ChevronDown,
  Shield,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { fetchApi } from "@/lib/api";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useAuthStore, canEditPL, canManageMaster } from "@/stores/authStore";

const topNavItems = [
  { href: "/dashboard", label: "ダッシュボード", icon: LayoutDashboard, roles: null },
  { href: "/daily-summary", label: "日次連携データ", icon: Calendar, roles: null, hidden: true },
];

const plSubMenu = [
  { href: "/income-statement?mode=pl", label: "PL版（全科目）", icon: FileSpreadsheet, roles: null },
  { href: "/income-statement?mode=vpl", label: "VPL版（車両関連のみ）", icon: FileSpreadsheet, roles: null },
];

const dataSubMenu = [
  { href: "/import", label: "データインポート", icon: Download, roles: "EDIT_PL" as const },
  { href: "/sync-logs", label: "連携記録", icon: Link2, roles: null },
];

const masterSubMenu = [
  { href: "/account-items", label: "勘定科目マスタ", icon: BookOpen, roles: "MASTER" as const },
  { href: "/arbitrary-insurance", label: "任意保険マスタ", icon: Shield, roles: "MASTER" as const },
  { href: "/course-vehicle-mapping", label: "コース・車両マッピング", icon: MapPin, roles: null },
];

export function Header() {
  const pathname = usePathname();
  const router = useRouter();
  const user = useAuthStore((s) => s.user);

  const handleLogout = async () => {
    await fetchApi("/api/auth/logout", { method: "POST" });
    router.push("/login");
    router.refresh();
  };

  const isItemVisible = (item: { roles?: string | null }) => {
    if (!item.roles) return true;
    if (!user) return false;
    if (item.roles === "EDIT_PL") return canEditPL(user.role);
    if (item.roles === "MASTER") return canManageMaster(user.role);
    return true;
  };

  const visibleTopNavItems = topNavItems.filter((item) => {
    if ((item as { hidden?: boolean }).hidden) return false;
    return isItemVisible(item);
  });

  const visibleDataSubMenu = dataSubMenu.filter(isItemVisible);
  const visibleMasterSubMenu = masterSubMenu.filter(isItemVisible);

  const isDataActive = pathname.startsWith("/import") || pathname.startsWith("/sync-logs");
  const isMasterActive = pathname.startsWith("/account-items") || pathname.startsWith("/arbitrary-insurance") || pathname.startsWith("/course-vehicle-mapping");
  const isPLActive = pathname.startsWith("/income-statement");

  const navLinkClass = (isActive: boolean) =>
    cn(
      "flex items-center gap-2 rounded-none px-3 py-2 text-sm font-medium transition-colors whitespace-nowrap",
      isActive
        ? "bg-white/20 text-excel-header-foreground"
        : "text-excel-header-foreground/90 hover:bg-white/15 hover:text-excel-header-foreground"
    );

  return (
    <header className="sticky top-0 z-50 w-full border-b border-white/20 bg-excel-header">
      <div className="flex h-11 items-center justify-between gap-6 px-4">
        <div className="flex items-center gap-6">
          <Link href="/" className="flex items-center shrink-0">
            <span className="text-sm font-semibold tracking-tight text-excel-header-foreground">
              IZUMI
            </span>
          </Link>
          <nav className="flex items-center gap-0.5">
            {visibleTopNavItems.map((item) => {
              const Icon = item.icon;
              const isActive = pathname.startsWith(item.href);
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={navLinkClass(isActive)}
                >
                  <Icon className="h-4 w-4 shrink-0" />
                  {item.label}
                </Link>
              );
            })}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <button className={navLinkClass(isPLActive)}>
                  <FileSpreadsheet className="h-4 w-4 shrink-0" />
                  車両損益計算書
                  <ChevronDown className="h-3.5 w-3.5 shrink-0 opacity-70" />
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="start" className="min-w-[200px]">
                {plSubMenu.map((item) => {
                  const Icon = item.icon;
                  return (
                    <DropdownMenuItem key={item.href} asChild>
                      <Link href={item.href} className="flex items-center gap-2 cursor-pointer">
                        <Icon className="h-4 w-4 shrink-0" />
                        {item.label}
                      </Link>
                    </DropdownMenuItem>
                  );
                })}
              </DropdownMenuContent>
            </DropdownMenu>
            {visibleDataSubMenu.length > 0 && (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <button
                    className={navLinkClass(isDataActive)}
                  >
                    <Download className="h-4 w-4 shrink-0" />
                    データ
                    <ChevronDown className="h-3.5 w-3.5 shrink-0 opacity-70" />
                  </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" className="min-w-[180px]">
                  {visibleDataSubMenu.map((item) => {
                    const Icon = item.icon;
                    return (
                      <DropdownMenuItem key={item.href} asChild>
                        <Link href={item.href} className="flex items-center gap-2 cursor-pointer">
                          <Icon className="h-4 w-4 shrink-0" />
                          {item.label}
                        </Link>
                      </DropdownMenuItem>
                    );
                  })}
                </DropdownMenuContent>
              </DropdownMenu>
            )}
            {visibleMasterSubMenu.length > 0 && (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <button
                    className={navLinkClass(isMasterActive)}
                  >
                    <BookOpen className="h-4 w-4 shrink-0" />
                    マスタ
                    <ChevronDown className="h-3.5 w-3.5 shrink-0 opacity-70" />
                  </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" className="min-w-[180px]">
                  {visibleMasterSubMenu.map((item) => {
                    const Icon = item.icon;
                    return (
                      <DropdownMenuItem key={item.href} asChild>
                        <Link href={item.href} className="flex items-center gap-2 cursor-pointer">
                          <Icon className="h-4 w-4 shrink-0" />
                          {item.label}
                        </Link>
                      </DropdownMenuItem>
                    );
                  })}
                </DropdownMenuContent>
              </DropdownMenu>
            )}
          </nav>
        </div>
        <Button
          variant="ghost"
          size="sm"
          onClick={handleLogout}
          className="text-excel-header-foreground/90 hover:bg-white/15 hover:text-excel-header-foreground"
        >
          <LogOut className="h-4 w-4 mr-1" />
          ログアウト
        </Button>
      </div>
    </header>
  );
}

"use client";

import Link from "next/link";
import { ShieldX } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function ForbiddenPage() {
  return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] gap-6">
      <ShieldX className="h-16 w-16 text-muted-foreground" />
      <div className="text-center space-y-2">
        <h1 className="text-2xl font-bold">アクセス権限がありません</h1>
        <p className="text-muted-foreground">
          このページを表示する権限がありません。
        </p>
      </div>
      <Button asChild variant="outline">
        <Link href="/dashboard">ダッシュボードに戻る</Link>
      </Button>
    </div>
  );
}

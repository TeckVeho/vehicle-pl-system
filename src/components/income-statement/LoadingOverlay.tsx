"use client";

interface LoadingOverlayProps {
  message?: string;
}

export function LoadingOverlay({ message = "読み込み中" }: LoadingOverlayProps) {
  return (
    <div
      className="fixed inset-0 z-[60] flex items-center justify-center bg-background/85 backdrop-blur-md animate-in fade-in duration-300"
      aria-live="polite"
      aria-busy="true"
    >
      <div className="flex flex-col items-center gap-8">
        {/* 経理担当者が帳簿を確認しているシーン */}
        <div className="relative flex flex-col items-center">
          {/* 帳簿・伝票の見開き */}
          <div className="relative flex w-40 overflow-hidden rounded-lg border border-border bg-card shadow-md">
            {/* 左ページ */}
            <div className="flex w-1/2 flex-col border-r border-border p-3">
              {/* 行がチェックされていく */}
              {[0, 1, 2, 3].map((i) => (
                <div
                  key={i}
                  className="flex items-center gap-2 border-b border-border/50 py-1.5 last:border-0"
                >
                  <span
                    className="flex h-4 w-4 shrink-0 items-center justify-center text-primary animate-check-draw"
                    style={{ animationDelay: `${i * 350}ms` }}
                    aria-hidden
                  >
                    ✓
                  </span>
                  <div className="h-2 flex-1 rounded bg-muted/60" />
                </div>
              ))}
            </div>
            {/* 右ページ（次のページを確認中） */}
            <div className="relative w-1/2 overflow-hidden bg-muted/30">
              <div className="flex flex-col p-3">
                {[0, 1, 2].map((i) => (
                  <div key={i} className="flex gap-2 border-b border-border/50 py-1.5 last:border-0">
                    <div className="h-2 flex-1 rounded bg-muted/60" />
                  </div>
                ))}
              </div>
              {/* 視線が下に移動（確認している感じ） */}
              <div
                className="absolute left-2 right-2 h-4 rounded bg-primary/20 animate-scan-down"
              />
            </div>
          </div>
          {/* ペンが横に動く（人が書いている） */}
          <div className="relative mt-2 h-1 w-32 overflow-hidden rounded-full bg-muted/50">
            <div
              className="absolute top-1/2 h-2 w-2 -translate-y-1/2 rounded-full bg-foreground"
              style={{
                animation: "pen-move 1.5s ease-in-out infinite",
              }}
            />
          </div>
        </div>
        <div className="flex flex-col items-center gap-3">
          <p className="text-sm font-medium text-foreground/90 tracking-wider">
            {message}
          </p>
          <div className="flex gap-1.5">
            <span
              className="h-2 w-2 rounded-full bg-foreground/50 animate-loading-dot"
              style={{ animationDelay: "0ms" }}
            />
            <span
              className="h-2 w-2 rounded-full bg-foreground/50 animate-loading-dot"
              style={{ animationDelay: "150ms" }}
            />
            <span
              className="h-2 w-2 rounded-full bg-foreground/50 animate-loading-dot"
              style={{ animationDelay: "300ms" }}
            />
          </div>
        </div>
      </div>
    </div>
  );
}

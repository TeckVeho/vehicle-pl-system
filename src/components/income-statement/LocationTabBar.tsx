"use client";

import { useRef, useState, useEffect } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { cn } from "@/lib/utils";

interface Location {
  id: string;
  code: string;
  name: string;
}

interface LocationTabBarProps {
  locationId: string;
  locations: Location[];
  onLocationChange: (value: string) => void;
}

export function LocationTabBar({
  locationId,
  locations,
  onLocationChange,
}: LocationTabBarProps) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [canScrollLeft, setCanScrollLeft] = useState(false);
  const [canScrollRight, setCanScrollRight] = useState(false);

  const updateScrollState = () => {
    const el = scrollRef.current;
    if (!el) return;
    setCanScrollLeft(el.scrollLeft > 1);
    setCanScrollRight(
      el.scrollLeft + el.clientWidth < el.scrollWidth - 1
    );
  };

  useEffect(() => {
    const el = scrollRef.current;
    if (!el) return;
    updateScrollState();
    el.addEventListener("scroll", updateScrollState);
    const ro = new ResizeObserver(updateScrollState);
    ro.observe(el);
    return () => {
      el.removeEventListener("scroll", updateScrollState);
      ro.disconnect();
    };
  }, [locations]);

  const scrollLeft = () => {
    scrollRef.current?.scrollBy({ left: -200, behavior: "smooth" });
  };
  const scrollRight = () => {
    scrollRef.current?.scrollBy({ left: 200, behavior: "smooth" });
  };

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 border-t border-excel-grid bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 pb-[env(safe-area-inset-bottom)]">
      <div className="relative">
        <div
          ref={scrollRef}
          className="flex h-10 min-h-[44px] items-center gap-0 overflow-x-auto overscroll-x-contain px-4 touch-pan-x [-webkit-overflow-scrolling:touch]"
        >
        {locations.map((loc) => (
          <button
            key={loc.id}
            type="button"
            onClick={() => onLocationChange(loc.id)}
            className={cn(
              "flex h-full shrink-0 items-center border-t-2 px-4 text-sm font-medium whitespace-nowrap transition-colors touch-manipulation cursor-pointer",
              locationId === loc.id
                ? "border-primary bg-muted text-foreground"
                : "border-transparent text-muted-foreground hover:bg-muted/60 hover:text-foreground"
            )}
          >
            {loc.name}
          </button>
        ))}
        </div>
        {canScrollLeft && (
          <div className="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-start pointer-events-none">
            <div className="absolute inset-0 bg-gradient-to-r from-background/60 to-transparent" />
            <button
              type="button"
              onClick={scrollLeft}
              className="relative z-10 pointer-events-auto flex h-8 w-8 items-center justify-center rounded-full bg-background/80 shadow-md backdrop-blur hover:bg-background/90 transition-colors"
              aria-label="左にスクロール"
            >
              <ChevronLeft className="h-4 w-4 text-foreground" />
            </button>
          </div>
        )}
        {canScrollRight && (
          <div className="absolute right-0 top-0 bottom-0 w-12 flex items-center justify-end pointer-events-none">
            <div className="absolute inset-0 bg-gradient-to-l from-background/60 to-transparent" />
            <button
              type="button"
              onClick={scrollRight}
              className="relative z-10 pointer-events-auto flex h-8 w-8 items-center justify-center rounded-full bg-background/80 shadow-md backdrop-blur hover:bg-background/90 transition-colors"
              aria-label="右にスクロール"
            >
              <ChevronRight className="h-4 w-4 text-foreground" />
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

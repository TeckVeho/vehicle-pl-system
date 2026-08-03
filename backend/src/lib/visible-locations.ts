export const DEFAULT_VISIBLE_LOCATION_CODES = ["LOC002", "LOC017"] as const;

export function getVisibleLocationCodes(): string[] {
  const raw = process.env.VISIBLE_LOCATION_CODES?.trim();
  if (!raw) return [...DEFAULT_VISIBLE_LOCATION_CODES];
  return raw.split(",").map((c) => c.trim()).filter(Boolean);
}

export function filterVisibleLocations<T extends { code: string }>(locations: T[]): T[] {
  const allowed = new Set(getVisibleLocationCodes());
  return locations.filter((l) => allowed.has(l.code));
}

export function visibleLocationPrismaWhere() {
  return { code: { in: getVisibleLocationCodes() } };
}

/**
 * Shared SQL utilities for raw bulk operations.
 *
 * Used by location-expense sync and allocation to perform
 * bulk INSERT ... ON DUPLICATE KEY UPDATE instead of N individual upserts.
 */

/** Escape a string value for raw SQL (single-quote escaping for MySQL) */
export function esc(val: string): string {
  return `'${val.replace(/'/g, "''")}'`;
}

/**
 * Generate a CUID-like unique ID for raw SQL INSERT VALUES.
 * Matches the format produced by Prisma's @default(cuid()).
 */
export function generateCuid(): string {
  const timestamp = Date.now().toString(36);
  const randomPart = Array.from({ length: 16 }, () =>
    Math.floor(Math.random() * 36).toString(36)
  ).join("");
  return `c${timestamp}${randomPart}`;
}

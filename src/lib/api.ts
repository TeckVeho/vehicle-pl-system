/**
 * API ベース URL（空の場合は Next.js の rewrites でプロキシされる相対パスを使用）
 */
export function getApiUrl(): string {
  // Browser: same-origin requests hit Next.js rewrites → backend API (keeps cookies on web host).
  if (typeof window !== "undefined") {
    return "";
  }
  const url = process.env.NEXT_PUBLIC_API_URL ?? "";
  return url.replace(/\/$/, "");
}

/**
 * API エンドポイントへの fetch（credentials 付き）
 */
export async function fetchApi(
  path: string,
  options: RequestInit = {}
): Promise<Response> {
  const base = getApiUrl();
  const url = path.startsWith("/") ? `${base}${path}` : `${base}/api/${path}`;
  return fetch(url, {
    ...options,
    credentials: "include",
    headers: {
      ...(options.headers ?? {}),
    },
  });
}

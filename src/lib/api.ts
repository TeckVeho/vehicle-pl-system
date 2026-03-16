/**
 * API ベース URL（空の場合は Next.js の rewrites でプロキシされる相対パスを使用）
 */
export function getApiUrl(): string {
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

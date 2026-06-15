/**
 * Google Drive フォルダ URL / raw ID から folder ID を抽出する。
 * マッチしない場合は trim した入力をそのまま返す（API 側で 404/403 等に委ねる）。
 */
export function extractFolderId(input: string): string {
  const trimmed = input.trim();
  if (!trimmed) return trimmed;

  const foldersMatch = trimmed.match(/\/folders\/([a-zA-Z0-9_-]+)/);
  if (foldersMatch) return foldersMatch[1];

  const openIdMatch = trimmed.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (openIdMatch) return openIdMatch[1];

  return trimmed;
}

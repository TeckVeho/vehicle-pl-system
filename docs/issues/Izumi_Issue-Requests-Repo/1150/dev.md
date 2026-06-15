# Dev Log — Issue #1150: [FE] 拠点SpreadsheetID設定UI

**Parent**: #953  
**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1150  
**Date**: 2026-05-07

---

## 実装概要

拠点ごとに Google Sheets ID を管理画面から設定できる UI を追加。  
BE の PATCH エンドポイントが未実装だったため、BE 側も合わせて実装。

---

## 変更ファイル

### Backend

#### 1. `backend/prisma/schema.prisma`
- `Location` モデルに `spreadsheetId String?` フィールドを追加

#### 2. `backend/prisma/dev.db`（SQLite ローカル DB）
- 直接 `ALTER TABLE Location ADD COLUMN spreadsheetId TEXT NULL` を実行
- ローカル dev 環境は SQLite（`file:./dev.db`）のため、`prisma migrate dev` の代わりに Python sqlite3 モジュールで直接変更
- `npx prisma generate` で Prisma クライアントを再生成済み

#### 3. `backend/src/routes/locations.ts`
- `PATCH /:id` エンドポイントを追加
- `requireRole(ROLES.MASTER)` で権限制御（DX / DX管理者のみ）
- リクエストボディ: `{ spreadsheetId: string | null }`
- 拠点が存在しない場合は 404 を返す
- `spreadsheetId: null` を送ると設定をクリア可能

### Frontend

#### 4. `src/app/locations/page.tsx`（新規作成）
- 拠点一覧を表示し、各行で `spreadsheetId` を入力・保存できる管理画面
- **主な機能:**
  - Google Sheets URL または Sheets ID をそのまま入力可（URL から ID を自動抽出: `/spreadsheets/d/{id}/` の正規表現）
  - 行ごとの「保存」ボタン（変更がある場合のみ有効）
  - 「クリア」ボタンで `spreadsheetId` を null にリセット（設定済み拠点のみ表示）
  - 設定済み / 未設定バッジ（`CheckCircle2` / `CircleDashed` アイコン）
  - 保存成功時 3 秒間「保存済」フィードバック → 自動リセット
  - エラー時インライン表示
  - 権限チェック: `canManageMaster` (DX / DX管理者) 以外は読み取り専用表示、未認可は `/forbidden` にリダイレクト

#### 5. `src/components/layout/Header.tsx`
- `masterSubMenu` に「拠点スプレッドシート設定」(`/locations`) を追加（MASTER 権限）
- `Database` アイコン（lucide-react）を import
- `isMasterActive` の判定に `/locations` パスを追加

---

## 設計上の決定事項

### URL → ID 自動抽出
```typescript
function extractSheetId(input: string): string {
  const match = input.trim().match(/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/);
  if (match) return match[1];
  return input.trim();
}
```
- フルURLを貼り付けても ID のみ抽出して保存
- URL でも ID でも入力可にすることで運用しやすさを向上

### 行ごとの保存（一括保存なし）
- 拠点数が少なく、個別設定の方が誤操作リスクが低いため行ごと保存を採用
- `isDirty` チェックで変更がない場合は「保存」ボタンを無効化

### ローカル DB の対処
- `schema.prisma` の provider は `mysql`（本番）
- ローカル開発は SQLite（`backend/prisma/dev.db`）
- `prisma migrate dev` は MySQL URL が必要なため失敗 → Python `sqlite3` で直接 ALTER TABLE 実行
- **本番 MySQL への適用**: `ALTER TABLE Location ADD COLUMN spreadsheetId VARCHAR(191) NULL;` を実行すること（または `npx prisma migrate dev` を MySQL 環境で実行）

---

## 検証方法

### Backend
```bash
# 1. サーバー起動（認証のために必要）
cd backend && npm run dev

# 2. ログイン（DX/DX管理者アカウント使用）
curl -X POST http://localhost:4000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"...","password":"..."}' \
  -c cookie.txt

# 3. spreadsheetId 設定
curl -X PATCH http://localhost:4000/api/locations/{locationId} \
  -H "Content-Type: application/json" \
  -b cookie.txt \
  -d '{"spreadsheetId":"1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms"}'

# 4. 設定確認
curl http://localhost:4000/api/locations -b cookie.txt | python -c "import json,sys; [print(l['name'], l.get('spreadsheetId')) for l in json.load(sys.stdin)]"
```

### Frontend
1. `npm run dev` でフロントエンド起動
2. DX / DX管理者でログイン
3. ヘッダー「マスタ」メニュー → 「拠点スプレッドシート設定」をクリック
4. 各拠点の入力欄に Sheets ID または URL を入力
5. 「保存」ボタンをクリック → バッジが「設定済」に変わることを確認
6. 「クリア」で設定を解除できることを確認

---

## 受け入れ基準チェック

- [x] 拠点管理画面で spreadsheetId を入力・保存できる
- [x] 設定済み拠点には視覚的に区別できるインジケーターが表示される（緑の「設定済」バッジ / 「未設定」バッジ）
- [x] 保存成功 / 失敗のフィードバックが表示される（「保存済」フラッシュ / エラーメッセージ）
- [x] 既存 UI に破壊的変更なし（新規ページ追加のみ、既存ページ変更なし）

---

## 未コミット変更一覧

```
backend/prisma/schema.prisma         (modified)
backend/prisma/dev.db                (binary - SQLite column added)
backend/src/routes/locations.ts      (modified)
src/app/locations/page.tsx           (new file)
src/components/layout/Header.tsx     (modified)
```

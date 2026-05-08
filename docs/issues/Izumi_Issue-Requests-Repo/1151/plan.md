# Issue #1151: [BE-A] P0: 売上ファイル連携 - Google Drive API 接続基盤・認証クライアント - Implementation Plan

## 概要 (Overview)

親 Issue [#953](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/953) における売上ファイル連携の **第一段階**として、本番データソースである **Google Drive 上のネイティブ `.xlsx`** を読むための **Google Drive API** 接続基盤を **`googleapis`** で導入する。

**接続基盤の主軸（本 plan の正）**

- サービスアカウント認証（**`GOOGLE_SERVICE_ACCOUNT_JSON`**）
- **読み取り専用**スコープ **`https://www.googleapis.com/auth/drive.readonly`** のみ
- **`getDriveClient()`**（命名例）で **`google.drive({ version: "v3", auth })`** を **シングルトン再利用**
- 実際の **ファイルダウンロード**（例: `files.get` + `alt=media`）、**Excel パース**、**拠点 → Drive file ID** の解決は **`spreadsheet-revenue.ts`（#953 / BE-B）** で行う。**本 issue ではビジネスロジック・パースを変更しない**（スタブのままでよい）。

**データ取得モデル:** **プル型** — バックエンドが必要なタイミングで Drive から **最新バイト** を取得する。セル単位のストリーミングはない。

**（任意・補助）Google Sheets API:** Google **Sheets ネイティブ**文書だけを読む経路が必要な場合、`spreadsheets.readonly` の **`getSheetsClient()`**（`google-sheets-client.ts`）を **補助モジュール**として置ける。**本番 `.xlsx` の主経路には使わない**（MIME が `.xlsx` の Drive ファイルは Sheets API の Spreadsheet ID ではない）。

**スコープ外:** GCP での SA 発行、Drive フォルダ／ファイルの共有（運用作業）。

**Acceptance alignment（Drive 基盤）:** `googleapis` 追加・**Drive readonly クライアント**・`.env.example`（Drive 共有・`drive.readonly` を明記）・未設定時 throw・**`npm test` 全件パス**。Sheets クライアントはチーム方針で追加／維持する場合のみ。

**実装状況メモ:** リポジトリにより **`google-sheets-client.ts` のみ先行**されている場合がある。その場合でも **本 plan で優先する未実装分は `google-drive-client.ts`（およびそのテスト）** と捉える。

---

## FE (Frontend)

### 該当なし

本 issue はバックエンドの接続／認証基盤のみ。UI・Next.js アプリ側の変更は不要。

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `backend/package.json`

##### 1.1.1. Add `googleapis` dependency

`backend` で **Google Drive API**（および任意で Sheets API）を公式クライアント経由で呼ぶため、`dependencies` に `googleapis` を追加する。

**変更内容:**

- `cd backend && npm install googleapis` で `dependencies` に `googleapis` が追記されることを確認する。
- `type: module` が既にあるため、`googleapis` の ESM 利用と整合する。

---

#### 1.2. File: `backend/src/lib/google-drive-client.ts` (新規・主成果)

##### 1.2.1. Singleton `getDriveClient()` with service account JSON

環境変数 **`GOOGLE_SERVICE_ACCOUNT_JSON`** からサービスアカウント JSON を読み、**読み取り専用**で **`google.drive({ version: "v3", auth })`** を構築し、モジュールスコープで 1 度だけキャッシュして返す。

**変更内容:**

- `import { google } from "googleapis"`。
- モジュールレベルで Drive クライアント参照を保持し、`getDriveClient()` で再利用。
- `process.env.GOOGLE_SERVICE_ACCOUNT_JSON` が未設定／空白のみのときは **throw `Error`**（メッセージはプレフィックス付き、例: `[google-drive] GOOGLE_SERVICE_ACCOUNT_JSON is not set`）。
- `JSON.parse(credentialsJson)` でパース。不正 JSON はそのまま伝播でよい。
- **`scopes: ["https://www.googleapis.com/auth/drive.readonly"]` のみ**。書き込みスコープは付けない。
- `new google.auth.GoogleAuth({ credentials: parsed, scopes })` で認証し `google.drive({ version: "v3", auth })` に渡す。
- **公開 API:** `export function getDriveClient(): ReturnType<typeof google.drive>`（型は実装に合わせて調整可）。
- テスト隔離用に **`resetGoogleDriveClientForTests()`** のような限定 export を付けてもよい（Sheets 側と同パターン）。

**将来連携メモ（#953）:** `spreadsheet-revenue.ts` で **拠点 ID → Drive `fileId`** を解決したうえで、`getDriveClient()` 経由で **メディアダウンロード**し、サーバ側 **Excel ライブラリ**でパースする。

---

#### 1.3. File: `backend/src/lib/google-drive-client.test.ts` (新規)

##### 1.3.1. Missing / whitespace env throws

`GOOGLE_SERVICE_ACCOUNT_JSON` が未設定または空白のみのとき `getDriveClient()` が `Error` を throw することを検証。

##### 1.3.2. Invalid JSON

不正 JSON で失敗することを検証。

##### 1.3.3. Valid JSON + mocked `google.drive`

`vi.mock("googleapis", ...)` で `GoogleAuth` と `google.drive` を差し替え、`version: "v3"` および **`drive.readonly`** scope が渡ることを検証。

**既存パターン参照:** `google-sheets-client.test.ts` / `driver-allocation.test.ts` の `vi.hoisted` + ESM 動的 import ／ `resetModules`。

---

#### 1.4. （任意）File: `backend/src/lib/google-sheets-client.ts`

Google **Sheets ネイティブ**ファイルを読む補助経路として **`spreadsheets.readonly`** の **`getSheetsClient()`** を残す／追加する場合のメモ。**本番 `.xlsx` の直読みには不要。** 仕様は従来どおり singleseton・同一 env・テスト隔離。詳細は別途または既存 plan を参照。

---

#### 1.5. File: `backend/.env.example`

##### 1.5.1. Document `GOOGLE_SERVICE_ACCOUNT_JSON`

**変更内容:**

- `GOOGLE_SERVICE_ACCOUNT_JSON` を追加。**値の例**はリポジトリにキー全文を載せない。
- GCP でサービスアカウントを作成し JSON キーを発行する旨。
- **本番経路（Drive `.xlsx`）:** 対象 **ファイルまたはフォルダ** をサービスアカウント **`client_email`** に **閲覧者**で共有する旨。
- アプリが Drive API を呼ぶときは **`https://www.googleapis.com/auth/drive.readonly`** を使う旨（本モジュールの前提）。
- （任意）Sheets API のみ使う開発向けに **`spreadsheets.readonly`** とスプレッドシート共有を一言。
- 本番ではシークレット管理（SSM 等）を使う旨。

---

## 実装順序 (Implementation Order)

1. **Backend: 依存追加** — 1.1.1 `npm install googleapis`
2. **Backend: Drive 実装** — 1.2.1 `google-drive-client.ts`
3. **Backend: Drive テスト** — 1.3.x `google-drive-client.test.ts`
4. **Backend: ドキュメント** — 1.5.1 `.env.example`
5. **（任意）Sheets 補助** — 1.4 が未実装なら後追いでも可
6. **統合確認** — `npm test` / `npm run build`

**Frontend:** なし

---

## 見積もり工数 (Estimated Effort)

Issue ラベル **sp:3** 目安（**約 0.5 人日**）。**Drive クライアント新規**（`google-drive-client.ts` + テスト + `.env.example` 更新）を含む場合の内訳:

| 作業 | 目安 |
|------|------|
| `googleapis` 依存・確認 | 0.25〜0.5 h |
| `google-drive-client.ts`（singleton・`drive.readonly`・env／解析エラー） | 1〜1.5 h |
| `google-drive-client.test.ts`（Vitest・`googleapis` モック・singleton リセット） | 1〜2 h |
| `.env.example` 文言・軽いドキュメント | 0.25〜0.5 h |
| ビルド／テスト通過確認・レビュー調整 | 0.25〜0.5 h |

**合計（Backend）:** **約 3〜5 時間**（初めて `googleapis` をモックする場合やレビュー指摘が多いと上限寄り）。

**すでに `google-sheets-client` が実装済み**で Drive のみ追加する場合、パターン流用で **下限〜中央**に寄りやすい。

**（任意）Sheets 補助モジュールの新規・二重保守:** +0〜1 h（要件次第）。

**Frontend:** **0 時間**

---

## 技術的な注意事項 (Technical Notes)

1. **Google Drive API と `.xlsx`:**

    - Drive 上の **Office `.xlsx`** は **Drive `fileId`** で参照し、`files.get` 等でバイトを取得して **サーバパース**する。
    - **Sheets API** は **Google Sheets ドキュメント**用。`.xlsx` を Sheets に変換しない方針なら **Drive API が必須**。

2. **パフォーマンス:**

    - **シングルトン**で `GoogleAuth`／**Drive** クライアントの再生成を避ける。
    - ダウンロードは BE-B でキャッシュ／TTL を検討してよい（本 issue では未着手）。

3. **UX / エラー設計（将来 BE-B）:**

    - **403 / 404** は共有漏れ・`fileId` 誤り・削除済み等と切り分けやすいログが望ましい。

4. **データ整合性・セキュリティ:**

    - **`drive.readonly`** のみで誤更新リスクを抑える。
    - 資格情報・レスポンス本文をログに出さない。

5. **既存機能との互換性:**

    - `spreadsheet-revenue.ts` は **本 issue では変更しない**（スタブのまま）。**実読取は #953** で Drive クライアントを利用して実装。

6. **シングルトンのユニットテスト隔離:**

    - `vi.resetModules()` + 動的 `import("./google-drive-client.js")`、または **`resetGoogleDriveClientForTests()`** でテスト間を隔離する。

7. **`GoogleAuth` と資格情報:**

    - 標準のサービスアカウント JSON を `credentials` に渡す。Drive と Sheets で **別モジュール**にするとスコープが混ざらず明確（同一 JSON を両方から読む）。

8. **プルモデルと「リアルタイム」:**

    - 最新データは **都度取得したバイト**が正。**変更通知（watch）** で再取得タイミングを詰めるのは任意。

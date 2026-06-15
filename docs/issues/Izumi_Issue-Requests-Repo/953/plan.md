# Issue #953: [001] P0: 売上スプレッドシート連携 — Implementation Plan

## 概要 (Overview)

### 現状 (Current State)
`backend/src/lib/spreadsheet-revenue.ts` の `getRevenueFromSpreadsheets` は**常に空 Map を返すスタブ**のまま。
そのため `income-statement.ts`・`dashboard.ts` から呼び出されても売上セル（山崎製パン〜関東運輸）は一律 **0 表示**。

### あるべき姿 (Target State)
1. `Location` モデルに各拠点の **Google Sheets ID** を保存し、管理 API で設定できる。
2. Google Sheets API（サービスアカウント認証）で指定年月・拠点のシートを読み取る。
3. **シート形式（本タスクで確定）**: タブ名 `YYYY-MM`、1 行目ヘッダー `vehicleNo + 勘定科目名列...`、2 行目以降に車両×金額データ。
4. `getRevenueFromSpreadsheets` が `Map<"vehicleId-accountItemId", amount>` を正しく返す。
5. 未設定・取得失敗時は **空 Map を返してゼロ表示**（fallback）、サーバーログに警告を出力。
6. `income-statement.ts` / `dashboard.ts` の呼び出し箇所はすでに merge ロジック実装済み — 追加修正最小限。

---

## ✅ Definition of Done — Mapping to Tasks

> 各 DoD 条件が**どのタスクで達成されるか**を明示します。コードレビュー・QA の基準として使用してください。

---

### DoD 1 — 参照元スプレッドシートの URL/ID を決定し、拠点とのマッピングを設定できること
> *Xác định URL/ID bảng tính tham chiếu và thiết lập ánh xạ với địa điểm.*

**達成条件の詳細:**
- `Location` テーブルに `spreadsheetId` カラムが存在する
- `PATCH /api/locations/:id` で各拠点の `spreadsheetId` を設定・更新できる
- `GET /api/locations` のレスポンスに `spreadsheetId` が含まれる

**対応タスク:**
| # | File | タスク |
|---|------|--------|
| BE 1.1 | `backend/prisma/schema.prisma` | `Location.spreadsheetId String?` 追加 |
| BE 1.2 | `backend/prisma/migrations/` | マイグレーション実行 |
| BE 1.6 | `backend/src/routes/locations.ts` | `PATCH /:id` エンドポイント追加 |

**確認方法:**
```bash
# spreadsheetId を設定
curl -X PATCH http://localhost:4000/api/locations/{locationId} \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"spreadsheetId":"1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms"}'

# 設定確認
curl http://localhost:4000/api/locations | jq '.[].spreadsheetId'
```

---

### DoD 2 — 認証（サービスアカウント等）を実装し、スプレッドシートからデータを取得できること
> *Triển khai xác thực (tài khoản dịch vụ) và có thể lấy dữ liệu từ bảng tính.*

**達成条件の詳細:**
- `GOOGLE_SERVICE_ACCOUNT_JSON` 環境変数を設定すると Google Sheets API へ接続できる
- サービスアカウントが対象スプレッドシートに「閲覧者」として共有されている
- 認証エラー時はサーバーログに `[spreadsheet] Failed to fetch...` が出力される（クラッシュしない）

**対応タスク:**
| # | File | タスク |
|---|------|--------|
| BE 1.3 | `backend/src/lib/google-sheets-client.ts` | Google Auth シングルトン実装 |
| BE 1.5 | `backend/.env.example` | `GOOGLE_SERVICE_ACCOUNT_JSON` 変数追加・ドキュメント化 |
| — | GCP コンソール | サービスアカウント作成・JSON キー発行・スプレッドシート共有 |

**確認方法:**
- `.env` に `GOOGLE_SERVICE_ACCOUNT_JSON` を設定してサーバー起動
- ログに認証エラーが出ないことを確認
- `getSheetsClient()` を呼び出して例外が発生しないことをユニットテストで確認

---

### DoD 3 — `getRevenueFromSpreadsheets` の本実装が完了し、vehicleNo + 勘定科目 → 金額のパースが正しく動作すること
> *Hoàn thành triển khai thực tế của `getRevenueFromSpreadsheets`, phân tích vehicleNo + khoản mục kế toán → số tiền.*

**達成条件の詳細:**
- `spreadsheetId` + `yearMonth`（タブ名）でシートを読み取れる
- ヘッダー行から勘定科目名（`AccountItem.name` と一致）を解析できる
- データ行の `vehicleNo` → `vehicleId` の変換が正しく動作する
- 戻り値が `Map<"vehicleId-accountItemId", amount>` 形式で返る
- カンマ区切り数値（`"1,500,000"`）・通常数値（`1500000`）の両方をパース

**対応タスク:**
| # | File | タスク |
|---|------|--------|
| BE 1.4 | `backend/src/lib/spreadsheet-revenue.ts` | スタブを本実装に置き換え（全ロジック） |
| BE 1.8 | `backend/src/lib/spreadsheet-revenue.test.ts` | パースロジックのユニットテスト |

**確認方法 (ユニットテスト):**
```typescript
// モックシートデータ
headers: ["vehicleNo", "山崎製パン", "ヤマザキ物流"]
row:     ["001-001",   "1500000",   "200,000"]

// 期待 Map エントリ
"vehicleId_001_001-accountItemId_yamazaki" → 1500000
"vehicleId_001_001-accountItemId_yamakoji" → 200000
```

---

### DoD 4 — 指定年月・拠点で、スプレッドシート参照対象の売上科目に正しい金額が表示されること
> *Số tiền chính xác được hiển thị cho các khoản mục doanh thu tham chiếu bảng tính theo năm tháng và địa điểm.*

**達成条件の詳細:**
- `GET /api/income-statement?yearMonth=YYYY-MM&locationId=...` のレスポンスの `records` に売上科目（山崎製パン〜関東運輸）の正しい金額が含まれる
- `GET /api/dashboard/summary?yearMonth=YYYY-MM` のロケーション別 `netRevenue` に売上が反映される
- `GET /api/income-statement/export?yearMonth=YYYY-MM` のCSVに売上金額が出力される

**対応タスク:**
| # | File | タスク |
|---|------|--------|
| — | `backend/src/routes/income-statement.ts` | **変更不要**（L243–252, L444–453 の呼び出しはすでに実装済み） |
| — | `backend/src/routes/dashboard.ts` | **変更不要**（L210–219 の呼び出しはすでに実装済み） |
| DoD 2, 3 の実装 | — | DoD 2+3 が完了すれば自動的に達成 |

**確認方法 (E2E):**
```bash
# 1. spreadsheetId を設定（DoD 1）
# 2. 実際のシートに yearMonth タブ・vehicleNo・売上金額を入力
# 3. API を叩いて金額を確認
curl "http://localhost:4000/api/income-statement?yearMonth=2026-04&locationId={id}" \
  | jq '.records | to_entries | map(select(.value > 0))'
```

---

### DoD 5 — 未接続・取得失敗時の挙動が仕様化され、income-statement.ts・dashboard.ts と整合していること
> *Hành vi khi chưa kết nối / lấy dữ liệu thất bại được quy định và tích hợp với income-statement.ts và dashboard.ts.*

> ⚠️ **[要 PM 確認 — engineering-backlog.md P0 タスク 4 より]**
> `engineering-backlog.md` には「MonthlyRecord フォールバック / 画面警告 / ゼロ表示 の**いずれかを明文化**すること」と記載されており、**fallback 方針はまだ決定していない**。
> 本実装に入る前に PM・設計者と以下の 3 択を確認・決定してください:
>
> | 選択肢 | 概要 | 追加実装コスト |
> |--------|------|----------------|
> | **A) ゼロ表示**（現 plan 採用） | 未接続・失敗時は 0 表示。income-statement.ts の既存 skip ロジックと整合 | なし（スタブと同動作） |
> | **B) 画面警告**（バナー等） | API レスポンスに `spreadsheetWarnings` フラグを追加し、FE が警告表示 | BE + FE 追加実装が必要 |
> | **C) MonthlyRecord フォールバック** | 未接続時は MonthlyRecord の値を使用。ただし L168–185 の skip ロジックを変更する必要あり | income-statement.ts / dashboard.ts の変更が必要 |
>
> **現 plan は選択肢 A を採用**。PM 確認後に変更がある場合は plan を更新してください。

**達成条件の詳細（本タスクでの仕様確定）:**

| ケース | 挙動 | 実装場所 |
|--------|------|---------|
| `spreadsheetId` 未設定 | `console.warn` → 空 Map → **ゼロ表示** | `spreadsheet-revenue.ts` |
| `GOOGLE_SERVICE_ACCOUNT_JSON` 未設定 | `getSheetsClient` throw → catch → 空 Map → **ゼロ表示** | `spreadsheet-revenue.ts` |
| Sheets API エラー（認証・タブ不在・ネットワーク等） | `console.error` → 空 Map → **ゼロ表示** | `spreadsheet-revenue.ts` |
| `income-statement.ts` への影響 | 空 Map の場合は既存 recordMap を上書きしない → 手入力科目は保持 | 変更不要（既実装） |
| `dashboard.ts` への影響 | 同上 | 変更不要（既実装） |

> ⚠️ **「MonthlyRecord フォールバック」について**: 現仕様では売上科目（山崎製パン〜関東運輸）は `revenueFromSpreadsheetIds` に含まれ MonthlyRecord を**スキップ**する（`income-statement.ts` L180–185）。
> したがって「フォールバック = MonthlyRecord 参照」ではなく「フォールバック = ゼロ表示」が現在の正しい仕様。
> MonthlyRecord フォールバックが必要な場合は別途仕様確認が必要。

**対応タスク:**
| # | File | タスク |
|---|------|--------|
| BE 1.4 | `backend/src/lib/spreadsheet-revenue.ts` | try/catch・warn/error ログ・空 Map 返却 |
| BE 1.8 | `backend/src/lib/spreadsheet-revenue.test.ts` | 各エラーケースのユニットテスト |

**確認方法 (ユニットテスト):**
```typescript
// ① spreadsheetId 未設定 → 空 Map
expect(await getRevenueFromSpreadsheets({...})).toEqual(new Map());

// ② Sheets API エラー → 空 Map（例外を throw しない）
vi.mocked(getSheetsClient).mockImplementation(() => { throw new Error("auth"); });
expect(await getRevenueFromSpreadsheets({...})).toEqual(new Map());
```

---

## FE (Frontend)

> **方針**: 損益計算書の表示ロジックは BE のみで完結。FE への変更は「拠点別スプレッドシート ID 設定 UI」のみ。本フェーズでは管理 API（PATCH）を先に実装し、FE 設定画面は任意追加（既存管理画面への追記）。

### 1. Files need to edit:

#### 1.1. File: フロントエンド管理画面（対象ページ未確定 — 任意）

##### 1.1.1. 拠点設定画面への `spreadsheetId` 入力フィールド追加

**概要:** 各拠点の Google Sheets ID（URL の `/d/XXXXXXXX/` 部分）を入力・保存できる UI。
フェーズ 1 では API 直接呼び出しでも設定可能なため、**FE は後回し可**。

**変更内容:**
- `PATCH /api/locations/:id` を呼び出す `<input type="text">` を追加
- 保存時に `{ spreadsheetId }` を PATCH
- 設定済み / 未設定の視覚的区別（例: バッジ表示）

> **FE はこのタスク単体では必須ではない。** 先に API を実装し、動作確認後に UI を追加する。

---

## BE (Backend)

### 1. Files need to edit / create:

#### 1.1. File: `backend/prisma/schema.prisma`

##### 1.1.1. `Location` モデルに `spreadsheetId` フィールドを追加

**現在の実装 (L29–40):**
```prisma
model Location {
  id        String   @id @default(cuid())
  code      String   @unique
  name      String
  vehicles  Vehicle[]
  ...
}
```

**変更内容:**
- `spreadsheetId String?` フィールドを追加（nullable、未設定拠点は null）
- コメント: `// 売上スプレッドシート連携用 Google Sheets ID（/d/{id}/ の部分）`

```prisma
model Location {
  id            String   @id @default(cuid())
  code          String   @unique
  name          String
  spreadsheetId String?  // 売上スプレッドシート連携用 Google Sheets ID
  vehicles      Vehicle[]
  ...
}
```

---

#### 1.2. File: `backend/prisma/migrations/` (新規マイグレーション)

##### 1.2.1. `spreadsheetId` カラム追加マイグレーション

**変更内容:**
- `npx prisma migrate dev --name add-location-spreadsheet-id` を実行して生成
- 生成される SQL (MySQL 対象):
  ```sql
  ALTER TABLE `Location` ADD COLUMN `spreadsheetId` VARCHAR(191) NULL;
  ```

---

#### 1.3. File: `backend/src/lib/google-sheets-client.ts` (新規作成)

##### 1.3.1. Google Sheets API クライアント シングルトンの実装

**概要:** `googleapis` ライブラリのサービスアカウント認証クライアントをラップし、シングルトンで提供する。

**変更内容:**

```typescript
import { google } from "googleapis";

let sheetsClient: ReturnType<typeof google.sheets> | null = null;

export function getSheetsClient() {
  if (sheetsClient) return sheetsClient;

  const credentialsJson = process.env.GOOGLE_SERVICE_ACCOUNT_JSON;
  if (!credentialsJson) {
    throw new Error("[spreadsheet] GOOGLE_SERVICE_ACCOUNT_JSON is not set");
  }

  const credentials = JSON.parse(credentialsJson);
  const auth = new google.auth.GoogleAuth({
    credentials,
    scopes: ["https://www.googleapis.com/auth/spreadsheets.readonly"],
  });

  sheetsClient = google.sheets({ version: "v4", auth });
  return sheetsClient;
}
```

**依存パッケージ追加:**
```bash
cd backend && npm install googleapis
```

---

#### 1.4. File: `backend/src/lib/spreadsheet-revenue.ts`

##### 1.4.1. スタブを本実装に置き換え

**現在の実装 (L1–37):**
```typescript
export async function getRevenueFromSpreadsheets(
  _params: GetRevenueFromSpreadsheetsParams
): Promise<Map<string, number>> {
  // スタブ: 空の Map を返す
  return new Map();
}
```

**変更内容 — 実装ロジック全体:**

```typescript
import { prisma } from "./prisma.js";
import { getSheetsClient } from "./google-sheets-client.js";

export async function getRevenueFromSpreadsheets(
  params: GetRevenueFromSpreadsheetsParams
): Promise<Map<string, number>> {
  const { locationId, yearMonth, vehicleIds, revenueAccountItemIds } = params;
  const result = new Map<string, number>();

  // 1. 拠点の spreadsheetId を取得
  const location = await prisma.location.findUnique({
    where: { id: locationId },
    select: { spreadsheetId: true, name: true },
  });

  if (!location?.spreadsheetId) {
    console.warn(
      `[spreadsheet] Location ${locationId} has no spreadsheetId configured — returning empty revenue`
    );
    return result;
  }

  // 2. vehicleId → vehicleNo マッピング
  const vehicles = await prisma.vehicle.findMany({
    where: { id: { in: vehicleIds } },
    select: { id: true, vehicleNo: true },
  });
  const vehicleNoToId = new Map(vehicles.map((v) => [v.vehicleNo, v.id]));

  // 3. accountItemId → name マッピング
  const accountItems = await prisma.accountItem.findMany({
    where: { id: { in: revenueAccountItemIds } },
    select: { id: true, name: true },
  });
  const accountNameToId = new Map(accountItems.map((a) => [a.name, a.id]));

  // 4. Google Sheets API 呼び出し（タブ名 = yearMonth）
  let rows: string[][];
  try {
    const sheets = getSheetsClient();
    const range = `${yearMonth}!A1:ZZ`;
    const response = await sheets.spreadsheets.values.get({
      spreadsheetId: location.spreadsheetId,
      range,
    });
    rows = (response.data.values ?? []) as string[][];
  } catch (err) {
    console.error(
      `[spreadsheet] Failed to fetch sheet for location ${locationId} (${location.name}), yearMonth=${yearMonth}:`,
      err
    );
    return result; // 取得失敗時はゼロ表示（fallback）
  }

  if (rows.length < 2) return result; // ヘッダーのみ or 空シート

  // 5. ヘッダー行から勘定科目列インデックスを取得
  const headers = rows[0];
  // headers[0] = "vehicleNo", headers[1..] = 勘定科目名
  const colIndexByAccountName = new Map<string, number>();
  for (let i = 1; i < headers.length; i++) {
    colIndexByAccountName.set(headers[i], i);
  }

  // 6. データ行をパース: vehicleNo → vehicleId + 金額
  for (let r = 1; r < rows.length; r++) {
    const row = rows[r];
    const vehicleNo = row[0]?.trim();
    if (!vehicleNo) continue;

    const vehicleId = vehicleNoToId.get(vehicleNo);
    if (!vehicleId) continue; // 不明な vehicleNo はスキップ

    for (const [accountName, colIdx] of colIndexByAccountName) {
      const accountItemId = accountNameToId.get(accountName);
      if (!accountItemId) continue;

      const rawValue = row[colIdx] ?? "";
      const amount = parseFloat(rawValue.replace(/,/g, "")) || 0;
      result.set(`${vehicleId}-${accountItemId}`, amount);
    }
  }

  return result;
}
```

**シート形式（本タスクで確定する仕様）:**

| 行 | A列 | B列 | C列 | ... |
|----|-----|-----|-----|-----|
| 1 (ヘッダー) | vehicleNo | 山崎製パン | ヤマザキ物流 | ... |
| 2 | 001-001 | 1500000 | 200000 | ... |
| 3 | 001-002 | 1200000 | 0 | ... |

- **タブ（シート名）**: `YYYY-MM`（例: `2026-03`）
- `vehicleNo` は `Vehicle.vehicleNo`（コース番号）と一致させること
- 勘定科目列名は `AccountItem.name` と**完全一致**させること
- 金額はカンマ区切り数値 or 数値（どちらも対応）
- 拠点ごとに 1 スプレッドシート、月ごとにタブを作成

---

#### 1.5. File: `backend/.env.example`

##### 1.5.1. Google 認証情報の環境変数を追加

**変更内容 (追記):**

```bash
# Google Sheets API（サービスアカウント）
# GCP コンソールでサービスアカウントを作成し、JSON キーを発行。
# 各拠点スプレッドシートにサービスアカウントのメールを閲覧者として共有すること。
# JSON の内容をそのまま設定（改行は \n でエスケープ不要 — シングルクォート内に貼る）
GOOGLE_SERVICE_ACCOUNT_JSON='{"type":"service_account","project_id":"...","private_key_id":"...","private_key":"-----BEGIN RSA PRIVATE KEY-----\n...","client_email":"...@....iam.gserviceaccount.com",...}'
```

---

#### 1.6. File: `backend/src/routes/locations.ts`

##### 1.6.1. `spreadsheetId` 管理用 PATCH エンドポイントを追加

**現在の実装 (L1–11):**
```typescript
locationsRouter.get("/", async (_req: Request, res: Response) => {
  const locations = await prisma.location.findMany(...);
  res.json(locations);
});
```

**変更内容 — PATCH エンドポイント追加:**

```typescript
import { requireRole, ROLES } from "../lib/auth.js";

// PATCH /api/locations/:id — spreadsheetId 設定（MASTER 権限）
locationsRouter.patch("/:id", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  const { id } = req.params;
  const { spreadsheetId } = req.body as { spreadsheetId?: string | null };

  const location = await prisma.location.update({
    where: { id },
    data: { spreadsheetId: spreadsheetId ?? null },
  });
  res.json(location);
});
```

---

#### 1.7. File: `backend/src/routes/income-statement.ts`

##### 1.7.1. スプレッドシート取得失敗時のレスポンスへの警告フラグ追加（任意）

**現在の実装 (L240–252):**
```typescript
// 売上科目（手入力専用以外）は各拠点スプレッドシート参照のみ
const revenueAccountItemIds = Array.from(revenueFromSpreadsheetIds);
if (revenueAccountItemIds.length > 0) {
  const spreadsheetRevenue = await getRevenueFromSpreadsheets({ ... });
  spreadsheetRevenue.forEach((amount, key) => {
    recordMap.set(key, amount);
  });
}
```

**変更内容（任意 — フェーズ 2）:**
- `getRevenueFromSpreadsheets` の戻り値に `{ data: Map, warnings: string[] }` を追加する形に拡張し、フロントエンドにワーニング情報を伝える案もあるが、**フェーズ 1 では現在の空 Map fallback のままで十分**。
- 現在の呼び出し箇所（L243–252 および export エンドポイント L444–453）は変更不要。

##### 1.7.2. `dashboard.ts` との整合性確認

**現在の実装 (dashboard.ts L202–220):**
```typescript
// 売上科目（手入力専用以外）は各拠点スプレッドシート参照のみ
if (revenueAccountItemIds.length > 0) {
  for (const locId of locationIds) {
    const spreadsheetRevenue = await getRevenueFromSpreadsheets({ ... });
    spreadsheetRevenue.forEach((amount, key) => { recordMap.set(key, amount); });
  }
}
```

**変更内容:** `dashboard.ts` 側の呼び出しも同関数を使うため、**関数実装後は自動的に反映される。変更不要**。

---

#### 1.8. File: `backend/src/lib/spreadsheet-revenue.test.ts` (新規作成)

##### 1.8.1. ユニットテスト（Vitest）

**変更内容:**
- `getSheetsClient` をモック（vi.mock）
- spreadsheetId 未設定時に空 Map を返すことを確認
- Google Sheets API エラー時に空 Map を返すことを確認
- 正常データのパース: vehicleNo → vehicleId、勘定科目名 → accountItemId、金額マッピングを検証
- カンマ区切り金額（`"1,500,000"`）のパースを検証
- 不明な vehicleNo / 勘定科目名はスキップされることを確認

---

## 実装順序 (Implementation Order)

1. **[BE] スプレッドシート形式を確定・合意**（PM/現場確認）
   - タブ名 = `YYYY-MM`
   - ヘッダー行の勘定科目名列の並びを確認
   - 各拠点担当者にフォーマット統一を依頼

2. **[BE] Prisma スキーマ変更 + マイグレーション** (`schema.prisma` → migrate)
   - `Location.spreadsheetId` 追加

3. **[BE] `googleapis` インストール + `google-sheets-client.ts` 作成**
   - GCP でサービスアカウント作成、JSON キー発行
   - 各拠点スプレッドシートにサービスアカウントを閲覧者共有

4. **[BE] `getRevenueFromSpreadsheets` 本実装** (`spreadsheet-revenue.ts`)
   - DB から spreadsheetId 取得 → Sheets API 呼び出し → パース → Map 返却

5. **[BE] `PATCH /api/locations/:id` 追加** (`locations.ts`)
   - 各拠点の spreadsheetId を設定できるようにする

6. **[BE] `.env.example` 更新 + 実環境への設定**

7. **[BE] ユニットテスト作成** (`spreadsheet-revenue.test.ts`)

8. **[E2E] 動作確認**: 年月・拠点を指定して GET /api/income-statement → 売上セルに正しい金額が返ることを確認

9. **[FE] 管理 UI（任意）**: 拠点設定画面に spreadsheetId 入力フィールドを追加

---

## 見積もり工数 (Estimated Effort)

### Backend

| タスク | 工数 |
|--------|------|
| Prisma スキーマ変更 + マイグレーション | 0.5h |
| `googleapis` セットアップ + `google-sheets-client.ts` | 1h |
| `getRevenueFromSpreadsheets` 本実装 | 3h |
| `PATCH /api/locations/:id` 追加 | 0.5h |
| `.env.example` 更新 | 0.25h |
| ユニットテスト (`spreadsheet-revenue.test.ts`) | 2h |
| E2E 動作確認（実シートへの接続テスト） | 1h |
| **Backend 小計** | **8〜10h** |

### Frontend（任意）

| タスク | 工数 |
|--------|------|
| 拠点設定 UI に spreadsheetId フィールド追加 | 1〜2h |
| **Frontend 小計** | **1〜2h** |

### 確認・調整

| タスク | 工数 |
|--------|------|
| 拠点担当者とシートフォーマット合意 | 1〜2h |
| **合計** | **10〜14h** |

---

## 技術的な注意事項 (Technical Notes)

### 1. Google Sheets API のレート制限とパフォーマンス

- Google Sheets API の読み取り上限: 300 req/min（プロジェクト単位）
- `dashboard.ts` は全拠点ループで複数回 `getRevenueFromSpreadsheets` を呼び出す（現在 L205–219）。
  - 拠点数が多い場合はリクエスト数が増加するため、**将来的にキャッシュ（5〜15 分 TTL）の追加を検討**
  - フェーズ 1 では許容範囲（拠点数は数十件程度）
- タブ名（yearMonth）が存在しない場合は Sheets API が 400 を返す → `try/catch` で空 Map fallback

### 2. 認証・セキュリティ

- **サービスアカウント JSON は環境変数で管理**（`.env` 非コミット）
- 各拠点スプレッドシートにサービスアカウントのメールを**閲覧者（Viewer）として共有**する必要あり（書き込み権限不要）
- `googleapis` は `scopes: ["spreadsheets.readonly"]` のみ要求

### 3. シートフォーマットの堅牢性

- `vehicleNo` 列（A列）は `Vehicle.vehicleNo`（例: `"001-001"`）と**完全一致**（前後スペースは trim）
- 勘定科目ヘッダー名は `AccountItem.name` と**完全一致**が必要 — 合わない列は無視（スキップ）
- 金額列はカンマ区切り (`"1,500,000"`) または数値 (`1500000`) を両方許容
- 不明な vehicleNo・勘定科目名はスキップ（サーバーログに warn）

### 4. 未接続・取得失敗時の挙動（フォールバック仕様）

| ケース | 挙動 |
|--------|------|
| `spreadsheetId` が null / 未設定 | console.warn、空 Map 返却 → ゼロ表示 |
| Google Sheets API エラー（認証失敗・タブ不在等） | console.error、空 Map 返却 → ゼロ表示 |
| `GOOGLE_SERVICE_ACCOUNT_JSON` 未設定 | `getSheetsClient()` でエラー throw → `getRevenueFromSpreadsheets` 内で catch → ゼロ表示 |

> **画面警告（フェーズ 2 検討）:** フロントエンドへの警告フラグ追加は フェーズ 1 対象外。ゼロ表示で実害を確認しながら判断。

### 5. 既存機能との互換性

- `getRevenueFromSpreadsheets` のインターフェース（`GetRevenueFromSpreadsheetsParams`、戻り値の `Map<string, number>`）は変更しない
- `income-statement.ts` L249–252 および `dashboard.ts` L216–218 の呼び出し箇所は**変更不要**
- フォールバックが空 Map のため、既存の `MonthlyRecord` ベースの手入力専用売上（その他・不動産収入・人材派遣収入）には影響しない

### 6. 将来拡張

- 複数スプレッドシート対応（複数年月×拠点）: 現状は拠点ごと 1 スプレッドシート（タブで月次分割）
- キャッシュ層の追加（Redis or インメモリ TTL）でパフォーマンス改善
- フロントエンド警告表示（スプレッドシート未設定・取得失敗バナー）

---

## 参考リンク・ファイル

| ドキュメント | 内容 |
|-------------|------|
| `backend/src/lib/spreadsheet-revenue.ts` | 現スタブ（本実装対象） |
| `backend/src/routes/income-statement.ts` L240–252 | 売上スプレッドシート merge ロジック |
| `backend/src/routes/dashboard.ts` L202–220 | dashboard 側の呼び出し |
| `backend/prisma/schema.prisma` L29–40 | Location モデル（spreadsheetId 追加対象） |
| `docs/account-item-calculation-spec.md` §2.1 | 売上科目一覧（スプレッドシート対象科目の名前確認） |
| `docs/external-integration-spec.md` §1.2 §6.1 | 外部連携仕様・売上データの取り扱い方針 |

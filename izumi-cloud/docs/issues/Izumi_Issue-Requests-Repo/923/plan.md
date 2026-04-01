# Issue #923: [FE] CI: GitHub Actions（lint/Jest/Mix）/ CI Frontend: ESLint, Jest, Mix build - Implementation Plan

## 概要 (Overview)

**現状:** `TeckVeho/izumi-cloud` には `.github/workflows/release-labeling.yaml` のみあり、**Node / ESLint / Jest / Laravel Mix の CI は未定義**。`package.json` には `lint` / `test` / `production` が既にある（lines 12–14）。

**目標:** `.github/workflows/frontend-ci.yml` に **job `frontend`** を定義し、PR / push で `npm ci` → `npm run lint` → `npm run test` → `npm run production` を実行する（FE のみ；`backend-ci` / `deploy-ci-*` は別 workflow）。アプリの Vue ソース（`resources/js` の画面ロジック）は **変更しない**。親 issue **#914** 向けの CI/CD ドキュメントに、**FE CI 失敗時の再現手順** を追記する（パスは `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md` を想定；未作成なら新規作成）。

**注意:** Jest は `jest.config.js` と `resources/js/tests/setup.js` で `dotenv` を読み込み、`MIX_LARAVEL_TEST_URL` を `testURL` に使用するため、CI では **環境変数の明示** が必要（`.env` が無い runner でも通るようにする）。

---

## FE (Frontend)

本 issue における「FE」は **UI 改修ではなくフロントエンドツールチェーン（npm / GitHub Actions）** を指す。

### 1. Files need to edit:

#### 1.1. File: `.github/workflows/frontend-ci.yml`（新規）

##### 1.1.1. Workflow skeleton & triggers

**既存コード:** 同ファイルは **存在しない**（新規追加）。既存の `release-labeling.yaml` は PR merge 後ラベルのみで、ビルドとは独立。

**変更内容:**

- `on`: チーム方針に合わせて **`pull_request`** と / または **`push`**（例: `develop`, `staging`, `production` または全ブランチ）。issue の受け入れは「PR / push で job が走る」ため、最低限 `pull_request` を含める。
- `permissions`: 既定の `contents: read` で足りる（デプロイ無しの場合）。
- job 名: `frontend`（issue 記載どおり）。

##### 1.1.2. Steps: checkout, Node, cache, npm scripts

**変更内容:**

- `actions/checkout@v4`（またはチーム標準バージョン）。
- `actions/setup-node@v4` に **`node-version`** を指定（例: `20`）。リポジトリに `.nvmrc` が無いため、**workflow と同一値** を単一ソースにするなら **別タスク 1.2** の `.nvmrc` / `engines` 追加を推奨。
- `actions/cache`（任意だが issue 推奨）: `path: ~/.npm`, key: `${{ runner.os }}-node-${{ hashFiles('package-lock.json') }}` など。`npm ci` の前に配置。
- `run: npm ci`（`package-lock.json` は root に存在）。
- 順に `npm run lint` → `npm run test` → `npm run production`。

**環境変数（Jest 用）:**

- job レベルまたは `npm run test` 直前の step で例:  
  `MIX_LARAVEL_TEST_URL: http://127.0.0.1/`  
  （`.env.example` line 84 の意図に合わせる。実 URL がテストで不要ならこのダミーで可；テストが実 HTTP に依存する場合は別途モック方針を `/dev` で確認。）

##### 1.1.3. Backend / deploy との分離

**変更内容:**

- リポジトリ方針: **FE は `frontend-ci.yml` のみ**。PHP/Composer は別ファイル（例: `backend-ci.yml`）、デプロイは `deploy-ci-dev` / `deploy-ci-staging` 等と **ワークフローを分ける**。同一 YAML に backend job を同居させない前提で、`frontend` job を単体で完結させる。

#### 1.2. File: `.nvmrc`（新規・推奨）

##### 1.2.1. Node バージョンの固定

**既存コード:** `.nvmrc` は **無い**。

**変更内容:**

- 1 行で LTS を固定（例: `20`）。`setup-node` の `node-version-file: '.nvmrc'` と組み合わせるとローカルと CI が一致しやすい。

#### 1.3. File: `package.json`（任意）

##### 1.3.1. `engines` フィールド

**既存コード:** `package.json` に `engines` なし（scripts は lines 3–14 付近）。

**変更内容:**

- チーム合意があれば `"engines": { "node": ">=20 <21" }` 等を追加し、CI とドキュメントの前提を揃える（**必須ではない**）。

#### 1.4. File: `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md`（新規または追記）

##### 1.4.1. FE CI 失敗時の再現手順

**既存コード:** `docs/issues/Izumi_Issue-Requests-Repo/914/` は **未作成の可能性**あり（issue.md 記載）。

**変更内容:**

- 親 #914 の計画どおりパスを優先。ファイルが無ければ **ディレクトリごと作成**。
- 少なくとも以下を記載:
  - 必要な Node バージョン（`.nvmrc` / workflow の値）。
  - ローカル手順: `npm ci` → `npm run lint` / `npm run test` / `npm run production`。
  - 失敗時: GitHub Actions の該当 job ログの見方、ローカルで同じコマンドを再実行する手順。
  - Jest 用: `MIX_LARAVEL_TEST_URL` を `.env` または export で設定する必要がある旨（`.env.example` line 84 参照）。

##### 1.4.2. `resources/js` 配下の Vue ファイル

**変更内容:** **触らない**（issue スコープ外）。

---

## BE (Backend)

### 1. Files need to edit:

**本 issue (#923) の範囲では、PHP / Laravel アプリケーションコードの変更は不要。**

#### 1.1. Backend CI は別 workflow（`backend-ci.yml` 等）

##### 1.1.1. 関連 issue との調整

**現在の実装:** Backend 向け CI（Composer / PHPUnit 等）は **別子 issue** で **`backend-ci.yml`（仮称）** として追加する想定。

**変更内容:**

- #923 では **`frontend-ci.yml` のみ**。BE は別 YAML で追加し、デプロイ（`deploy-ci-dev` / `deploy-ci-staging` 等）も別 workflow とする。

---

## 実装順序 (Implementation Order)

1. **Frontend ツールチェーン（CI）**（他に依存しない）

    - Node バージョン方針決定 →（推奨）`.nvmrc` 追加
    - `.github/workflows/frontend-ci.yml` 新規: `frontend` job、`MIX_LARAVEL_TEST_URL` 設定、cache、各 npm script
    - ローカルまたは `act` で動作確認（任意）

2. **ドキュメント**（CI コマンドが確定してから）

    - `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md` に FE 失敗時の再現手順を追記

3. **Backend CI**（本 issue の外）

    - 関連 issue 担当が `backend-ci.yml` を追加 → Actions で FE / BE 各ワークフローを確認

4. **統合テスト**

    - PR 上で GitHub Actions の全ジョブが緑になること
    - 既存 lint/test が赤い場合は issue 記載どおり **スコープ内で修正**またはチーム合意のドキュメント化

---

## 見積もり工数 (Estimated Effort)

- **Backend**: **0 時間**（#923 ではコード変更なし；統合は別 issue）

- **Frontend（CI + ドキュメント）**: **3–5 時間**

    - `frontend-ci.yml` 作成・キャッシュ・環境変数調整: 1.5–2.5 時間
    - 初回 CI での lint/test/build エラー対応（既存赤字の場合）: 0.5–2 時間
    - `cicd.md` 追記: 0.5–1 時間

**合計**: **3–5 時間**（赤字修正の大規模化は別相談）

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

    - `npm ci` は毎回クリーンインストールのため、`actions/cache` で `~/.npm` をキャッシュすると時間短縮になりやすい。

2. **UX 考慮:**

    - 本 issue はユーザー向け UI に変更を加えない。

3. **データ整合性:**

    - 該当なし（DB / API 変更なし）。

4. **既存機能との互換性:**

    - ソース変更を避け、ビルド成果物の挙動は本番ビルド（`production`）で検証。Laravel Mix 5 + webpack のメモリ使用量が大きい場合は `NODE_OPTIONS=--max-old-space-size=4096` を job に付与する選択肢あり。

5. **Jest / 環境:**

    - `jest.config.js`（lines 1–4, 24–25）と `resources/js/tests/setup.js`（line 3）が `dotenv` に依存するため、CI では **`.env` 無しでも最低限 `MIX_LARAVEL_TEST_URL` を workflow で供給**する。

6. **参考: 既存 npm scripts（`package.json`）**

```12:14:package.json
        "lint": "eslint --ext .js,.vue resources/js",
        "test": "jest --silent --runInBand --detectOpenHandles",
        "production": "cross-env NODE_ENV=production node_modules/webpack/bin/webpack.js --no-progress --hide-modules --config=node_modules/laravel-mix/setup/webpack.config.js",
```

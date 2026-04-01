# Issue #914: Excute CICD automatically_IC - Implementation Plan

## 概要 (Overview)

**要件:** GitHub Actions で **CI**（PHP テスト・Pint、FE の lint / ビルド）を PR / push で実行し、**develop** と **staging** を **Environment / Secrets / envbasic / CD workflow** で分離して自動デプロイする。実装対象は **cloud** リポジトリ。

**現状:**

- `.github/workflows/` には **`release-labeling.yaml`** のみ（staging / production マージ時の issue ラベル付け）。**CI・CD 用 workflow は未整備**。
- `phpunit.xml` は **SQLite メモリ** でテスト可能（CI にそのまま流用しやすい）。
- フロントは **Laravel Mix（webpack）**（`package.json` の `development` / `production`、`lint` は `eslint` + `.vue`）。
- `composer.json` に **`veho-dev/s3-logger` (dev-master)** あり — CI では **COMPOSER_AUTH** 等の認証が必要になる可能性が高い。

**目標状態:**

- PR / 指定ブランチ push で **CI が常に緑** になるまでコマンド・キャッシュを調整。
- GitHub **Environments** `develop` / `staging` と **専用 Secrets**。
- **envbasic** テンプレ（パスワードなし・プレースホルダ）と **cd-pipeline-dev.yml** / **cd-pipeline-staging.yml**。
- 短い **docs**（CI の見方、デプロイ手順、workflow リンク）。

---

## FE (Frontend)

*アプリの Vue 画面ロジックは変更しない。CI で `resources/js` を lint / テスト / ビルドする。*

### 1. Files need to edit:

#### 1.1. File: `.github/workflows/ci.yml`（新規）

##### 1.1.1. Job `frontend`（Node）

**目的:** PR / push で FE の品質ゲートを通す。

**既存コード:**

- `package.json`（scripts）:
  - `lint`: `eslint --ext .js,.vue resources/js`
  - `test`: `jest --silent --runInBand --detectOpenHandles`
  - `production`: Laravel Mix 本番ビルド

**変更内容:**

- `actions/checkout@v4`
- `actions/setup-node@v4` — Node バージョンはローカルと揃える（例: **20 LTS**、要確認）
- `npm ci`（`package-lock.json` あり）
- `npm run lint`
- `npm run test`（失敗が多い場合は段階的に `--passWithNoTests` やスコープ縮小を issue で合意）
- `npm run production`（Mix ビルドが CI で通ること）
- `node_modules` / npm cache の `actions/cache` を検討

#### 1.2. File: `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md`（新規・短い運用メモ）

##### 1.2.1. CI で FE が落ちたとき

**変更内容:**

- `npm run lint` / `npm run production` のログの見方、ローカル再現コマンドを記載。

---

## BE (Backend)

*アプリの Controller 等は変更しない。PHP テスト・スタイル・デプロイ automation を追加する。*

### 1. Files need to edit:

#### 1.1. File: `.github/workflows/ci.yml`（新規・同一ファイルに PHP job）

##### 1.1.1. Job `php`（Composer + PHPUnit + Pint）

**目的:** Laravel 12 / PHP 8.2 でテストとコードスタイルを担保。

**現在の実装:**

- `composer.json` の `scripts.test`: `config:clear` + `php artisan test`
- `phpunit.xml`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` など testing 用 env 済み

**変更内容:**

- `actions/checkout@v4`
- `shivammathur/setup-php@v2` — **PHP 8.2**、extensions: `mbstring`, `xml`, `curl`, `sqlite`, `pdo_sqlite` 等（プロジェクト要件に合わせて追加）
- **Composer 認証:** プライベートパッケージ用に `COMPOSER_AUTH` または `GITHUB_TOKEN` / `composer config http-basic`（組織方針に従う）
- `composer install --prefer-dist --no-progress`
- `cp .env.example .env` + `php artisan key:generate`（必要なら）
- `composer test` または `php artisan test --compact`
- `./vendor/bin/pint --test`（ルールはプロジェクト既存の Pint 設定に従う）

##### 1.1.2. トリガー設計

**変更内容:**

- `on`: `pull_request`（`develop` / `staging` / `main` 等・チームのデフォルトブランチに合わせる）、`push`（同ブランチ）
- `concurrency` で同ブランチの重複実行をキャンセル（任意）

#### 1.2. File: `.github/workflows/cd-pipeline-dev.yml`（新規）

##### 1.2.1. CD develop

**変更内容:**

- `on`: `workflow_dispatch` および / または `push` to **`develop`**（運用ルールに合わせて確定）
- `environment: develop`
- ジョブ例:
  - SSH（`SSH_PRIVATE_KEY` 等は **Environment secrets**）
  - `rsync` / `git pull` / `composer install --no-dev` / `php artisan migrate --force` / `npm ci && npm run production` 等（**サーバー手順をインフラと Chốt**）
  - ヘルスチェック（`curl` で URL develop）

#### 1.3. File: `.github/workflows/cd-pipeline-staging.yml`（新規）

##### 1.3.1. CD staging

**変更内容:**

- `environment: staging`
- develop と **同じステップ構成でも Secrets / env ファイルのみ差し替え**（issue の原則）

#### 1.4. Directory: `.github/workflows/envbasic/`（新規）

##### 1.4.1. `.env.basic_develop` / `.env.basic_staging`

**変更内容:**

- **実パスワード・API キーをコミットしない**（`KEY=` のみやダミー値）
- サーバー側で `cp` して `.env` を完成させる手順を `cicd.md` に書く
- `.gitignore` で誤コミットしそうなファイルがないか確認（テンプレのみなら通常はコミット可）

#### 1.5. GitHub リポジトリ設定（コード外）

##### 1.5.1. Environments & Secrets

**変更内容:**

- Repo **Settings → Environments**: `develop`, `staging` を作成（承認者・ブランチルール）
- 各 Environment に **専用** Secrets（SSH、DB、COMPOSER_AUTH 等）— **develop / staging で共有しない**

##### 1.5.2.（任意）Branch protection

**変更内容:**

- `develop` / `staging` に **Required status checks** = CI workflow

---

## 実装順序 (Implementation Order)

1. **インフラ・スコープ確定**（URL / SSH ユーザー / デプロイパス / `develop`・`staging` のブランチ戦略）
2. **GitHub で Actions 有効化** + **Environments `develop` / `staging` 作成**
3. **CI（`.github/workflows/ci.yml`）** — PHP + Node を先に緑にする（Composer 認証を最優先で解消）
4. **envbasic テンプレ** — パスワードなしでコミット可能な形
5. **CD develop** → 手動 / push で 1 回成功させ smoke test
6. **CD staging** → 同上
7. **ドキュメント**（`cicd.md` または `docs/` 配下の短い README リンク）
8. **（任意）Branch protection**

*本 issue ではアプリの FE/BE ソース改修は前提にしない。*

---

## 見積もり工数 (Estimated Effort)

- **Backend（PHP / Composer / CD / Secrets / サーバー手順）**: **12–24 時間**
  - CI 基盤・認証トラブルシュート: 3–8h
  - develop CD + 検証: 4–8h
  - staging CD + 検証: 3–6h
  - ドキュメント: 1–2h

- **Frontend（CI 内 lint / jest / Mix build の安定化）**: **4–10 時間**
  - workflow 組み込み: 1–2h
  - 既存 lint / test の CI 失敗修正（発生時）: 3–8h

**合計**: **16–34 時間**（インフラ権限・プライベート Composer・既存テストの赤の有無で幅あり）

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

   - `composer` / `npm` に `cache` を付与し job 時間を短縮。
   - テストは SQLite メモリのままなら DB サービスコンテナは不要。

2. **UX 考慮:**

   - デプロイ失敗時に **どの Environment で落ちたか** がログで分かるよう job 名・ `environment` を明示。

3. **データ整合性:**

   - `migrate --force` のタイミング（メンテ窓口）を運用で合意。ロールバック戦略をドキュメント化。

4. **既存機能との互換性:**

   - 既存の **`release-labeling.yaml`**（staging / production 向け）は **そのまま残し**、ブランチ名・運用と矛盾しないか確認。
   - **production** 向け自動 CD は本 issue のスコープ外だが、将来追加時は同様に Environment を分離する。

5. **セキュリティ:**

   - **Secrets を develop / staging で共有しない**（issue 要件）。
   - envbasic はテンプレのみ。本番相当の値は GitHub Secrets またはサーバー上でのみ保持。

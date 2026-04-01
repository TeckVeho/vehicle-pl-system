# CI/CD メモ（親 Issue #914）/ Ghi chép CI/CD (parent #914)

子 issue **#923**（Frontend CI）に関連する運用メモ。

## Node.js

- **推奨バージョン / Phiên bản chuẩn:** `14.21.3`（リポジトリルートの `.nvmrc` と GitHub Actions `frontend-ci.yml` の `setup-node` が参照）
- ローカル: `nvm use` または `fnm use`（`.nvmrc` があるディレクトリで）

## フロントエンド CI と同じコマンドをローカルで再現する / Reproduce FE CI locally

リポジトリルートで:

```bash
npm ci
npm run lint
npm run test
npm run production
```

### Jest 用の環境変数 / Biến môi trường cho Jest

CI では `MIX_LARAVEL_TEST_URL` を workflow で設定している。ローカルでは `.env`（`.env.example` の `MIX_LARAVEL_TEST_URL` を参照）か、一時的に:

```bash
export MIX_LARAVEL_TEST_URL=http://127.0.0.1/
npm run test
```

## GitHub Actions で FE job が落ちたとき / Khi job Frontend trên GA fail

1. PR の **Checks** タブ → ワークフロー **Frontend CI** → job **Frontend (lint, test, build)** を開く。
2. 失敗した **step**（ESLint / Jest / production）のログを確認する。
3. 上記と同じ順でローカル（Node `14.21.3` / `nvm use`）からコマンドを再実行し、同じエラーになるか確認する。
4. `npm ci` と CI のキャッシュを揃えるため、ローカルで `rm -rf node_modules && npm ci` も試す。

## Backend CI

PHP / Composer などの job は別子 issue で `.github/workflows/backend-ci.yml`（仮称）として追加予定。デプロイは `deploy-ci-dev` / `deploy-ci-staging` 等の別 workflow。追加後は本ドキュメントに backend 向けの再現手順を追記する。

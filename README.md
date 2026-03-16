# 車両別損益計算システム (IZUMI)

15拠点・約500台の車両を対象に、月次の損益計算書を車両単位で管理・表示するWebアプリケーションです。

## 技術スタック

- **フロントエンド**: Next.js 14 (App Router), TypeScript, Tailwind CSS + shadcn/ui
- **バックエンド**: Express
- **データベース**: ローカル開発は SQLite / 本番は AWS RDS (MySQL) / Prisma

## セットアップ

### 1. 依存関係のインストール

```bash
npm install
cd backend && npm install
```

### 2. バックエンドの環境変数

`backend/.env` を作成し、以下を設定してください。

```bash
cp backend/.env.example backend/.env
```

**ローカル開発（デフォルト）**: SQLite を使用。DB サーバー不要。

```
DATABASE_URL="file:./dev.db"
CORS_ORIGIN="http://localhost:3000"
```

**本番**: `backend/prisma/schema.prisma` の `provider` を `mysql` に変更し、`DATABASE_URL` を RDS の接続情報に設定。

### 3. データベースの初期化

```bash
cd backend
npx prisma db push
npx prisma db seed
```

### 4. 開発サーバーの起動

**ターミナル1（バックエンド）:**

```bash
npm run dev:backend
```

**ターミナル2（フロントエンド）:**

```bash
npm run dev
```

- フロントエンド: http://localhost:3000
- バックエンド API: http://localhost:4000

フロントエンドは `/api/*` をバックエンド (localhost:4000) にプロキシします。本番では `NEXT_PUBLIC_API_URL` に API の URL を設定してください。

## 機能

- **損益計算書**: 車両別の月次損益を表形式で表示。縦軸に勘定科目、横軸に車両の月間金額・売上比(%)
- **データインポート**: CSVで月次データを一括取込
- **エクスポート**: 損益計算書をCSVでダウンロード

## CSVインポート形式

```
車両番号,勘定科目名,金額
001-001,山崎製パン,100000
001-001,乗務員給料,50000
```

# 資格管理アプリ

資格取得に向けた学習を一元管理する Web アプリケーションです。  
資格の登録・学習記録の記録・進捗のダッシュボード可視化・カレンダー表示などを備えています。

## 技術スタック

| レイヤー | 技術 |
|---|---|
| フロントエンド | Next.js 16 / React 19 / TypeScript / Tailwind CSS 4 |
| バックエンド | Laravel 13 / PHP 8.4 |
| 認証 | Laravel Sanctum（トークンベース） |
| データベース | PostgreSQL 18 |
| インフラ | Docker Compose（Nginx / PHP-FPM / Node.js / PostgreSQL） |

## 機能一覧

### 認証
- ユーザー登録・ログイン・ログアウト
- トークンベース認証（SPA 対応）

### ダッシュボード
- 資格数・総学習時間・今週の学習時間・学習日数の表示
- 資格別の学習時間グラフ
- 直近の学習記録一覧

### 資格管理
- 資格の CRUD（名前・試験日・ステータス・メモ）
- ステータス管理（学習中 / 合格 / 不合格）
- 分野（カテゴリ）の追加・インライン編集・削除

### 勉強記録
- 学習記録の CRUD（日付・時間・内容・資格・分野）
- 資格・分野・日付によるフィルタリング
- ページネーション（20件/ページ）

### カレンダー
- 月別カレンダーで学習日をハイライト表示
- 日付クリックで当日の学習記録を表示

### ヘルプ
- アプリの使い方ガイド（7セクション）
- キーワード検索による絞り込み

## ER 図

```
users
├── certifications
│   └── categories
└── study_logs（→ certifications, → categories）
```

| テーブル | 主なカラム |
|---|---|
| users | id, name, email, password |
| certifications | id, user_id, name, exam_date, status, memo |
| categories | id, certification_id, name |
| study_logs | id, user_id, certification_id, category_id, studied_on, duration_minutes, content |

## API エンドポイント

| メソッド | パス | 説明 |
|---|---|---|
| POST | `/api/register` | ユーザー登録 |
| POST | `/api/login` | ログイン |
| POST | `/api/logout` | ログアウト |
| GET/POST | `/api/certifications` | 資格 一覧/作成 |
| GET/PUT/DELETE | `/api/certifications/{id}` | 資格 詳細/更新/削除 |
| GET/POST | `/api/certifications/{id}/categories` | 分野 一覧/作成 |
| PUT/DELETE | `/api/categories/{id}` | 分野 更新/削除 |
| GET/POST | `/api/study-logs` | 学習記録 一覧/作成 |
| GET/PUT/DELETE | `/api/study-logs/{id}` | 学習記録 詳細/更新/削除 |
| GET | `/api/dashboard` | ダッシュボード集計 |
| GET | `/api/calendar` | カレンダー（月別） |
| GET | `/api/calendar/{date}` | カレンダー（日別） |

## 環境構築

### 前提条件

- Docker Desktop
- WSL2（Windows の場合）

### セットアップ手順

1. **リポジトリをクローン**

```bash
git clone https://github.com/kurodakenya/sikaku_kanri.git
cd sikaku_kanri
```

2. **環境変数ファイルを作成**

```bash
cp backend/.env.example backend/.env
```

3. **Docker コンテナを起動**

```bash
docker compose up -d
```

4. **Laravel の初期セットアップ**

```bash
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

5. **アクセス**

ブラウザで http://localhost:8080 を開く

## ディレクトリ構成

```
sikaku_kanri/
├── backend/                # Laravel（API サーバー）
│   ├── app/
│   │   ├── Http/Controllers/
│   │   └── Models/
│   ├── database/migrations/
│   └── routes/api.php
├── frontend/               # Next.js（SPA）
│   └── src/
│       ├── app/
│       │   ├── (auth)/     # ログイン・登録
│       │   └── (dashboard)/# ダッシュボード・資格・記録・カレンダー・ヘルプ
│       ├── components/ui/  # shadcn/ui ベースのUIコンポーネント
│       └── lib/api.ts      # API クライアント
├── docker/
│   └── nginx/default.conf  # リバースプロキシ設定
├── docker-compose.yml
└── README.md
```

## アーキテクチャ

```
ブラウザ → Nginx(:8080)
             ├── /api/* → Laravel (PHP-FPM)  → PostgreSQL
             └── /*     → Next.js (Node.js)
```

Nginx がリバースプロキシとして動作し、`/api` へのリクエストを Laravel に、それ以外を Next.js にルーティングします。

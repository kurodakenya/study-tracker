# 資格勉強 進捗管理アプリ — プロジェクト設計書

## 概要

資格勉強の進捗を管理するWebアプリケーション。
自分自身が使うツールとして開発し、転職活動のポートフォリオとしても活用する。

- **目標**: 2027年3月までに完成・GitHub公開
- **AWS CLF-C02の勉強と並行して進める**

---

## 技術スタック

| カテゴリ | 技術 | 備考 |
|----------|------|------|
| フロントエンド | Next.js | Reactベース |
| バックエンド | Laravel (PHP) | API提供 |
| データベース | PostgreSQL | 業務はMySQLだが差別化のため選択 |
| インフラ | Docker | 開発環境構築 |

### 開発環境

| ツール | バージョン |
|--------|-----------|
| PHP | 8.4.27 |
| Node.js | 24.22.2 (LTS) |
| npm | 11.19.1 |
| Composer | 2.11.1 |
| Git | 2.45.0 |
| Docker Desktop | インストール済 |
| エディタ | VS Code |
| OS | Windows |

---

## 画面一覧（9画面）

| # | 画面名 | 概要 |
|---|--------|------|
| 1 | ログイン | メール + パスワード |
| 2 | ユーザー登録 | 名前 + メール + パスワード |
| 3 | ダッシュボード | 資格一覧 + 各資格の累計勉強時間 |
| 4 | 資格一覧 | 登録した資格の一覧表示 |
| 5 | 資格登録/編集 | 資格名・目標日・メモ・ステータス・分野管理 |
| 6 | 勉強記録一覧 | フィルタ：資格 / 日付範囲 / 分野 |
| 7 | 勉強記録登録/編集 | 日付・時間・内容・資格紐付け・分野選択 |
| 8 | カレンダー | 草表示 + 日付クリックでその日の記録表示 |
| 9 | ヘルプ | 1ページ構成・サイド目次・キーワード検索・取扱説明書 |

### サイドメニュー

- ダッシュボード
- 資格管理
- 勉強記録
- カレンダー
- ヘルプ

---

## 仕様詳細

### ログイン / ユーザー登録

- ログイン: メールアドレス + パスワード
- ユーザー登録: 名前 + メールアドレス + パスワード

### ダッシュボード

- 初期表示: 登録資格の一覧 + 各資格の累計勉強時間
- その他の機能はサイドメニューから遷移

### 資格管理

- 入力項目: 資格名、目標日（試験日）、メモ（任意）、ステータス
- ステータス選択肢: 勉強中 / 合格 / 不合格
- 分野管理: 資格ごとに分野を自由に追加・編集・削除
- 分野は資格登録時でも、登録後でも設定可能

### 勉強記録

- 入力項目: 日付、勉強時間、やった内容（自由入力）
- 資格との紐付け（どの資格の勉強か選択）
- 分野選択（選択した資格の分野から選ぶ）
- フィルタ: 資格 / 日付範囲 / 分野

### カレンダー

- 草表示（勉強した日に色がつく）
- 日付クリックでその日の勉強記録を表示

### ヘルプ

- 1ページ構成
- 左サイドに目次（クリックでスクロール）
- 上部にキーワード検索（該当セクションを絞り込み）
- 内容: アプリ概要 / 資格の登録方法 / 分野の設定 / 勉強記録の付け方 / ダッシュボードの見方 / カレンダーの使い方 / FAQ

---

## テーブル設計

### users（ユーザー）

| カラム名 | 型 | 制約 | NULL | 説明 |
|----------|-----|------|------|------|
| id | BIGSERIAL | PRIMARY KEY | NO | |
| name | VARCHAR(255) | | NO | 名前 |
| email_encrypted | TEXT | | NO | 暗号化メールアドレス（表示用） |
| email_hash | VARCHAR(255) | UNIQUE | NO | 検索用ハッシュ（ログイン時に使用） |
| password | VARCHAR(255) | | NO | bcryptハッシュ |
| created_at | TIMESTAMP | | NO | |
| updated_at | TIMESTAMP | | NO | |

> メールアドレスはブラインドインデックス方式で暗号化。
> email_hashで検索、email_encryptedを復号して表示。

### certifications（資格）

| カラム名 | 型 | 制約 | NULL | 説明 |
|----------|-----|------|------|------|
| id | BIGSERIAL | PRIMARY KEY | NO | |
| user_id | BIGINT | FK → users | NO | |
| name | VARCHAR(255) | | NO | 資格名 |
| exam_date | DATE | | YES | 目標日（試験日） |
| status | VARCHAR(20) | | NO | studying / passed / failed |
| memo | TEXT | | YES | メモ |
| created_at | TIMESTAMP | | NO | |
| updated_at | TIMESTAMP | | NO | |

### categories（分野）

| カラム名 | 型 | 制約 | NULL | 説明 |
|----------|-----|------|------|------|
| id | BIGSERIAL | PRIMARY KEY | NO | |
| certification_id | BIGINT | FK → certifications | NO | |
| name | VARCHAR(255) | | NO | 分野名（例：ネットワーク） |
| created_at | TIMESTAMP | | NO | |
| updated_at | TIMESTAMP | | NO | |

### study_logs（勉強記録）

| カラム名 | 型 | 制約 | NULL | 説明 |
|----------|-----|------|------|------|
| id | BIGSERIAL | PRIMARY KEY | NO | |
| user_id | BIGINT | FK → users | NO | |
| certification_id | BIGINT | FK → certifications | NO | |
| category_id | BIGINT | FK → categories | YES | 分野（任意） |
| studied_on | DATE | | NO | 勉強した日付 |
| duration_minutes | INTEGER | | NO | 勉強時間（分単位） |
| content | TEXT | | YES | やった内容 |
| created_at | TIMESTAMP | | NO | |
| updated_at | TIMESTAMP | | NO | |

---

## API設計

### 認証

| メソッド | エンドポイント | 説明 | 備考 |
|----------|---------------|------|------|
| POST | /api/register | ユーザー登録 | name, email, password |
| POST | /api/login | ログイン | email, password |
| POST | /api/logout | ログアウト | 認証トークン必要 |

### 資格

| メソッド | エンドポイント | 説明 | 備考 |
|----------|---------------|------|------|
| GET | /api/certifications | 資格一覧 | 累計勉強時間つき |
| POST | /api/certifications | 資格登録 | name, exam_date, status, memo |
| GET | /api/certifications/{id} | 資格詳細 | |
| PUT | /api/certifications/{id} | 資格編集 | |
| DELETE | /api/certifications/{id} | 資格削除 | 関連する分野・記録も削除 |

### 分野（カテゴリ）

| メソッド | エンドポイント | 説明 | 備考 |
|----------|---------------|------|------|
| GET | /api/certifications/{id}/categories | 分野一覧 | 資格に紐づく分野 |
| POST | /api/certifications/{id}/categories | 分野追加 | name |
| PUT | /api/categories/{id} | 分野編集 | name |
| DELETE | /api/categories/{id} | 分野削除 | |

### 勉強記録

| メソッド | エンドポイント | 説明 | 備考 |
|----------|---------------|------|------|
| GET | /api/study-logs | 記録一覧 | フィルタ対応（下記参照） |
| POST | /api/study-logs | 記録登録 | certification_id, category_id, studied_on, duration_minutes, content |
| GET | /api/study-logs/{id} | 記録詳細 | |
| PUT | /api/study-logs/{id} | 記録編集 | |
| DELETE | /api/study-logs/{id} | 記録削除 | |

### ダッシュボード / カレンダー

| メソッド | エンドポイント | 説明 | 備考 |
|----------|---------------|------|------|
| GET | /api/dashboard | ダッシュボード | 資格一覧 + 各累計勉強時間 |
| GET | /api/calendar | カレンダーデータ | 月ごとの勉強日データ（草表示用） |
| GET | /api/calendar/{date} | 特定日の記録 | yyyy-mm-dd形式 |

### フィルタ（GET /api/study-logs クエリパラメータ）

| パラメータ | 型 | 説明 | 例 |
|-----------|-----|------|-----|
| certification_id | INTEGER | 資格で絞り込み | ?certification_id=1 |
| category_id | INTEGER | 分野で絞り込み | ?category_id=2 |
| from | DATE | 開始日 | ?from=2027-01-01 |
| to | DATE | 終了日 | ?to=2027-01-31 |

---

## セキュリティ

- パスワード: bcryptハッシュ化（Laravel標準）
- メールアドレス: ブラインドインデックス方式で暗号化
  - `email_encrypted`: 暗号化されたメールアドレス（表示用・復号可能）
  - `email_hash`: ハッシュ値（検索用・UNIQUE制約）

---

## プロジェクト構成（予定）

```
study-tracker/
├── docker-compose.yml
├── backend/           ← Laravel（API）
│   └── Dockerfile
├── frontend/          ← Next.js
│   └── Dockerfile
└── docker/
    └── nginx/
        └── default.conf  ← Webサーバー設定
```

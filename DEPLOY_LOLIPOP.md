# ロリポップ（Lolipop）サーバーへのデプロイ手順書

本アプリケーション（ContractFlow / 携帯回線・乗り換え管理アプリ）をロリポップ！レンタルサーバーにアップロードして公開するための手順書です。

---

## 1. 事前準備（ローカル環境）

### アセットのビルド
```bash
npm run build
```
これにより `public/build/` ディレクトリ内に本番用 CSS/JS が生成されます。

### 本番用 Composer 最適化（アップロード前）
```bash
composer install --no-dev --optimize-autoloader
```

---

## 2. ロリポップ側の設定

### ① PHPバージョンの確認
- ロリポップのユーザー専用ページにログイン
- **サーバーの管理・設定** > **PHP設定** を開く
- 対象ドメインの PHP バージョンを **PHP 8.2 以上（推奨: 8.3 / 8.4）** に設定

### ② MySQL データベースの作成
- **サーバーの管理・設定** > **データベース** を開く
- 「作成」ボタンから新しいデータベースを作成
- 以下の接続情報を控えておきます：
  - **サーバー（ホスト名）**: 例 `mysql***.phy.lolipop.lan`
  - **データベース名**: 例 `LAA*******-db`
  - **ユーザー名**: 例 `LAA*******`
  - **パスワード**: 設定したパスワード

### ③ 公開フォルダ（ドキュメントルート）の設定
- **独自ドメイン設定** にて、公開（ドキュメントルート）フォルダを `public` ディレクトリに設定します。
  - 例：`/home/users/0/ユーザー名/web/mnp/public`
  - または、ルート直下に置く場合は `.htaccess` で `public` 配下にリダイレクトする構成にします。

---

## 3. ファイルのアップロード

FTP または SSH を使用して、プロジェクト全体をサーバーにアップロードします。

> [!NOTE]
> アップロードから除外するもの：
> - `node_modules/`
> - `.git/`
> - `tests/`

---

## 4. サーバー上での環境設定（.env）

サーバー上の `.env` ファイルを作成・編集します：

```dotenv
APP_NAME="ContractFlow"
APP_ENV=production
APP_KEY=base64:【ローカルのAPP_KEYまたは php artisan key:generate で生成したキー】
APP_DEBUG=false
APP_URL=https://あなたのドメイン.com

APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja

# データベース設定 (ロリポップ)
DB_CONNECTION=mysql
DB_HOST=mysql***.phy.lolipop.lan
DB_PORT=3306
DB_DATABASE=LAA*******-db
DB_USERNAME=LAA*******
DB_PASSWORD=設定したパスワード
```

---

## 5. データベースのマイグレーションと初期データ投入

SSH が利用可能なプラン（ハイスピードプラン等）の場合：
```bash
php artisan migrate --seed --force
```

SSH が利用できないプランの場合：
- ロリポップの「phpMyAdmin」にログイン
- ローカルでエクスポートした SQL、またはマイグレーション実行済みのテーブル構造・データをインポートしてください。

---

## 6. パーミッション設定

サーバー上で以下のディレクトリに書き込み権限（`777` または `755`）を付与します：
- `storage/`
- `bootstrap/cache/`

以上でロリポップ上での稼働準備が完了します。

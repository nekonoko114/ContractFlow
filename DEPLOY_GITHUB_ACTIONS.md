# GitHub Actions によるロリポップ自動デプロイ設定ガイド

このガイドに従って設定を行うことで、GitHub の `main` ブランチに `git push` するだけで、自動的にアセットビルド・ファイル転送・マイグレーション・キャッシュクリアが行われるようになります。

---

## 1. ロリポップ側の事前準備

### (1) データベース（MySQL）の作成
1. ロリポップ！ユーザー専用ページにログインします。
2. 左メニューの **「サーバーの管理・設定」 > 「データベース」** を開きます。
3. **「作成」** ボタンをクリックし、新しいデータベースを作成します。
4. 以下の情報をメモしておきます：
   - サーバー名（例: `mysql***.phy.lolipop.lan`）
   - データベース名（例: `LAA*******-db`）
   - ユーザー名（例: `LAA*******`）
   - パスワード

### (2) SSH の有効化と接続情報の確認
1. 左メニューの **「サーバーの管理・設定」 > 「SSH構造」** を開きます。
2. **「SSHを有効にする」** をクリックします。
3. 以下の接続情報を確認します：
   - **サーバー（Host）**: 例 `ssh***.lolipop.jp`
   - **ポート（Port）**: `2222`
   - **ユーザー名（User）**: 例 `ユーザー名`
   - **パスワード**: SSH用パスワード

### (3) ドメインの公開フォルダ（ドキュメントルート）設定
1. 左メニューの **「サーバーの管理・設定」 > 「独自ドメイン設定」** を開きます。
2. 対象ドメインの **公開（アップロード）フォルダ** を以下のように設定します：
   - 設定値: `contractflow/public`
   > [!IMPORTANT]
   > Laravel の公開ディレクトリは `public` フォルダです。公開フォルダを `contractflow/public` に指定することで、`.env` やシステム本体が外部から直接アクセスされるのを防ぎ、最も安全に運用できます。

---

## 2. SSH 鍵ペアの作成（GitHub Actions 用）

GitHub Actions からロリポップへ安全に接続するため、SSH 秘密鍵と公開鍵を作成します。

ローカルのターミナルで以下のコマンドを実行します：

```bash
# デプロイ専用の鍵ペアを作成（パスフレーズは空で Enter）
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy" -f ~/.ssh/lolipop_deploy_key -N ""
```

作成された2つのファイル：
- `~/.ssh/lolipop_deploy_key`（**秘密鍵**: GitHub Secrets に登録）
- `~/.ssh/lolipop_deploy_key.pub`（**公開鍵**: ロリポップサーバーに登録）

### ロリポップサーバーへの公開鍵の登録
ロリポップに SSH 接続し、公開鍵を登録します：

```bash
# ロリポップへ SSH 接続（パスワード入力）
ssh -p 2222 ユーザー名@ssh***.lolipop.jp

# サーバー上で authorized_keys を作成・設定
mkdir -p ~/.ssh
chmod 700 ~/.ssh
touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# 公開鍵の内容を authorized_keys に追記して保存
# （ローカルの lolipop_deploy_key.pub の中身を貼り付けます）
echo "あなたの公開鍵文字列" >> ~/.ssh/authorized_keys

# デプロイ先ディレクトリを作成
mkdir -p ~/web/contractflow
```

---

## 3. サーバー側への初期 `.env` ファイルの配置

ロリポップサーバー上の `~/web/contractflow/.env` を作成します：

```bash
# ロリポップのデプロイ先ディレクトリに移動
cd ~/web/contractflow

# .env ファイルを作成
vi .env
```

`.env` の内容：

```dotenv
APP_NAME="ContractFlow"
APP_ENV=production
APP_KEY=base64:【ローカルのAPP_KEYまたは生成したキー】
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

# セッション・キャッシュ
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

---

## 4. GitHub リポジトリの Secrets 登録

GitHub のリポジトリページを開き、**「Settings」 > 「Secrets and variables」 > 「Actions」** を開きます。
**「New repository secret」** から以下の5つを登録します：

| Secret名 | 設定する値 | 例 |
| :--- | :--- | :--- |
| `SSH_HOST` | ロリポップのSSHサーバー名 | `ssh***.lolipop.jp` |
| `SSH_PORT` | SSHポート番号 | `2222` |
| `SSH_USERNAME` | SSHユーザー名 | `ユーザー名` |
| `SSH_PRIVATE_KEY` | ローカルの秘密鍵（`~/.ssh/lolipop_deploy_key` の全文） | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `DEPLOY_PATH` | ロリポップ上の絶対パス | `/home/users/0/xxx/web/contractflow`（※`pwd` で確認可能） |

> [!TIP]
> ロリポップ上での絶対パスは、SSHログイン後に `cd ~/web/contractflow && pwd` を実行すると取得できます。

---

## 5. 自動デプロイの実行

設定完了後、`main` ブランチに変更を push するだけで自動デプロイが開始されます：

```bash
git push origin main
```

GitHub リポジトリの **「Actions」** タブから、デプロイの進捗とログをリアルタイムで確認できます。
デプロイが完了したら、設定したドメインにアクセスして動作を確認してください。

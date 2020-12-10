Salseforce の Canvas アプリケーションと埋め込まれる側の PHP アプリケーションの実装例です。

## ディレクトリ構成

- forcecom: SFDX プロジェクト
- php: PHP プロジェクト Heroku にデプロイ可

## プロジェクトの利用方法

### 1. PHP Heroku プロジェクトの初期化

Heroku で動作確認する場合は、前提条件として以下が必要です。

- Heroku アカウント
- PHP
- Composer
- Heroku コマンド

Heroku コマンドは以下のページを参考に入れてください

https://devcenter.heroku.com/articles/getting-started-with-php#set-up

Heroku アプリを作成

$ heroku create

Heroku の URL は、接続アプリケーションに設定するため控えておきます。

例: https://morning-woodland-99878.herokuapp.com/

プロジェクトをデプロイ

```
$ git subtree push --prefix php heroku master
```

### 2. 接続アプリケーションを設定

forcecom/force-app/main/default/connectedApps/Canvas_App.connectedApp-meta.xml を開き、
`ConnectedApp/canvasConfig/canvasUrl` 要素の値を、作成した Heroku アプリケーションの URL に置き換えます。

```
<ConnectedApp xmlns="http://soap.sforce.com/2006/04/metadata">
    <canvasConfig>
        <accessMethod>Post</accessMethod>
        <canvasUrl>作成したHEROKUのURLを設定</canvasUrl>
        <locations>Visualforce</locations>
        <samlInitiationMethod>None</samlInitiationMethod>
    </canvasConfig>
    ...
```

forcecom ディレクトリに移動して以下のスクリプトを実行して、接続アプリケーションのコンシューマキーに新しい値をセットします。
(コンシューマキーは `ConnectedApp/oauthConfig/consumerKey` 要素)

```
$ cd forcecom
$ node ./scripts/js/set_cunsumer_key_to_connected_app.js
```

※ 接続アプリケーションのコンシューマキーはグローバルで一意である必要があるため Scratch 組織に push する前に新しい値を生成してセットしておく必要があります。

### 3. SFDX プロジェクトの初期化

引き続き forcecom ディレクトリで作業します。

SFDX プロジェクトに必要な node パッケージをインストール

```
$ npm install
```

Scratch 組織の作成とソースの push

```
$ sfdx force:org:create -f ./config/project-scratch-def.json -s -d 30
$ sfdx force:source:push
```

### 4. Scratch Org を開きログイン

Scratch Org では force:org:open で起動するとセッションに問題があるらしいのでパスワードでログインする必要があります。

以下のコマンドでパスワードを生成

```
$ sfdx force:user:password:generate
```

生成したパスワードで Scratch Org にログイン
https://test.salseforce.com からログインします。

### 5. 接続アプリケーションのポリシーを修正

設定で[アプリケーションマネージャ]を開き「Canvas App」のメニューより[Manage]を選択、[ポリシーを編集]を開きます。

[OAuth ポリシー] > [許可されているユーザ]で「管理者が承認したユーザは事前承認済み」を選択して[保存]します。

<img width="540" alt="貼り付けた画像_2020_11_27_9_33" src="https://user-images.githubusercontent.com/790480/100398840-b4686b80-3093-11eb-83f6-da48a600b460.png">

※ 署名付きリクエストを送信するにはこの設定が必要です。

### 6. Heroku の環境変数にコンシューマシークレットをセット

設定で[アプリケーションマネージャ]を開き「Canvas App」のメニューより[参照]を開きます。

[API (OAuth 設定の有効化)]の[コンシューマの秘密]の値を表示してコピーします。コピーした値を、以下のコマンドで Heroku の環境変数「CANVAS_CONSUMER_SECRET」にセットします。

```
$ heroku config:set CANVAS_CONSUMER_SECRET=<コンシューマの秘密の値>
```

### 7. プロファイルに接続アプリケーション「Canvas App」を追加

システム管理者プロファイルの編集を開き、[接続アプリケーションへのアクセス]で「Canvas App」にチェックを入れて保存します。

## 動作確認

アプリケーション ランチャーで「Canvas」と入力して、「Canvas App」タブを開きます。

## TIPS

PHP 側を修正して、push --force したい場合は以下のコマンドでデプロイ

```
$ git push heroku `git subtree split --prefix php master`:master --force
```

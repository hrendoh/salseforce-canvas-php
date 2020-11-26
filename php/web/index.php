<?php

require('../vendor/autoload.php');

// Signed Requestを取得
$signedRequest = $_REQUEST['signed_request'];
// 接続アプリケーションのコンシューマ シークレットを用意
$consumer_secret = $_ENV['CANVAS_CONSUMER_SECRET'];

if ($signedRequest == null || $consumer_secret == null) {
  echo "Error: Signed Request or Consumer Secret not found";
}

//decode the signedRequest
$sep = strpos($signedRequest, '.');
// 署名を取り出す
$encodedSig = substr($signedRequest, 0, $sep);
// Base64エンコードされたリクエストパラメータを取り出す
$encodedEnv = substr($signedRequest, $sep + 1);
// 署名検証用の文字列を生成
$calcedSig = base64_encode(hash_hmac("sha256", $encodedEnv, $consumer_secret, true));
if ($calcedSig != $encodedSig) {
  echo "Error: Signed Request Failed.  Is the app in Canvas?";
}

// リクエストパラメータをデコード
$sr = base64_decode($encodedEnv);
$canvas_request = json_decode($sr);

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__ . '/views',
));

// Our web handlers

$app->get('/', function () use ($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->run();

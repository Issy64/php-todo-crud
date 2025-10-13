<?php

declare(strict_types=1);

/*
----------------------------------------
  ・セッション開始（フラッシュメッセージ等で後々使う）
  ・依存ファイルを読み込み、PDOを初期化
  ・タイトルの上限を設定
----------------------------------------
*/
session_start();

require_once __DIR__ . '/../app/db.php'; //getPDO() を定義しているファイル
require_once __DIR__ . '/../app/helper.php';
require_once __DIR__ . '/../app/actions/list.php';
require_once __DIR__ . '/../app/actions/create.php';
require_once __DIR__ . '/../app/actions/update.php';
require_once __DIR__ . '/../app/actions/delete.php';
require_once __DIR__ . '/../app/actions/confirm.php';
$pdo = getPDO();

const TITLE_MAX = 120;

/*
----------------------------------------
ルーティング
  （目的）
  ・クエリ ?action=list を受け取り、デフォルトは list
  ・「Webリクエスト → アクション関数」の入口
  ・予期せぬエラーはステータス500で返す
  ・詳細は開発中はecho、完成後はログに
----------------------------------------
*/
$action = $_GET['action'] ?? 'list';
$action = trim($action);
$allowList = ['list', 'create', 'update', 'delete', 'delete_confirm'];

if ($action === '' || !in_array($action, $allowList)) {
  http_response_404();
  exit;
}

try {
  switch ($action) {
    case 'list':
      handle_list($pdo);
      break;

    case 'create':
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_405();
        break;
      }
      handle_create($pdo);
      break;

    case 'update':
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_405();
        break;
      }
      handle_update($pdo);
      break;

    case 'delete':
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_405();
        break;
      }
      handle_delete($pdo);
      break;

    case 'delete_confirm':
      handle_confirm($pdo);
      break;

    default:
      http_response_404();
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo 'Internal Server Error';
  // 開発用（必要ならコメントアウト）
  echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
  exit;
}

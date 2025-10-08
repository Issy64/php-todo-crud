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

require_once __DIR__ . '/../app/db.php';//getPDO() を定義しているファイル
require_once __DIR__ . '/../app/csrf.php';
require_once __DIR__ . '/../app//actions/list.php';
require_once __DIR__ . '/../app//actions/create.php';
require_once __DIR__ . '/../app//actions/update.php';
require_once __DIR__ . '/../app//actions/delete.php';
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
$allowList = ['list', 'create', 'update', 'delete'];

if ($action === '' || !in_array($action, $allowList)) {
  http_response_code(404);
  echo 'Not Found';
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

    default:
      http_response_code(404);
      echo 'Not Found';
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo 'Internal Server Error';
  // 開発用（必要ならコメントアウト）
  echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
  exit;
}


/*
----------------------------------------
flashヘルパ
  （目的）
  ・flashとして表示させたい機能をヘルパ化
----------------------------------------
*/
function flash(string $key): ?string
{
  if (!isset($_SESSION['flash'][$key]))
    return null;
  $msg = $_SESSION['flash'][$key];
  unset($_SESSION['flash'][$key]);
  return $msg;
}

function http_response_405(): void
{
  http_response_code(405);
  header("Allow: POST");
  echo 'Method Not Allowed';
}
<?php

declare(strict_types=1);

/*
----------------------------------------
ブートストラップ
  （目的）
  ・セッション開始（フラッシュメッセージ等で後々使う）
  ・依存ファイルを読み込み、PDOを初期化
----------------------------------------
*/
session_start();

require_once(__DIR__ . '/../app/db.php');//getPDO() を定義しているファイル
$pdo = getPDO();

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

try {
  switch ($action) {
    case 'list':
      handle_list($pdo);
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
アクション関数: list
  （目的）
  ・DBから最新順でTodo一覧を取得し、HTMLをレンダリング
  ・「Webリクエストの流れ」: GET → ルータ（switch）→ handle_list() → SQL → 画面
----------------------------------------
*/
function handle_list(PDO $pdo): void
{
  $sql = <<<SQL
select id, title, is_done, created_at, updated_at
from todos
order by id asc
limit 100
SQL;

  $stmt = $pdo->query($sql);
  $todos = $stmt->fetchAll();

  render_list_view($todos);
}

/*
----------------------------------------
レンダラ
  （目的）
  ・表示時のXSS対策(htmlspecialchars)を集中させる
  ・最小のマークアップのみ(後にテンプレートファイルに分離)
----------------------------------------
*/
function render_list_view(array $todos): void
{
  $h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

  ?>

  <!-- ここからHTML記述 -->
  <!DOCTYPE html>
  <html lang="ja">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo一覧</title>
    <link rel="stylesheet" href="style.css">
  </head>

  <body>
    <h1>ToDo一覧</h1>

    <p class="muted">
      これは「R(read)」の最小実装です。GETアクセスでDBを読み出して表示しています。
    </p>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Status</th>
          <th>Created</th>
          <th>Updated</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todos as $t): ?>
          <tr>
            <td><?php echo $h($t['id']); ?></td>
            <td><?php echo $h($t['title']); ?></td>
            <td>
              <span class="badge">
                <?php echo ((int) $t['is_done'] == 1) ? 'Done' : 'Open'; ?>
              </span>
            </td>
            <td class="muted"><?php echo $h($t['created_at']); ?></td>
            <td class="muted"><?php echo $h($t['updated_at']); ?></td>
          </tr>
        <?php endforeach; ?>

        <?php if (count($todos) == 0): ?>
          <tr>
            <td colspan="5">データがありません</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </body>

  </html>

  <?php
}













// // 許可するリスト
// $allowed = ['list','create','store','edit','update','destroy'];

// // $_GETが存在、かつnullではない→そのまま、違うなら'list'
// $action = $_GET['action'] ?? 'list';
// $action = trim($action);
// if ($action === '' || !in_array($action, $allowed, true)) {
//   $action = 'list'; // または 404 的なハンドリング
// }

// switch ($action) {
//   case 'list':    /* TODO: SELECT全件 → views/list.php */ break;
//   case 'create':  /* TODO: 空データで views/form.php    */ break;
//   case 'store':   /* TODO: CSRF/validate→INSERT→PRG      */ break;
//   case 'edit':    /* TODO: idを検証→1件取得→form表示     */ break;
//   case 'update':  /* TODO: CSRF/validate→UPDATE→PRG       */ break;
//   case 'destroy': /* TODO: CSRF/存在確認→DELETE→PRG       */ break;
//   default:        /* TODO: flash.error→list               */ break;
// }


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
function http_response_405(): void
{
  http_response_code(405);
  header("Allow: POST");
  echo 'Method Not Allowed';
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
アクション関数: create
  （目的）
  ・POSTされた文字列をインサートする
  ・CSRFトークン検証→文字列受取→インサート
  ・インサート時にプリペアドステートメントを利用し、安全にSQLクエリに落とし込む
----------------------------------------
*/
function handle_create(PDO $pdo): void
{
  $token = $_POST['csrf_token'] ?? '';
  if (!verifyToken($token)) {
    http_response_code(400);
    echo 'Bad Request (CSRF token invalid)';
    return;
  }

  $title = trim((string) ($_POST['title'] ?? ''));

  if ($title === '' || mb_strlen($title, 'UTF-8') > TITLE_MAX) {
    $_SESSION['flash']['error'] = 'Title is required and up to' . TITLE_MAX . 'chars';
    header('Location: /?action=list', true, 303);
    return;
  }

  $sql = <<<SQL
INSERT INTO todos(title)
VALUES(:title)
SQL;
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':title' => $title]);

  $_SESSION['flash']['success'] = 'Todo created!';
  header('Location: /?action=list', true, 303);
}

function handle_update(PDO $pdo): void
{
  // CSRF
  $token = $_POST['csrf_token'] ?? '';
  if (!verifyToken($token)) {
    http_response_code(400);
    echo 'Bad Request (CSRF token invalid)';
    return;
  }

  // ID検証
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  if (!$id) {
    $_SESSION['flash']['error'] = 'Invalid ID';
    header('Location: /?action=list', true, 303);
    exit;
  }

  // 存在確認
  $stmt = $pdo->prepare('SELECT id FROM todos WHERE id = :id');
  $stmt->execute([':id' => $id]);
  if (!$stmt->fetch()) {
    $_SESSION['flash']['error'] = 'Todo not found';
    header('Location: /?action=list', true, 303);
    exit;
  }

  // トグル更新（updated_atも更新）
  $sql = <<<SQL
UPDATE todos
SET is_done = CASE WHEN is_done = 1 THEN 0 ELSE 1 END,
updated_at = datetime('now','localtime')
WHERE id = :id
SQL;

  $u = $pdo->prepare($sql);
  $u->execute([':id' => $id]);

  if ($u->rowCount() === 1) {
    $_SESSION['flash']['success'] = 'Todo updated!';
  } elseif($u->rowCount() === 0 ) {
    $_SESSION['flash']['error'] = 'Update failed';
  }

  header('Location: /?action=list', true, 303);
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
  $success = flash('success');
  $error = flash('error');
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
    <?php if ($success): ?>
      <p class="flash success" aria-live="polite"><?php echo $h($success); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
      <p class="flash error" aria-live="assertive"><?php echo $h($error); ?></p>
    <?php endif; ?>

    <h1>ToDo一覧</h1>

    <form method="post" action="/?action=create">
      <input type="hidden" name="csrf_token" value="<?php echo $h(generateToken()); ?>">
      <label for="title">New Todo</label>
      <input id="title" name="title" type="text" required maxlength="<?php echo TITLE_MAX; ?>" inputmode="text"
        autocomplete="off" aria-describedby="title-help">
      <small id="title-help">1〜<?php echo TITLE_MAX; ?>文字。空白のみは不可。</small>
      <button type="submit">Add</button>
    </form>

    <hr>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Status</th>
          <th>Created</th>
          <th>Updated</th>
          <th>Checked</th>
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
            <td>
              <form method="post" action="/?action=update" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= $h(generateToken()) ?>">
                <input type="hidden" name="id" value="<?= $h($t['id']) ?>">
                <button type="submit" class="btn">
                  <?= ((int) $t['is_done'] === 1) ? 'ReOpen' : 'Mark as Done' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (count($todos) == 0): ?>
          <tr>
            <td colspan="6">データがありません</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>




  </body>

  </html>

  <?php
}
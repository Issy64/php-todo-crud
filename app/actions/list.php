<?PHP
declare(strict_types=1);
require_once __DIR__ ."/../../views/list.php";
// ----------------------------------------
// アクション関数: list
// （目的）
// ・DBから最新順でTodo一覧を取得し、HTMLをレンダリング
// ・「Webリクエストの流れ」: GET → ルータ（switch）→ handle_list() → SQL → 画面
// ----------------------------------------

function handle_list(PDO $pdo): void
{
    $sql = <<<SQL
select id, title, is_done, created_at, updated_at
from todos
order by id desc
limit 100
SQL;

    $stmt = $pdo->query($sql);
    $todos = $stmt->fetchAll();

    render_list_view($todos);
}
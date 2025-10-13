<?php
declare(strict_types=1);
require_once __DIR__ . '/../csrf.php';
/*
----------------------------------------
アクション関数: update
（目的）
・ToDoのStatus[Open/Done]を更新する。
・CSRFトークン検証→文字列受取→インサート
・インサート時にプリペアドステートメントを利用し、安全にSQLクエリに落とし込む
----------------------------------------
*/
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
    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT,
        ['min_range' => 1]
    );
    if ($id === false || $id === null) {
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
    } else{
        $_SESSION['flash']['error'] = 'Update failed';
    }

    header('Location: /?action=list', true, 303);
}
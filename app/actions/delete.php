<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ ."/../../public/index.php";
require_once __DIR__ ."/../../views/list.php";
/*
----------------------------------------
アクション関数: delete
（目的）
・ToDoの列を削除する
・
・
----------------------------------------
*/
function handle_delete(PDO $pdo): void
{
    //csrf_token検証
    $token = $_POST['csrf_token'] ?? '';
    if (verifyToken($token) === false) {
        $_SESSION['flash']['error'] = 'Bad Request(CSRF_invalid)';
        header('Location:/?action=list', true, 303);
        exit;
    }
    //id検証
    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT,
        ['min_range' => 1]
    );

    $sql = <<<SQL
delete from todos
where id = :id
SQL;

    $d = $pdo->prepare($sql);
    $d->bindValue('id', $id, PDO::PARAM_INT);
    $d->execute();

    if ($d->rowCount() === 1) {
        $_SESSION['flash']['success'] = 'Todo deleted!';
    } else {
        $_SESSION['flash']['error'] = 'Delete failed';
    }

    header('Location:/?action=list', true, 303);
}


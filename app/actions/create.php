<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/csrf.php';
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
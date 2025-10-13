<?php

declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../csrf.php';
require_once __DIR__ . '/../../app/helper.php';

function handle_confirm(): void
{
    $pdo = getPDO();

    $id = $_POST['id'];

    $stmt = $pdo->prepare('SELECT title FROM todos WHERE id=:id');
    $stmt->execute([':id' => $id]);
    $title = $stmt->fetchColumn(0);

    $success = flash('success');
    $error = flash('error');
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>確認</title>
        <style>
            * {
                box-sizing: border-box;
                text-align: center;
            }

            form {
                display: inline-block;
                margin: 0 10px;
            }
        </style>
    </head>

    <body>
        <h1>このTodoを削除しても良いですか？</h1>
        <p>Title：<strong><?php echo html_helper($title) ?></strong></p>
        <form method="post" action="/?action=delete">
            <input type="hidden" name="csrf_token" value="<?php echo html_helper(generateToken()) ?>">
            <input type="hidden" name="id" value="<?php echo html_helper($id) ?>">
            <button type="submit">はい</button>
        </form>
        <form method="post" action="/?action=list">
            <button type="submit">いいえ</button>
        </form>

    </body>

    </html>

<?php
}

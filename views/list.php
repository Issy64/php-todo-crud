<?php

declare(strict_types=1);
require_once __DIR__ . '/../app/csrf.php';
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
                autocomplete="off" aria-describedby="title-help" value="<?php echo getSticky() ?>">
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
                            <form method="post" action="/?action=delete" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= $h(generateToken()) ?>">
                                <input type="hidden" name="id" value="<?= $h($t['id']) ?>">
                                <button type="submit" class="btn">
                                    delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($todos) == 0): ?>
                    <tr>
                        <td colspan="6" id="todo-none">データがありません</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </body>

    </html>
<?php
}

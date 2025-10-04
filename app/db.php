<?php
/*
--------------------------------------------------
PDO(PHP Data Objects)
PDOオブジェクトを返すgetPDOを作る
PDOオブジェクトのインスタンスを生成するためにDSNを成形する
エラーモードを例外(エラー出力)に設定する。
フェッチの結果を連想配列に設定する
--------------------------------------------------
*/

function getPDO(): PDO {
    //カレントディレクトリの(__DIR__/)１つ上層の(../)ストレージディレクトリの(storage/)ファイル(database.sqlite)
    $path = __DIR__ . '/../storage/database.sqlite';
    $dsn  = 'sqlite:' . $path;   // ← これが DSN（Data Source Name）
    $pdo  = new PDO($dsn);

    // エラーモードやフェッチ設定
    //setAttribute($属性名, $値)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//エラーモードを例外とする
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);//フェッチの戻り(結果)を連想配列に設定
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $pdo;
}
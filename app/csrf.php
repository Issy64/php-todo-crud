<?php
/*
---------------------------------------------------
サーバーがフォーム生成時にランダムなトークンを作る。
それをユーザーのセッションに保存する。
同じ値を <input type="hidden"> に埋めてフォームに出す。
POSTされたとき、セッションに保存してある値と一致するか確認。
一致すれば“本人”、不一致なら“偽装リクエスト”として拒否。
暗号論的に安全な乱数：CSPRNG（Cryptographically Secure Pseudo Random Number Generator）
---------------------------------------------------
*/

// トークンを作る関数
function generateToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// トークンを検証する関数
function verifyToken(string $token): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    // 時間的攻撃を避けるため、ハッシュ比較を使う
    return hash_equals($_SESSION['csrf_token'], $token);
}
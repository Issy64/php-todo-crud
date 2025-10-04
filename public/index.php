<?php

session_start();

// 許可するリスト
$allowed = ['list','create','store','edit','update','destroy'];

// $_GETが存在、かつnullではない→そのまま、違うなら'list'
$action = $_GET['action'] ?? 'list';
$action = trim($action);
if ($action === '' || !in_array($action, $allowed, true)) {
  $action = 'list'; // または 404 的なハンドリング
}

switch ($action) {
  case 'list':    /* TODO: SELECT全件 → views/list.php */ break;
  case 'create':  /* TODO: 空データで views/form.php    */ break;
  case 'store':   /* TODO: CSRF/validate→INSERT→PRG      */ break;
  case 'edit':    /* TODO: idを検証→1件取得→form表示     */ break;
  case 'update':  /* TODO: CSRF/validate→UPDATE→PRG       */ break;
  case 'destroy': /* TODO: CSRF/存在確認→DELETE→PRG       */ break;
  default:        /* TODO: flash.error→list               */ break;
}


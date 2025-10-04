# 1) 目的（Why / What / How）
- Why（狙い）：<br>
フレームワーク無しでも Web アプリの全体像（入口→分岐→永続化→表示）を掴み、後のLaravel学習の土台を作る。<br>
Webアプリを作るにあたり、何がわからないのか分からないので、ChatGPTをフル活用して一度作ってみる。
- What（成果物）：<br>
最小ToDo（tasks：id, title, created_at）のCRUDが動くミニアプリ。
- How（方針）：<br>
フロントコントローラ1枚（public/index.php）でアクション分岐<br>
＋共通化（app/db.php,views/layout.php）<br>
＋安全のタネ（CSRF/簡易バリデーション）。

# 2) スコープ / 非スコープ（Scope / Out of scope）
- 含める（Do）<br>
　CRUD：一覧・新規・編集・削除<br>
　PRG（Post→Redirect→Get）<br>
　CSRFトークン（フォームごと）<br>
　例外時の人間向けエラー表示（フラッシュ）<br>
　最小スタイル（読みやすい余白のみ）<br>
- 含めない（Do not）<br>
　認証・ユーザー管理<br>
　画像アップロード、複数テーブル、外部ライブラリ<br>
　高度なバリデーション、Ajax、SPA化<br>
　Docker化・CI/CD（今回は学習外）<br>

# 3) Doneの定義（Definition of Done）
- update と destroy が実際に動作し、一覧へPRGで戻る
- 全SQLがプリペアド（直書き結合なし）
- CSRF検証で不一致時は更新を拒否し、エラーをフラッシュ表示
- 例外時は白画面にならず、「わかるメッセージ」で案内
- READMEのQuickstartで2分起動できる（種データ入り）
- スクショ1枚がREADMEに掲載されている

# 4) ルーティング設計（入口→分岐→処理→出力）
- GET  /?action=list          -> 一覧表示（SELECT全件） -> views/list.php<br>
- GET  /?action=create        -> 新規フォーム         -> views/form.php<br>
- POST /?action=store         -> CSRF/validate/INSERT -> PRGで list<br>
- GET  /?action=edit&id=:id   -> 1件取得→フォーム     -> views/form.php<br>
- POST /?action=update&id=:id -> CSRF/validate/UPDATE -> PRGで list<br>
- POST /?action=destroy&id=:id-> CSRF/DELETE          -> PRGで list<br>

データ取得/更新は index.php で実行し、viewsは表示専用。<br>
セッションは index.php の最上部で開始。

# 5) データモデル（SQLite）
- テーブル：tasks(id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, created_at TEXT NOT NULL)
- 制約：title 必須・最大100文字（アプリ側で検証）
- シード：('Write README'), ('Implement update'), ('Add CSRF')

# 6) 受け入れテスト（Acceptance）
- 空文字""は保存されず、エラーが表示される
- 100文字はOK、101文字はNG（境界値）
- 存在しないidの edit/update/destroy は安全に失敗し、フラッシュ表示
- POST後にF5で再送信ダイアログが出ない（PRG効いている）
- CSRFトークンを改ざんすると更新されない
- 一覧→編集→保存→一覧の往復時間10秒以内（操作導線が短い）

# 7) 成果の見せ方（採用視点）
- README冒頭5行（何・なぜ・どうやって・技術・スクショ）
- Quickstartで3コマンド以内
- ツリー図は8行以内、責務の一言付き
- コミットメッセージは機能単位（feat: edit/update 等）

# 8) リスクと回避（短文）
1. リスク：ファイルパス違い/権限でSQLite接続に失敗<br>
回避：DBを相対パスで/app/db.phpから取得、失敗時は例外→フラッシュ
2. リスク：PRG漏れで二重INSERT<br>
回避：store/update/destroy は必ず header('Location: ...'); exit;
3. リスク：CSRF未適用フォームの混入<br>
回避：views/form.phpにトークン埋め込みの共通関数を使う
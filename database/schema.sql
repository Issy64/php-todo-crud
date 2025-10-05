/*
 ----------
 id：数値型、主キーとし、NULLなしとユニークを強制、自動採番はprimary keyの動作に任せる
 title：文字列型、nullは許容しない
 is_done：数値型、1=完了、0=未完了、デフォルトで0を格納する
 create_at、update_at：文字列型、nullは許容しない、デフォルトは現在日時。
 
 SQLiteはdate型をnumericとして扱い、datetimeの値が数値として扱えないため、
 最終的に文字列型として格納する。そのため、最初から文字列型として格納する。
 ----------
 */
CREATE TABLE IF NOT EXISTS todos(
    id integer primary key,
    title text not null check(length(title) > 0),
    is_done integer not null default 0 check(is_done in(0, 1)),
    created_at text not null default(datetime('now')),
    updated_at text not null default(datetime('now'))
);
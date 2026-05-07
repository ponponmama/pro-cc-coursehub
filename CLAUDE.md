# CourseHub

オンライン学習プラットフォーム。

## 技術スタック

- Laravel 10
- MySQL
- Blade + Tailwind CSS

## 開発環境

Docker Compose（Laravel Sail）で起動:

```bash
./vendor/bin/sail up -d
```

### Sail コマンド体系

```bash
./vendor/bin/sail artisan <command>   # Artisan コマンド
./vendor/bin/sail composer <command>  # Composer
./vendor/bin/sail npm <command>       # npm
./vendor/bin/sail exec laravel.test <command>  # コンテナ内で直接実行
```

マイグレーション:

```bash
./vendor/bin/sail artisan migrate
```

シーディング:

```bash
./vendor/bin/sail artisan db:seed
```

## コース構造

```
Course（コース）
└── Chapter（チャプター）
    └── Lesson（レッスン）
        └── Quiz（クイズ）
            ├── Question（設問）
            └── Option（選択肢）
```

受講者の進捗は `LessonProgress`、クイズの回答は `Submission` で管理。

## ユーザーロール

| ロール | 概要 | 主な権限 |
|--------|------|---------|
| admin | 管理者 | 全コース・全ユーザーの閲覧・管理 |
| coach | コーチ | 自分のコースの作成・編集・削除 |
| student | 受講生 | 公開コースの閲覧・受講・レビュー投稿（受講完了後） |

権限の詳細は `app/Policies/` を参照。

## コーディング規約

詳細は `.claude/rules/coding.md` を参照。主な方針:

- Controller ではバリデーションに **Form Request** を使用する
- 認可は **Policy** で行う（Controller に直接書かない）
- 変数名・メソッド名は **camelCase**、テーブル名・カラム名は **snake_case**
- N+1 クエリを避けるため、リレーションは `with()` / `withCount()` で Eager Loading する

## テスト

```bash
./vendor/bin/sail artisan test
```

特定のテストのみ実行:

```bash
./vendor/bin/sail artisan test --filter=<TestClassName>
```

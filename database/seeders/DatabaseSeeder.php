<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Review;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ================================================================
            // 1. Users (55 total: 1 admin, 3 coaches, 51 students)
            // ================================================================
            $admin = User::factory()->admin()->create([
                'name' => '管理者',
                'email' => 'admin@coursehub.com',
            ]);

            $coaches = collect();
            $coachNames = ['田中太郎', '佐藤花子', '鈴木一郎'];
            foreach (range(1, 3) as $i) {
                $coaches->push(User::factory()->coach()->create([
                    'name' => $coachNames[$i - 1],
                    'email' => "coach{$i}@coursehub.com",
                ]));
            }

            $students = User::factory()->count(51)->student()->create();

            // ================================================================
            // 2. Categories (5)
            // ================================================================
            $categoryData = [
                ['name' => 'Web開発',       'slug' => 'web-development'],
                ['name' => 'モバイル',       'slug' => 'mobile'],
                ['name' => 'データベース',    'slug' => 'database'],
                ['name' => 'インフラ',       'slug' => 'infrastructure'],
                ['name' => 'AI/ML',          'slug' => 'ai-ml'],
            ];
            $categories = collect();
            foreach ($categoryData as $data) {
                $categories->push(Category::create($data));
            }

            // ================================================================
            // 3. Tags (15)
            // ================================================================
            $tagNames = ['PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Docker', 'Git', 'AWS', 'MySQL', 'Python', 'TypeScript', 'Node.js', 'CSS', 'HTML', 'Linux'];
            $tags = collect();
            foreach ($tagNames as $name) {
                $tags->push(Tag::create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]));
            }

            // ================================================================
            // 4. Courses (10 total: coach1=4, coach2=3, coach3=3)
            // ================================================================
            $courseConfigs = [
                ['coach' => 0, 'title' => 'Laravel入門',          'slug' => 'laravel-intro',       'cat' => 0, 'status' => 'published', 'difficulty' => 'beginner',     'tags' => [0, 1, 8],
                 'description' => "Laravelフレームワークの基礎を学ぶ入門コースです。\n\nMVCアーキテクチャの概念から、ルーティング、コントローラー、Eloquent ORMまで、Webアプリケーション開発に必要な基本スキルを身につけます。実際にブログアプリケーションを構築しながら、Laravelの魅力を体感できます。"],
                ['coach' => 0, 'title' => 'PHPオブジェクト指向',   'slug' => 'php-oop',             'cat' => 0, 'status' => 'published', 'difficulty' => 'intermediate', 'tags' => [0],
                 'description' => "PHPにおけるオブジェクト指向プログラミングを体系的に学びます。\n\nクラスとオブジェクトの基本から、継承、インターフェース、トレイト、デザインパターンまで、保守性の高いコードを書くための設計手法を習得します。"],
                ['coach' => 0, 'title' => 'Laravel API開発',      'slug' => 'laravel-api',         'cat' => 0, 'status' => 'draft',     'difficulty' => 'advanced',     'tags' => [0, 1],
                 'description' => "LaravelでRESTful APIを設計・実装する方法を学ぶ上級コースです。\n\n認証（Sanctum）、リソースクラス、APIバージョニング、テスト駆動開発など、本番レベルのAPI構築スキルを身につけます。"],
                ['coach' => 0, 'title' => 'Docker実践',           'slug' => 'docker-practice',     'cat' => 3, 'status' => 'published', 'difficulty' => 'intermediate', 'tags' => [5, 14],
                 'description' => "Dockerを使ったコンテナベースの開発環境構築を実践的に学びます。\n\nDockerfileの書き方、Docker Composeによるマルチコンテナ構成、ボリューム管理、ネットワーク設定など、実務で使えるDockerスキルを習得します。"],
                ['coach' => 1, 'title' => 'JavaScript基礎',      'slug' => 'javascript-basics',   'cat' => 0, 'status' => 'published', 'difficulty' => 'beginner',     'tags' => [2, 13, 12],
                 'description' => "JavaScriptプログラミングの基礎を一から学ぶコースです。\n\n変数、関数、配列、オブジェクトなどの基本文法から、DOM操作、イベント処理、非同期処理まで、フロントエンド開発の土台を築きます。"],
                ['coach' => 1, 'title' => 'React入門',            'slug' => 'react-intro',         'cat' => 0, 'status' => 'published', 'difficulty' => 'intermediate', 'tags' => [2, 3],
                 'description' => "Reactを使ったモダンなフロントエンド開発を学びます。\n\nコンポーネント設計、JSX、State管理、Hooks、React Routerなど、SPA（シングルページアプリケーション）を構築するための知識を身につけます。"],
                ['coach' => 1, 'title' => 'TypeScript実践',       'slug' => 'typescript-practice', 'cat' => 0, 'status' => 'archived',  'difficulty' => 'advanced',     'tags' => [2, 10],
                 'description' => "TypeScriptの型システムを活用した堅牢なアプリケーション開発を学びます。\n\nジェネリクス、ユーティリティ型、型ガードなど、TypeScriptの高度な機能を実践的に習得します。"],
                ['coach' => 2, 'title' => 'MySQL基礎',            'slug' => 'mysql-basics',        'cat' => 2, 'status' => 'published', 'difficulty' => 'beginner',     'tags' => [8],
                 'description' => "MySQLデータベースの基礎を学ぶコースです。\n\nテーブル設計、SQL文の書き方、インデックス、正規化など、データベース管理の基本スキルを身につけます。"],
                ['coach' => 2, 'title' => 'AWS入門',              'slug' => 'aws-intro',           'cat' => 3, 'status' => 'published', 'difficulty' => 'intermediate', 'tags' => [7, 14],
                 'description' => "AWSクラウドサービスの基礎を学ぶ入門コースです。\n\nEC2、S3、RDS、VPCなど、主要サービスの使い方と設計のベストプラクティスを学びます。"],
                ['coach' => 2, 'title' => 'Python機械学習',       'slug' => 'python-ml',           'cat' => 4, 'status' => 'published', 'difficulty' => 'advanced',     'tags' => [9],
                 'description' => "Pythonを使った機械学習の基礎から応用までを学ぶコースです。\n\nNumPy、Pandas、scikit-learnを活用し、データ前処理、モデル構築、評価手法を実践的に習得します。"],
            ];

            $courses = collect();
            foreach ($courseConfigs as $config) {
                $course = Course::create([
                    'user_id' => $coaches[$config['coach']]->id,
                    'category_id' => $categories[$config['cat']]->id,
                    'title' => $config['title'],
                    'slug' => $config['slug'],
                    'description' => $config['description'],
                    'difficulty' => $config['difficulty'],
                    'status' => $config['status'],
                    'published_at' => $config['status'] === 'published' ? now()->subMonths(rand(1, 6)) : null,
                ]);
                $course->tags()->attach(collect($config['tags'])->map(fn ($i) => $tags[$i]->id));
                $courses->push($course);
            }

            // ================================================================
            // 5. Chapters (~30) & Lessons (~80)
            // ================================================================
            $chapterTemplates = [
                // Laravel入門
                0 => [
                    ['title' => '環境構築と基本概念', 'lessons' => [
                        ['title' => 'Laravelとは', 'body' => "Laravelは、PHPで書かれたWebアプリケーションフレームワークです。\n\nエレガントな構文と豊富な機能を提供し、開発者が効率的にWebアプリケーションを構築できるようサポートします。\n\nこのレッスンでは、Laravelの歴史、特徴、そしてなぜ多くの開発者に選ばれているのかを学びます。\n\nMVCパターンを採用しており、モデル（データベースとのやり取り）、ビュー（ユーザーに表示する画面）、コントローラー（ビジネスロジック）を分離して管理できます。"],
                        ['title' => '開発環境のセットアップ', 'body' => "Laravel開発に必要な環境を構築します。\n\nComposer（PHPのパッケージ管理ツール）のインストールから、Laravel Sailを使ったDocker環境の構築まで、ステップバイステップで進めます。\n\n開発に必要なツール:\n- PHP 8.1以上\n- Composer\n- Docker Desktop\n- Git\n\nこれらのツールをインストールし、`laravel new`コマンドで新しいプロジェクトを作成する方法を学びます。"],
                        ['title' => 'ディレクトリ構造の理解', 'body' => "Laravelプロジェクトのディレクトリ構造を理解しましょう。\n\n主要なディレクトリ:\n- app/ - アプリケーションのコアコード\n- config/ - 設定ファイル\n- database/ - マイグレーション、シーダー\n- resources/ - ビュー、CSS、JavaScript\n- routes/ - ルート定義\n- tests/ - テストコード\n\nそれぞれのディレクトリの役割と、ファイルの配置規則を理解することが、Laravel開発の第一歩です。"],
                    ]],
                    ['title' => 'ルーティングとコントローラー', 'lessons' => [
                        ['title' => 'ルーティングの基本', 'body' => "LaravelのルーティングはURLとアプリケーションの処理を結びつける仕組みです。\n\nroutes/web.phpファイルでルートを定義します。\n\n```php\nRoute::get('/hello', function () {\n    return 'Hello, World!';\n});\n```\n\nHTTPメソッド（GET、POST、PUT、DELETE）に対応したルート定義方法を学びます。"],
                        ['title' => 'コントローラーの作成', 'body' => "コントローラーはアプリケーションのビジネスロジックを管理するクラスです。\n\n`php artisan make:controller`コマンドでコントローラーを作成し、リクエストの処理とレスポンスの返却を実装します。\n\nリソースコントローラーを使えば、CRUD操作に必要な7つのメソッドを自動生成できます。"],
                    ]],
                    ['title' => 'Eloquent ORM', 'lessons' => [
                        ['title' => 'モデルとマイグレーション', 'body' => "Eloquent ORMはLaravelのデータベース抽象化レイヤーです。\n\nモデルクラスを通じて、データベーステーブルとのやり取りをオブジェクト指向的に行えます。\n\nマイグレーションを使ってデータベーススキーマをバージョン管理する方法も学びます。"],
                        ['title' => 'リレーションシップ', 'body' => "Eloquentのリレーションシップ機能を学びます。\n\n- hasOne / belongsTo（1対1）\n- hasMany / belongsTo（1対多）\n- belongsToMany（多対多）\n\nこれらのリレーションを定義し、関連データを効率的に取得する方法を理解します。"],
                        ['title' => 'クエリビルダー', 'body' => "Eloquentのクエリビルダーを使った高度なデータ取得方法を学びます。\n\nwhere句、orderBy、ページネーション、集計関数など、実務で頻繁に使うクエリパターンを習得します。"],
                    ]],
                    ['title' => 'ビューとBlade', 'lessons' => [
                        ['title' => 'Bladeテンプレート入門', 'body' => "BladeはLaravelのテンプレートエンジンです。\n\nHTMLの中にPHPのロジックを簡潔に埋め込むことができます。\n\n@if、@foreach、@extends、@sectionなどのディレクティブを使って、動的なHTMLページを生成する方法を学びます。"],
                        ['title' => 'レイアウトとコンポーネント', 'body' => "Bladeのレイアウト継承機能とコンポーネントシステムを学びます。\n\n共通のヘッダー・フッターを持つマスターレイアウトを作成し、各ページで継承する方法や、再利用可能なUIパーツをコンポーネントとして定義する方法を習得します。"],
                        ['title' => 'フォームとバリデーション', 'body' => "HTMLフォームからのデータ送信と、サーバーサイドでのバリデーションを学びます。\n\nCSRFトークン、バリデーションルール、エラーメッセージの表示、Old入力値の復元など、フォーム処理に必要な知識を身につけます。", 'is_published' => false],
                    ]],
                ],
                // PHPオブジェクト指向
                1 => [
                    ['title' => 'オブジェクト指向の基本', 'lessons' => [
                        ['title' => 'クラスとオブジェクト', 'body' => "オブジェクト指向プログラミングの最も基本的な概念であるクラスとオブジェクトについて学びます。\n\nクラスは設計図、オブジェクトはその設計図から作られた実体です。プロパティとメソッドの定義方法、コンストラクタの使い方を理解しましょう。"],
                        ['title' => 'カプセル化とアクセス修飾子', 'body' => "データを保護し、適切なインターフェースを提供するカプセル化の概念を学びます。\n\npublic、protected、privateの各アクセス修飾子の使い分けと、getterメソッド・setterメソッドによるデータアクセスの制御方法を習得します。"],
                    ]],
                    ['title' => '継承とポリモーフィズム', 'lessons' => [
                        ['title' => '継承の仕組み', 'body' => "クラスの継承を使って、コードの再利用性を高める方法を学びます。\n\n親クラスの機能を子クラスが引き継ぎ、必要に応じてオーバーライドする仕組みを理解します。"],
                        ['title' => '抽象クラスとインターフェース', 'body' => "抽象クラスとインターフェースの違いと使い分けを学びます。\n\n抽象クラスは共通の実装を持つ基底クラスとして、インターフェースは契約（コントラクト）として機能します。"],
                        ['title' => 'トレイト', 'body' => "PHPのトレイトを使ったコードの水平再利用を学びます。\n\nクラスの単一継承の制限を補い、複数のクラスで共通の機能を共有する方法を習得します。"],
                    ]],
                    ['title' => 'デザインパターン', 'lessons' => [
                        ['title' => 'シングルトンパターン', 'body' => "クラスのインスタンスが1つだけ生成されることを保証するシングルトンパターンを学びます。\n\nデータベース接続やログ管理など、システム全体で共有するリソースの管理に適しています。"],
                        ['title' => 'ファクトリーパターン', 'body' => "オブジェクトの生成ロジックを専用のクラスに委譲するファクトリーパターンを学びます。\n\n条件に応じて異なるクラスのインスタンスを返す柔軟な設計を実現します。"],
                    ]],
                ],
                // Docker実践
                3 => [
                    ['title' => 'Dockerの基礎', 'lessons' => [
                        ['title' => 'コンテナ技術の概要', 'body' => "コンテナ技術の基本概念と、従来の仮想マシンとの違いを学びます。\n\nDockerがなぜ現代の開発で広く使われているのか、その利点と仕組みを理解します。"],
                        ['title' => 'Dockerfileの書き方', 'body' => "Dockerイメージを作成するためのDockerfileの基本的な書き方を学びます。\n\nFROM、RUN、COPY、CMD、ENTRYPOINTなどの主要な命令の使い方と、効率的なイメージの作成方法を習得します。"],
                    ]],
                    ['title' => 'Docker Compose', 'lessons' => [
                        ['title' => 'マルチコンテナ構成', 'body' => "Docker Composeを使って複数のコンテナを連携させる方法を学びます。\n\nWebサーバー、アプリケーション、データベースなど、複数のサービスを定義し、一括で管理する方法を習得します。"],
                        ['title' => 'ボリュームとネットワーク', 'body' => "Dockerのボリューム機能を使ったデータの永続化と、コンテナ間のネットワーク通信について学びます。\n\n開発環境でのホットリロードやデータベースのデータ保持など、実践的な設定方法を理解します。"],
                        ['title' => '本番環境への適用', 'body' => "開発環境で構築したDocker構成を本番環境に適用するための考慮事項を学びます。\n\nマルチステージビルド、セキュリティ対策、パフォーマンスチューニングなど、実運用で必要な知識を身につけます。"],
                    ]],
                ],
                // JavaScript基礎
                4 => [
                    ['title' => 'JavaScriptの基本文法', 'lessons' => [
                        ['title' => '変数と型', 'body' => "JavaScriptの変数宣言（let、const、var）と基本的なデータ型について学びます。\n\n文字列、数値、真偽値、null、undefinedなど、各データ型の特徴と使い分けを理解します。"],
                        ['title' => '関数', 'body' => "JavaScriptの関数定義方法を学びます。\n\n関数宣言、関数式、アロー関数の違いと、引数、戻り値、スコープの概念を理解します。コールバック関数やクロージャーについても触れます。"],
                        ['title' => '配列とオブジェクト', 'body' => "配列とオブジェクトの操作方法を学びます。\n\nmap、filter、reduceなどの配列メソッドや、オブジェクトのプロパティアクセス、分割代入、スプレッド構文などを習得します。"],
                    ]],
                    ['title' => 'DOM操作とイベント', 'lessons' => [
                        ['title' => 'DOMの基本', 'body' => "Document Object Model（DOM）を使ってHTML要素を操作する方法を学びます。\n\n要素の取得、作成、変更、削除など、JavaScriptからWebページを動的に操作する基本技術を身につけます。"],
                        ['title' => 'イベント処理', 'body' => "ユーザーの操作（クリック、入力、スクロールなど）に反応するイベント処理を学びます。\n\nイベントリスナーの登録、イベントオブジェクトの活用、イベント伝播の制御方法を理解します。"],
                    ]],
                    ['title' => '非同期処理', 'lessons' => [
                        ['title' => 'Promise', 'body' => "非同期処理を扱うためのPromiseオブジェクトについて学びます。\n\nコールバック地獄を解消し、読みやすい非同期コードを書くための方法を理解します。"],
                        ['title' => 'async/await', 'body' => "async/await構文を使った、より直感的な非同期処理の書き方を学びます。\n\nfetch APIを使ったHTTP通信やエラーハンドリングの実践的なパターンを習得します。"],
                    ]],
                ],
                // React入門
                5 => [
                    ['title' => 'Reactの基礎', 'lessons' => [
                        ['title' => 'Reactの概要とJSX', 'body' => "Reactの基本概念とJSX構文について学びます。\n\nコンポーネントベースのUI構築、仮想DOMの仕組み、JSXの書き方と制約を理解します。"],
                        ['title' => 'コンポーネントとProps', 'body' => "Reactのコンポーネント設計とPropsによるデータの受け渡しを学びます。\n\n関数コンポーネントの作成方法と、親子間のデータフローを理解します。"],
                    ]],
                    ['title' => 'State管理とHooks', 'lessons' => [
                        ['title' => 'useStateフック', 'body' => "Reactの状態管理の基本であるuseStateフックを学びます。\n\nコンポーネント内の状態の定義、更新、そしてUIへの反映の仕組みを理解します。"],
                        ['title' => 'useEffectフック', 'body' => "副作用を管理するためのuseEffectフックを学びます。\n\nデータの取得、タイマー処理、DOM操作など、レンダリング後に実行したい処理の書き方を理解します。"],
                        ['title' => 'カスタムフック', 'body' => "共通のロジックを再利用可能なカスタムフックとして切り出す方法を学びます。\n\nフックの設計原則と、チーム開発での活用方法を習得します。"],
                    ]],
                ],
                // MySQL基礎
                7 => [
                    ['title' => 'データベース設計', 'lessons' => [
                        ['title' => 'リレーショナルデータベースとは', 'body' => "リレーショナルデータベースの基本概念を学びます。\n\nテーブル、行、列の関係性と、リレーショナルモデルがなぜデータ管理に適しているかを理解します。"],
                        ['title' => 'テーブル設計と正規化', 'body' => "適切なテーブル設計の方法と正規化の概念を学びます。\n\n第1正規形から第3正規形まで、データの冗長性を排除し、整合性を保つための設計手法を習得します。"],
                    ]],
                    ['title' => 'SQL文の基礎', 'lessons' => [
                        ['title' => 'SELECT文', 'body' => "データを検索・取得するためのSELECT文の書き方を学びます。\n\nWHERE句、ORDER BY、GROUP BY、JOINなど、データ取得に必要な構文を一通り習得します。"],
                        ['title' => 'INSERT・UPDATE・DELETE', 'body' => "データの挿入、更新、削除を行うSQL文を学びます。\n\nトランザクション処理の概念と、安全なデータ操作の方法を理解します。"],
                        ['title' => 'インデックスとパフォーマンス', 'body' => "インデックスの仕組みとクエリパフォーマンスの最適化について学びます。\n\n実行計画の読み方やスロークエリの分析方法を理解し、高速なデータベース操作を実現します。"],
                    ]],
                ],
                // AWS入門
                8 => [
                    ['title' => 'AWSの基本', 'lessons' => [
                        ['title' => 'クラウドコンピューティング概要', 'body' => "クラウドコンピューティングの基本概念と、AWSが提供するサービスの全体像を学びます。\n\nIaaS、PaaS、SaaSの違いや、リージョンとアベイラビリティゾーンの概念を理解します。"],
                        ['title' => 'EC2とS3', 'body' => "AWSの代表的なサービスであるEC2（仮想サーバー）とS3（オブジェクトストレージ）の使い方を学びます。\n\nインスタンスの作成、セキュリティグループの設定、S3バケットの管理方法を習得します。"],
                    ]],
                    ['title' => 'ネットワークとセキュリティ', 'lessons' => [
                        ['title' => 'VPCの設計', 'body' => "VPC（Virtual Private Cloud）を使ったネットワーク設計を学びます。\n\nサブネット、ルートテーブル、インターネットゲートウェイの設定方法を理解し、安全なネットワーク環境を構築します。"],
                        ['title' => 'IAMとセキュリティ', 'body' => "IAM（Identity and Access Management）を使ったアクセス制御を学びます。\n\nユーザー、グループ、ロール、ポリシーの管理方法と、最小権限の原則に基づいたセキュリティ設計を習得します。"],
                    ]],
                ],
                // Python機械学習
                9 => [
                    ['title' => '機械学習の基礎', 'lessons' => [
                        ['title' => '機械学習とは', 'body' => "機械学習の基本概念と、教師あり学習・教師なし学習・強化学習の分類を学びます。\n\n回帰、分類、クラスタリングなどの主要なタスクと、それぞれの適用場面を理解します。"],
                        ['title' => 'NumPyとPandas入門', 'body' => "データ処理の基盤となるNumPyとPandasライブラリの使い方を学びます。\n\n配列操作、データフレームの作成と操作、データの読み込みと前処理の方法を習得します。"],
                    ]],
                    ['title' => 'モデル構築と評価', 'lessons' => [
                        ['title' => 'scikit-learnの使い方', 'body' => "scikit-learnライブラリを使った機械学習モデルの構築方法を学びます。\n\n線形回帰、ロジスティック回帰、決定木、ランダムフォレストなどのアルゴリズムを実装します。"],
                        ['title' => 'モデルの評価と改善', 'body' => "構築したモデルの性能を評価し、改善する方法を学びます。\n\n交差検証、混同行列、ROC曲線、ハイパーパラメータチューニングなどの手法を理解します。"],
                        ['title' => '特徴量エンジニアリング', 'body' => "モデルの精度を向上させるための特徴量エンジニアリングを学びます。\n\n欠損値の処理、カテゴリ変数のエンコーディング、特徴量選択などの実践的なテクニックを習得します。"],
                    ]],
                ],
            ];

            // Generic chapter/lesson templates for courses not explicitly defined
            $genericChapters = [
                ['title' => '基礎知識', 'lessons' => ['概要と歴史', '基本的な用語と概念', '環境構築']],
                ['title' => '実践編', 'lessons' => ['基本操作', '応用テクニック', 'ベストプラクティス']],
                ['title' => '発展編', 'lessons' => ['実践プロジェクト', 'トラブルシューティング']],
            ];

            $genericLessonBodies = [
                "このレッスンでは、基本的な概念と用語について学びます。\n\n実際のコード例を通じて、理論と実践を結びつけながら理解を深めていきましょう。\n\nまずは全体像を把握し、次のレッスンで詳細に踏み込んでいきます。",
                "前回のレッスンで学んだ内容を踏まえ、より実践的なスキルを身につけます。\n\nハンズオン形式で実際にコードを書きながら、体験的に学んでいきましょう。\n\nわからないことがあれば、コメント欄で質問してください。",
                "このレッスンでは応用的なテクニックを学びます。\n\n実務でよく遭遇するパターンと、それに対するベストプラクティスを紹介します。\n\n最後に演習問題に取り組んで、理解度を確認しましょう。",
            ];

            $allLessons = collect();
            $courseLessonsMap = [];

            foreach ($courses as $courseIndex => $course) {
                $courseLessonsMap[$course->id] = collect();

                if (isset($chapterTemplates[$courseIndex])) {
                    // Use explicit templates
                    foreach ($chapterTemplates[$courseIndex] as $chapterOrder => $chapterData) {
                        $chapter = Chapter::create([
                            'course_id' => $course->id,
                            'title' => $chapterData['title'],
                            'order' => $chapterOrder + 1,
                        ]);

                        foreach ($chapterData['lessons'] as $lessonOrder => $lessonData) {
                            $isPublished = $lessonData['is_published'] ?? true;
                            $lesson = Lesson::create([
                                'chapter_id' => $chapter->id,
                                'title' => $lessonData['title'],
                                'body' => $lessonData['body'],
                                'order' => $lessonOrder + 1,
                                'is_published' => $isPublished,
                            ]);
                            $allLessons->push($lesson);
                            $courseLessonsMap[$course->id]->push($lesson);
                        }
                    }
                } else {
                    // Use generic templates
                    $numChapters = fake()->numberBetween(2, 3);
                    for ($c = 0; $c < $numChapters; $c++) {
                        $chapterTitle = $genericChapters[$c]['title'] ?? "第" . ($c + 1) . "章";
                        $chapter = Chapter::create([
                            'course_id' => $course->id,
                            'title' => $chapterTitle,
                            'order' => $c + 1,
                        ]);

                        $lessonTitles = $genericChapters[$c]['lessons'] ?? ['基本操作', '応用操作'];
                        foreach ($lessonTitles as $l => $lessonTitle) {
                            $lesson = Lesson::create([
                                'chapter_id' => $chapter->id,
                                'title' => $lessonTitle,
                                'body' => $genericLessonBodies[$l % count($genericLessonBodies)],
                                'order' => $l + 1,
                                'is_published' => true,
                            ]);
                            $allLessons->push($lesson);
                            $courseLessonsMap[$course->id]->push($lesson);
                        }
                    }
                }
            }

            // ================================================================
            // 6. Quizzes (15), Questions (~60), Options (~240)
            // ================================================================
            $quizTemplates = [
                ['question' => 'Laravelで新しいプロジェクトを作成するコマンドはどれですか？', 'options' => ['composer create-project laravel/laravel', 'npm init laravel', 'laravel new --install', 'php install laravel'], 'correct' => 0],
                ['question' => 'Laravelのルート定義ファイルはどこにありますか？', 'options' => ['app/routes.php', 'routes/web.php', 'config/routes.php', 'public/routes.php'], 'correct' => 1],
                ['question' => 'Eloquentでレコードを全件取得するメソッドはどれですか？', 'options' => ['Model::find()', 'Model::all()', 'Model::get()', 'Model::fetch()'], 'correct' => 1],
                ['question' => 'PHPでクラスを継承するキーワードはどれですか？', 'options' => ['implements', 'extends', 'inherits', 'uses'], 'correct' => 1],
                ['question' => 'JavaScriptで定数を宣言するキーワードはどれですか？', 'options' => ['var', 'let', 'const', 'define'], 'correct' => 2],
                ['question' => 'ReactでStateを管理するためのHookはどれですか？', 'options' => ['useEffect', 'useContext', 'useState', 'useReducer'], 'correct' => 2],
                ['question' => 'SQLでデータを昇順に並べ替えるキーワードはどれですか？', 'options' => ['DESC', 'ASC', 'SORT', 'ORDER'], 'correct' => 1],
                ['question' => 'Dockerfileでベースイメージを指定する命令はどれですか？', 'options' => ['BASE', 'IMAGE', 'FROM', 'USE'], 'correct' => 2],
                ['question' => 'AWSでオブジェクトストレージを提供するサービスはどれですか？', 'options' => ['EC2', 'RDS', 'S3', 'VPC'], 'correct' => 2],
                ['question' => 'CSSでフレックスボックスを有効にするプロパティはどれですか？', 'options' => ['display: block', 'display: flex', 'display: grid', 'display: inline'], 'correct' => 1],
                ['question' => 'Gitでブランチをマージするコマンドはどれですか？', 'options' => ['git combine', 'git merge', 'git join', 'git connect'], 'correct' => 1],
                ['question' => 'PHPの配列で要素数を取得する関数はどれですか？', 'options' => ['array_length()', 'sizeof()', 'count()', 'len()'], 'correct' => 2],
                ['question' => 'MySQLでテーブルを作成するSQL文はどれですか？', 'options' => ['MAKE TABLE', 'NEW TABLE', 'CREATE TABLE', 'ADD TABLE'], 'correct' => 2],
                ['question' => 'HTTPステータスコード404の意味はどれですか？', 'options' => ['サーバーエラー', 'リダイレクト', 'ページが見つからない', '認証が必要'], 'correct' => 2],
                ['question' => 'Laravelでバリデーションエラーを表示するBlade変数はどれですか？', 'options' => ['$messages', '$errors', '$validation', '$alerts'], 'correct' => 1],
            ];

            $publishedLessons = $allLessons->filter(fn ($l) => $l->is_published);
            $lessonsForQuiz = $publishedLessons->random(min(15, $publishedLessons->count()));

            $quizCount = 0;

            foreach ($lessonsForQuiz as $lesson) {
                $quiz = Quiz::create([
                    'lesson_id' => $lesson->id,
                    'title' => $lesson->title . ' 確認テスト',
                    'passing_score' => fake()->randomElement([60, 70, 80]),
                ]);

                // Bug 3-3-3: First quiz is empty (0 questions)
                if ($quizCount === 0) {
                    $quizCount++;
                    continue;
                }

                $numQuestions = fake()->numberBetween(3, 5);
                $usedTemplates = collect($quizTemplates)->shuffle()->take($numQuestions);

                foreach ($usedTemplates->values() as $q => $template) {
                    $question = Question::create([
                        'quiz_id' => $quiz->id,
                        'body' => $template['question'],
                        'order' => $q + 1,
                    ]);

                    foreach ($template['options'] as $o => $optionText) {
                        Option::create([
                            'question_id' => $question->id,
                            'body' => $optionText,
                            'is_correct' => $o === $template['correct'],
                        ]);
                    }
                }

                $quizCount++;
            }

            // ================================================================
            // 7. Enrollments (~100)
            // ================================================================
            $publishedCourses = $courses->filter(fn ($c) => $c->status === 'published');
            $enrollments = collect();

            foreach ($students as $student) {
                $numEnrollments = fake()->numberBetween(1, 3);
                $enrolledCourses = $publishedCourses->random(min($numEnrollments, $publishedCourses->count()));

                if (!$enrolledCourses instanceof \Illuminate\Support\Collection) {
                    $enrolledCourses = collect([$enrolledCourses]);
                }

                foreach ($enrolledCourses as $course) {
                    $status = fake()->randomElement(['active', 'active', 'active', 'completed', 'cancelled']);

                    $enrollment = Enrollment::create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'status' => $status,
                        'enrolled_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
                        'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-1 month', 'now') : null,
                    ]);
                    $enrollments->push($enrollment);
                }
            }

            // Ensure active enrollments for first course (bug 3-3-1)
            $firstCourse = $courses->first();
            $firstCourseActiveEnrollments = $enrollments
                ->where('course_id', $firstCourse->id)
                ->where('status', 'active');

            if ($firstCourseActiveEnrollments->isEmpty()) {
                $someStudents = $students->random(3);
                foreach ($someStudents as $student) {
                    $existing = $enrollments->where('user_id', $student->id)->where('course_id', $firstCourse->id)->first();
                    if ($existing) continue;

                    $enrollment = Enrollment::create([
                        'user_id' => $student->id,
                        'course_id' => $firstCourse->id,
                        'status' => 'active',
                        'enrolled_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
                        'completed_at' => null,
                    ]);
                    $enrollments->push($enrollment);
                    $firstCourseActiveEnrollments->push($enrollment);
                }
            }

            // ================================================================
            // 8. LessonProgress (~500)
            // ================================================================
            $activeAndCompletedEnrollments = $enrollments->whereIn('status', ['active', 'completed']);

            foreach ($activeAndCompletedEnrollments as $enrollment) {
                $courseLessons = $courseLessonsMap[$enrollment->course_id] ?? collect();
                $publishedCourseLessons = $courseLessons->filter(fn ($l) => $l->is_published);

                if ($publishedCourseLessons->isEmpty()) continue;

                foreach ($publishedCourseLessons as $lesson) {
                    if ($enrollment->status === 'completed') {
                        $status = 'completed';
                    } else {
                        $status = fake()->randomElement(['completed', 'completed', 'in_progress', 'not_started']);
                    }

                    LessonProgress::create([
                        'user_id' => $enrollment->user_id,
                        'lesson_id' => $lesson->id,
                        'status' => $status,
                        'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-3 months', 'now') : null,
                    ]);
                }
            }

            // Bug 3-3-1: Force active students in first course to complete all published lessons
            $firstCoursePublishedLessons = ($courseLessonsMap[$firstCourse->id] ?? collect())
                ->filter(fn ($l) => $l->is_published);
            $activeInFirstCourse = $enrollments
                ->where('course_id', $firstCourse->id)
                ->where('status', 'active')
                ->take(2);

            foreach ($activeInFirstCourse as $enrollment) {
                foreach ($firstCoursePublishedLessons as $lesson) {
                    LessonProgress::updateOrCreate(
                        ['user_id' => $enrollment->user_id, 'lesson_id' => $lesson->id],
                        ['status' => 'completed', 'completed_at' => fake()->dateTimeBetween('-1 month', 'now')]
                    );
                }
            }

            // ================================================================
            // 9. Submissions (~50)
            // ================================================================
            $quizzesWithQuestions = Quiz::has('questions')->with('questions.options')->get();
            $submissionCount = 0;

            foreach ($quizzesWithQuestions as $quiz) {
                $lesson = $quiz->lesson;
                $chapter = $lesson->chapter;
                $courseId = $chapter->course_id;

                $enrolledStudentIds = $enrollments
                    ->where('course_id', $courseId)
                    ->whereIn('status', ['active', 'completed'])
                    ->pluck('user_id')
                    ->unique();

                if ($enrolledStudentIds->isEmpty()) continue;

                $numStudents = min(fake()->numberBetween(3, 6), $enrolledStudentIds->count());
                $studentsForQuiz = $enrolledStudentIds->random($numStudents);

                if (!$studentsForQuiz instanceof \Illuminate\Support\Collection) {
                    $studentsForQuiz = collect([$studentsForQuiz]);
                }

                foreach ($studentsForQuiz as $studentId) {
                    $questions = $quiz->questions;
                    $answers = [];
                    $correctCount = 0;

                    foreach ($questions as $question) {
                        $options = $question->options;
                        if ($options->isEmpty()) continue;

                        $pickCorrect = fake()->boolean(60);
                        if ($pickCorrect) {
                            $selected = $options->firstWhere('is_correct', true) ?? $options->first();
                            if ($selected->is_correct) $correctCount++;
                        } else {
                            $wrongOptions = $options->where('is_correct', false);
                            $selected = $wrongOptions->isNotEmpty() ? $wrongOptions->random() : $options->first();
                        }

                        $answers[] = [
                            'question_id' => $question->id,
                            'option_id' => $selected->id,
                        ];
                    }

                    $totalQuestions = $questions->count();
                    $score = $totalQuestions > 0 ? (int) round($correctCount / $totalQuestions * 100) : 0;

                    Submission::create([
                        'user_id' => $studentId,
                        'quiz_id' => $quiz->id,
                        'score' => $score,
                        'answers' => $answers,
                        'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
                    ]);

                    $submissionCount++;
                    if ($submissionCount >= 50) break 2;
                }
            }

            // ================================================================
            // 10. Reviews (~20)
            // ================================================================
            $completedEnrollments = $enrollments->where('status', 'completed');

            foreach ($completedEnrollments->shuffle()->take(20) as $enrollment) {
                Review::create([
                    'user_id'   => $enrollment->user_id,
                    'course_id' => $enrollment->course_id,
                    'rating'    => fake()->numberBetween(3, 5),
                    'comment'   => fake()->optional(0.7)->sentence(),
                ]);
            }

            // Bug 3-4-1: Guarantee failing submissions
            $failingQuizzes = $quizzesWithQuestions->take(3);
            foreach ($failingQuizzes as $quiz) {
                $courseId = $quiz->lesson->chapter->course_id;
                $enrolledStudentIds = $enrollments
                    ->where('course_id', $courseId)
                    ->whereIn('status', ['active', 'completed'])
                    ->pluck('user_id')
                    ->unique();

                if ($enrolledStudentIds->isEmpty()) continue;

                $studentId = $enrolledStudentIds->random();
                $existingSubmission = Submission::where('user_id', $studentId)
                    ->where('quiz_id', $quiz->id)
                    ->first();

                if ($existingSubmission) {
                    $existingSubmission->update([
                        'score' => fake()->numberBetween(10, (int) ($quiz->passing_score - 10)),
                    ]);
                } else {
                    $questions = $quiz->questions;
                    $answers = [];
                    foreach ($questions as $question) {
                        $options = $question->options;
                        if ($options->isEmpty()) continue;
                        $wrongOptions = $options->where('is_correct', false);
                        $selected = $wrongOptions->isNotEmpty() ? $wrongOptions->random() : $options->first();
                        $answers[] = ['question_id' => $question->id, 'option_id' => $selected->id];
                    }

                    Submission::create([
                        'user_id' => $studentId,
                        'quiz_id' => $quiz->id,
                        'score' => fake()->numberBetween(10, (int) ($quiz->passing_score - 10)),
                        'answers' => $answers,
                        'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
                    ]);
                }
            }
        });
    }
}

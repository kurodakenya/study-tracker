<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Certification;
use App\Models\StudyLog;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        $emailHash = hash('sha256', 'test@example.com');
        $user = User::where('email_hash', $emailHash)->first()
            ?? User::factory()->create([
                'name' => 'Test User',
                'email_encrypted' => 'test@example.com',
                'email_hash' => $emailHash,
            ]);

        // 基本情報技術者試験（2026年4月 春期を想定）
        $cert = Certification::create([
            'user_id' => $user->id,
            'name' => '基本情報技術者試験',
            'exam_date' => '2026-04-19',
            'status' => 'studying',
            'memo' => '2026年 春期試験を受験予定',
        ]);

        // 午前・午後の主要分野をカテゴリ登録
        $categoryNames = [
            'テクノロジ系（基礎理論）',
            'テクノロジ系（コンピュータシステム）',
            'テクノロジ系（技術要素）',
            'テクノロジ系（開発技術）',
            'マネジメント系',
            'ストラテジ系',
            '科目B（アルゴリズムとプログラミング）',
            '科目B（情報セキュリティ）',
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'certification_id' => $cert->id,
                'name' => $name,
            ]);
        }

        // 2026-01-05 〜 2026-03-08 の勉強記録を生成
        // 63日間 × 平均90分 = 合計5,670分
        $startDate = Carbon::parse('2026-01-05');
        $endDate = Carbon::parse('2026-03-08');
        $period = CarbonPeriod::create($startDate, $endDate);
        $totalDays = $startDate->diffInDays($endDate) + 1; // 63日

        $targetTotal = $totalDays * 90; // 5,670分

        // 日ごとの勉強時間を生成（自然なばらつきをつける）
        $dailyMinutes = $this->generateDailyMinutes($totalDays, $targetTotal);

        // 学習内容テンプレート
        $contents = $this->getStudyContents();

        $dayIndex = 0;
        foreach ($period as $date) {
            $minutes = $dailyMinutes[$dayIndex];

            if ($minutes === 0) {
                $dayIndex++;
                continue;
            }

            // 1日の勉強を1〜2セッションに分割
            $sessions = $this->splitIntoSessions($minutes);

            foreach ($sessions as $sessionMinutes) {
                // 学習フェーズに応じたカテゴリ選択
                $category = $this->pickCategory($categories, $dayIndex, $totalDays);
                $content = $this->pickContent($contents, $category->name, $dayIndex, $totalDays);

                StudyLog::create([
                    'user_id' => $user->id,
                    'certification_id' => $cert->id,
                    'category_id' => $category->id,
                    'studied_on' => $date->format('Y-m-d'),
                    'duration_minutes' => $sessionMinutes,
                    'content' => $content,
                ]);
            }

            $dayIndex++;
        }

        // 応用情報技術者試験
        $apCert = Certification::create([
            'user_id' => $user->id,
            'name' => '応用情報技術者試験',
            'exam_date' => '2027-04-18',
            'status' => 'studying',
            'memo' => '2027年 春期試験を受験予定',
        ]);

        $apCategories = [];
        foreach ([
            'テクノロジ系（基礎理論・コンピュータ）',
            'テクノロジ系（技術要素）',
            'テクノロジ系（開発技術）',
            'マネジメント系',
            'ストラテジ系',
            '午後問題（記述対策）',
        ] as $name) {
            $apCategories[] = Category::create([
                'certification_id' => $apCert->id,
                'name' => $name,
            ]);
        }

        // 11月の勉強記録（4日分 × 45分）
        $apLogs = [
            ['date' => '2026-11-08', 'category' => 0, 'content' => 'テキスト 基礎理論（離散数学・応用数学）を読み進めた'],
            ['date' => '2026-11-10', 'category' => 1, 'content' => 'テキスト ネットワーク（TCP/IP・ルーティング）を学習'],
            ['date' => '2026-11-11', 'category' => 1, 'content' => 'テキスト データベース（正規化・SQL応用）を学習'],
            ['date' => '2026-11-14', 'category' => 4, 'content' => 'テキスト 経営戦略・情報戦略の章を読み進めた'],
        ];

        foreach ($apLogs as $log) {
            StudyLog::create([
                'user_id' => $user->id,
                'certification_id' => $apCert->id,
                'category_id' => $apCategories[$log['category']]->id,
                'studied_on' => $log['date'],
                'duration_minutes' => 45,
                'content' => $log['content'],
            ]);
        }
    }

    /**
     * 自然なばらつきのある日別勉強時間を生成
     * 平日は多め、休日はばらつき、たまに休み
     */
    private function generateDailyMinutes(int $totalDays, int $targetTotal): array
    {
        $startDate = Carbon::parse('2026-01-05');
        $minutes = [];

        for ($i = 0; $i < $totalDays; $i++) {
            $date = $startDate->copy()->addDays($i);
            $isWeekend = $date->isWeekend();

            // 休みの日（約1割）
            if (mt_rand(1, 100) <= 10) {
                $minutes[] = 0;
                continue;
            }

            if ($isWeekend) {
                // 休日: 60〜180分（まとまった時間が取れる日もある）
                $minutes[] = mt_rand(4, 12) * 15;
            } else {
                // 平日: 60〜120分
                $minutes[] = mt_rand(4, 8) * 15;
            }
        }

        // 合計を目標に合わせて調整
        $currentTotal = array_sum($minutes);
        $activeDays = array_keys(array_filter($minutes, fn ($m) => $m > 0));

        if (count($activeDays) === 0) {
            $minutes[0] = $targetTotal;
            return $minutes;
        }

        $diff = $targetTotal - $currentTotal;
        $adjustPerDay = (int) round($diff / count($activeDays));

        foreach ($activeDays as $idx) {
            $minutes[$idx] = max(15, $minutes[$idx] + $adjustPerDay);
            // 15分単位に丸める
            $minutes[$idx] = (int) (round($minutes[$idx] / 15) * 15);
        }

        // 端数調整（最後のアクティブな日で吸収）
        $finalDiff = $targetTotal - array_sum($minutes);
        $lastActive = end($activeDays);
        $minutes[$lastActive] = max(15, $minutes[$lastActive] + $finalDiff);

        return $minutes;
    }

    /**
     * 1日の勉強時間を1〜2セッションに分割
     */
    private function splitIntoSessions(int $totalMinutes): array
    {
        if ($totalMinutes <= 60) {
            return [$totalMinutes];
        }

        // 60分超なら50%の確率で2セッションに分割
        if (mt_rand(0, 1) === 1) {
            $first = (int) (round($totalMinutes * mt_rand(40, 60) / 100 / 15) * 15);
            $first = max(15, min($first, $totalMinutes - 15));
            return [$first, $totalMinutes - $first];
        }

        return [$totalMinutes];
    }

    /**
     * 学習フェーズに応じてカテゴリを選択
     * 序盤: 基礎理論、コンピュータシステム中心
     * 中盤: 技術要素、開発技術、マネジメント、ストラテジ
     * 終盤: 科目B（アルゴリズム、セキュリティ）中心
     */
    private function pickCategory(array $categories, int $dayIndex, int $totalDays): Category
    {
        $progress = $dayIndex / $totalDays;

        if ($progress < 0.35) {
            // 序盤: 午前の基礎分野中心
            $weights = [30, 25, 20, 10, 5, 5, 3, 2];
        } elseif ($progress < 0.7) {
            // 中盤: 幅広く + 科目Bも開始
            $weights = [8, 8, 20, 18, 12, 12, 14, 8];
        } else {
            // 終盤: 科目B重点 + 弱点補強
            $weights = [5, 5, 10, 8, 7, 7, 38, 20];
        }

        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $i => $w) {
            $cumulative += $w;
            if ($rand <= $cumulative) {
                return $categories[$i];
            }
        }

        return $categories[0];
    }

    /**
     * カテゴリと学習進捗に応じた学習内容テキスト
     */
    private function pickContent(array $contents, string $categoryName, int $dayIndex, int $totalDays): string
    {
        $progress = $dayIndex / $totalDays;

        $key = match (true) {
            str_contains($categoryName, '基礎理論') => 'basic',
            str_contains($categoryName, 'コンピュータシステム') => 'computer',
            str_contains($categoryName, '技術要素') => 'tech',
            str_contains($categoryName, '開発技術') => 'dev',
            str_contains($categoryName, 'マネジメント') => 'management',
            str_contains($categoryName, 'ストラテジ') => 'strategy',
            str_contains($categoryName, 'アルゴリズム') => 'algorithm',
            str_contains($categoryName, 'セキュリティ') => 'security',
            default => 'basic',
        };

        $pool = $contents[$key];

        if ($progress < 0.35) {
            // 序盤はテキスト学習系
            $filtered = array_filter($pool, fn ($c) => str_contains($c, 'テキスト') || str_contains($c, '学習') || str_contains($c, '読み'));
        } elseif ($progress < 0.7) {
            // 中盤は問題演習系
            $filtered = array_filter($pool, fn ($c) => str_contains($c, '問題') || str_contains($c, '演習') || str_contains($c, '過去問'));
        } else {
            // 終盤は模擬・復習系
            $filtered = array_filter($pool, fn ($c) => str_contains($c, '模擬') || str_contains($c, '復習') || str_contains($c, '総復習') || str_contains($c, '過去問'));
        }

        $filtered = array_values($filtered);
        if (empty($filtered)) {
            $filtered = $pool;
        }

        return $filtered[array_rand($filtered)];
    }

    private function getStudyContents(): array
    {
        return [
            'basic' => [
                'テキスト第1章 基数変換・論理演算を読み進めた',
                '2進数・16進数の変換練習',
                'テキスト 離散数学（集合・論理）を学習',
                '確率・統計の基礎をテキストで学習',
                '情報理論（エントロピー、符号化）の学習',
                '過去問 基礎理論分野を20問解いた',
                '基礎理論の過去問演習（正答率65%）',
                '間違えた問題の復習（基数変換・論理演算）',
                '基礎理論分野の模擬問題を解いた',
                '基礎理論の総復習（苦手な確率問題中心）',
            ],
            'computer' => [
                'テキスト CPU・メモリの仕組みを学習',
                'テキスト OSの役割（プロセス管理・メモリ管理）を読んだ',
                'ハードウェア（入出力装置）のテキストを読み進めた',
                'テキスト システム構成（クライアントサーバ、RAID）を学習',
                '過去問 コンピュータシステム分野を15問',
                '稼働率計算の問題演習',
                'システム構成の過去問演習（正答率70%）',
                'コンピュータシステム分野の復習（RAID・仮想化中心）',
                '模擬試験のコンピュータシステム部分を解き直し',
                'コンピュータアーキテクチャの総復習',
            ],
            'tech' => [
                'テキスト データベース（正規化・SQL）を学習',
                'テキスト ネットワーク基礎（TCP/IP・OSI参照モデル）を読んだ',
                'テキスト セキュリティ基礎（暗号化・認証）を学習',
                'SQL文の練習問題を解いた',
                'ネットワークの過去問を15問解いた',
                'DB設計・正規化の問題演習',
                '過去問 技術要素分野 25問（正答率60%）',
                'IPアドレス・サブネットマスクの演習',
                '技術要素分野の模擬問題を解いた',
                'ネットワークとDBの苦手分野を復習',
            ],
            'dev' => [
                'テキスト ソフトウェア開発手法（ウォーターフォール・アジャイル）を学習',
                'テキスト テスト技法（ホワイトボックス・ブラックボックス）を読んだ',
                'UML図（クラス図・シーケンス図）の学習',
                'テキスト プロジェクトマネジメント基礎を読み進めた',
                '開発技術の過去問を15問解いた',
                'モジュール分割・結合度の問題演習',
                '過去問 開発技術分野（正答率55%）',
                'テスト技法の問題演習を中心に復習',
                '開発技術の模擬問題を解いた',
                '開発プロセスの総復習',
            ],
            'management' => [
                'テキスト プロジェクトマネジメント（PMBOK）を学習',
                'テキスト ITサービスマネジメント（ITIL）を読んだ',
                'テキスト システム監査の基礎を学習',
                '過去問 マネジメント分野を10問解いた',
                'マネジメント分野の問題演習（正答率70%）',
                'システム監査の過去問を中心に演習',
                'マネジメント分野の模擬問題を解いた',
                'PMBOK・ITILの用語を復習',
            ],
            'strategy' => [
                'テキスト 経営戦略（SWOT分析・PPM）を学習',
                'テキスト 法務（知的財産権・個人情報保護法）を読んだ',
                'テキスト 企業会計の基礎を学習',
                'テキスト システム戦略（EA・SOA）を学習',
                '過去問 ストラテジ分野を10問解いた',
                'ストラテジ分野の問題演習（正答率75%）',
                '法務・会計の過去問演習',
                'ストラテジ分野の模擬問題を解いた',
                'ストラテジの総復習（法務が弱いので重点的に）',
            ],
            'algorithm' => [
                'テキスト アルゴリズムの基礎（フローチャート）を学習',
                'テキスト 基本的なデータ構造（配列・リスト・スタック・キュー）を学習',
                'テキスト ソートアルゴリズム（バブル・選択・挿入）を読んだ',
                'テキスト 探索アルゴリズム（線形探索・二分探索）を学習',
                '擬似言語のトレース練習を行った',
                '科目Bサンプル問題（アルゴリズム）を3問解いた',
                '科目Bサンプル問題（アルゴリズム）を4問解いた',
                '過去問の擬似言語問題を演習（正答率50%）',
                '再帰処理・木構造のトレース練習',
                'アルゴリズム問題の模擬演習（時間を計って解いた）',
                '苦手なソート・探索アルゴリズムを復習',
                '科目B模擬問題を本番形式で解いた',
                'アルゴリズム問題の総復習と時間配分の確認',
            ],
            'security' => [
                'テキスト 情報セキュリティの基礎（CIA）を学習',
                'テキスト 暗号化技術（共通鍵・公開鍵）を読んだ',
                'テキスト 認証技術・ディジタル署名を学習',
                'テキスト ネットワークセキュリティ（ファイアウォール・IDS）を読んだ',
                '科目Bサンプル問題（セキュリティ）を2問解いた',
                '情報セキュリティの過去問演習（正答率65%）',
                '科目Bセキュリティ問題の模擬演習',
                'セキュリティ分野の総復習（攻撃手法と対策を整理）',
            ],
        ];
    }
}

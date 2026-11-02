<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Certification;
use App\Models\StudyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Certification $certification;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name'            => 'テストユーザー',
            'email_encrypted' => encrypt('test@example.com'),
            'email_hash'      => hash('sha256', 'test@example.com'),
            'password'        => 'password123',
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        $this->certification = Certification::create([
            'user_id' => $this->user->id,
            'name'    => '基本情報技術者',
            'status'  => 'studying',
        ]);

        $this->category = Category::create([
            'certification_id' => $this->certification->id,
            'name'             => 'ネットワーク',
        ]);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ==========================================
    // 一覧取得
    // ==========================================

    public function test_学習記録一覧を取得できる(): void
    {
        StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'category_id'      => $this->category->id,
            'studied_on'       => '2026-11-01',
            'duration_minutes' => 60,
            'content'          => 'TCP/IPの復習',
        ]);

        $response = $this->getJson('/api/study-logs', $this->authHeaders());

        $response->assertStatus(200)
                 ->assertJsonFragment(['content' => 'TCP/IPの復習']);
    }

    public function test_資格IDで絞り込める(): void
    {
        $other = Certification::create([
            'user_id' => $this->user->id,
            'name'    => 'LPIC',
            'status'  => 'studying',
        ]);

        StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'studied_on'       => '2026-11-01',
            'duration_minutes' => 60,
        ]);
        StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $other->id,
            'studied_on'       => '2026-11-01',
            'duration_minutes' => 30,
        ]);

        $response = $this->getJson(
            "/api/study-logs?certification_id={$this->certification->id}",
            $this->authHeaders()
        );

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_日付範囲で絞り込める(): void
    {
        StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'studied_on'       => '2026-10-01',
            'duration_minutes' => 60,
        ]);
        StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'studied_on'       => '2026-11-15',
            'duration_minutes' => 90,
        ]);

        $response = $this->getJson(
            '/api/study-logs?from=2026-11-01&to=2026-11-30',
            $this->authHeaders()
        );

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    // ==========================================
    // 作成
    // ==========================================

    public function test_学習記録を作成できる(): void
    {
        $response = $this->postJson('/api/study-logs', [
            'certification_id' => $this->certification->id,
            'category_id'      => $this->category->id,
            'studied_on'       => '2026-11-02',
            'duration_minutes' => 90,
            'content'          => 'サブネットマスクの計算',
        ], $this->authHeaders());

        $response->assertStatus(201);

        $this->assertDatabaseHas('study_logs', [
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'duration_minutes' => 90,
        ]);
    }

    public function test_必須項目なしでは422エラー(): void
    {
        $response = $this->postJson('/api/study-logs', [
            'content' => 'メモだけ',
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'certification_id',
                     'studied_on',
                     'duration_minutes',
                 ]);
    }

    public function test_他人の資格にはログを作れない(): void
    {
        $other = User::create([
            'name'            => '他人',
            'email_encrypted' => encrypt('other@example.com'),
            'email_hash'      => hash('sha256', 'other@example.com'),
            'password'        => 'password123',
        ]);
        $otherCert = Certification::create([
            'user_id' => $other->id,
            'name'    => '他人の資格',
            'status'  => 'studying',
        ]);

        $response = $this->postJson('/api/study-logs', [
            'certification_id' => $otherCert->id,
            'studied_on'       => '2026-11-02',
            'duration_minutes' => 60,
        ], $this->authHeaders());

        $response->assertStatus(422);
    }

    // ==========================================
    // 更新・削除
    // ==========================================

    public function test_学習記録を更新できる(): void
    {
        $log = StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'studied_on'       => '2026-11-01',
            'duration_minutes' => 60,
        ]);

        $response = $this->putJson("/api/study-logs/{$log->id}", [
            'duration_minutes' => 120,
            'content'          => '修正後の内容',
        ], $this->authHeaders());

        $response->assertStatus(200);

        $this->assertDatabaseHas('study_logs', [
            'id'               => $log->id,
            'duration_minutes' => 120,
            'content'          => '修正後の内容',
        ]);
    }

    public function test_学習記録を削除できる(): void
    {
        $log = StudyLog::create([
            'user_id'          => $this->user->id,
            'certification_id' => $this->certification->id,
            'studied_on'       => '2026-11-01',
            'duration_minutes' => 60,
        ]);

        $response = $this->deleteJson("/api/study-logs/{$log->id}", [], $this->authHeaders());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('study_logs', ['id' => $log->id]);
    }

    public function test_他人の学習記録は削除できない(): void
    {
        $other = User::create([
            'name'            => '他人',
            'email_encrypted' => encrypt('other2@example.com'),
            'email_hash'      => hash('sha256', 'other2@example.com'),
            'password'        => 'password123',
        ]);
        $otherCert = Certification::create([
            'user_id' => $other->id,
            'name'    => '他人の資格',
            'status'  => 'studying',
        ]);
        $log = StudyLog::create([
            'user_id'          => $other->id,
            'certification_id' => $otherCert->id,
            'studied_on'       => '2026-11-01',
            'duration_minutes' => 60,
        ]);

        $response = $this->deleteJson("/api/study-logs/{$log->id}", [], $this->authHeaders());

        $response->assertStatus(403);
    }
}

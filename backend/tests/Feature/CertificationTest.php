<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificationTest extends TestCase
{
    // テストごとにDBをリセット（マイグレーションを毎回実行）
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザーを作成してトークン取得
        $this->user = User::create([
            'name'            => 'テストユーザー',
            'email_encrypted' => encrypt('test@example.com'),
            'email_hash'      => hash('sha256', 'test@example.com'),
            'password'        => 'password123',
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** 認証ヘッダーを付ける */
    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ==========================================
    // 一覧取得
    // ==========================================

    public function test_資格一覧を取得できる(): void
    {
        Certification::create([
            'user_id' => $this->user->id,
            'name'    => '基本情報技術者',
            'status'  => 'studying',
        ]);

        $response = $this->getJson('/api/certifications', $this->authHeaders());

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['name' => '基本情報技術者']);
    }

    public function test_認証なしでは401エラー(): void
    {
        $response = $this->getJson('/api/certifications');

        $response->assertStatus(401);
    }

    public function test_ステータスで絞り込める(): void
    {
        Certification::create([
            'user_id' => $this->user->id,
            'name'    => '基本情報',
            'status'  => 'studying',
        ]);
        Certification::create([
            'user_id' => $this->user->id,
            'name'    => 'LPIC',
            'status'  => 'passed',
        ]);

        $response = $this->getJson('/api/certifications?status=passed', $this->authHeaders());

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['name' => 'LPIC']);
    }

    // ==========================================
    // 新規作成
    // ==========================================

    public function test_資格を作成できる(): void
    {
        $response = $this->postJson('/api/certifications', [
            'name'      => '応用情報技術者',
            'exam_date' => '2027-04-18',
            'status'    => 'studying',
            'memo'      => '春期試験',
        ], $this->authHeaders());

        $response->assertStatus(201);

        $this->assertDatabaseHas('certifications', [
            'name'    => '応用情報技術者',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_資格名なしでは422エラー(): void
    {
        $response = $this->postJson('/api/certifications', [
            'status' => 'studying',
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('name');
    }

    public function test_無効なステータスでは422エラー(): void
    {
        $response = $this->postJson('/api/certifications', [
            'name'   => 'テスト資格',
            'status' => 'invalid_status',
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('status');
    }

    // ==========================================
    // 更新
    // ==========================================

    public function test_資格を更新できる(): void
    {
        $cert = Certification::create([
            'user_id' => $this->user->id,
            'name'    => '更新前',
            'status'  => 'studying',
        ]);

        $response = $this->putJson("/api/certifications/{$cert->id}", [
            'name'   => '更新後',
            'status' => 'passed',
        ], $this->authHeaders());

        $response->assertStatus(200);

        $this->assertDatabaseHas('certifications', [
            'id'     => $cert->id,
            'name'   => '更新後',
            'status' => 'passed',
        ]);
    }

    public function test_他人の資格は更新できない(): void
    {
        $other = User::create([
            'name'            => '他人',
            'email_encrypted' => encrypt('other@example.com'),
            'email_hash'      => hash('sha256', 'other@example.com'),
            'password'        => 'password123',
        ]);

        $cert = Certification::create([
            'user_id' => $other->id,
            'name'    => '他人の資格',
            'status'  => 'studying',
        ]);

        $response = $this->putJson("/api/certifications/{$cert->id}", [
            'name' => '乗っ取り',
        ], $this->authHeaders());

        $response->assertStatus(403);
    }

    // ==========================================
    // 削除
    // ==========================================

    public function test_資格を削除できる(): void
    {
        $cert = Certification::create([
            'user_id' => $this->user->id,
            'name'    => '削除対象',
            'status'  => 'studying',
        ]);

        $response = $this->deleteJson("/api/certifications/{$cert->id}", [], $this->authHeaders());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('certifications', ['id' => $cert->id]);
    }
}

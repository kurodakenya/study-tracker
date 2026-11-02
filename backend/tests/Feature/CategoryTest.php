<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Certification $certification;

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

        // テスト用資格
        $this->certification = Certification::create([
            'user_id' => $this->user->id,
            'name'    => '基本情報技術者',
            'status'  => 'studying',
        ]);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_分野一覧を取得できる(): void
    {
        Category::create([
            'certification_id' => $this->certification->id,
            'name'             => 'ネットワーク',
        ]);

        $response = $this->getJson(
            "/api/certifications/{$this->certification->id}/categories",
            $this->authHeaders()
        );

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'ネットワーク']);
    }

    public function test_分野を作成できる(): void
    {
        $response = $this->postJson(
            "/api/certifications/{$this->certification->id}/categories",
            ['name' => 'セキュリティ'],
            $this->authHeaders()
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('categories', [
            'certification_id' => $this->certification->id,
            'name'             => 'セキュリティ',
        ]);
    }

    public function test_分野名なしでは422エラー(): void
    {
        $response = $this->postJson(
            "/api/certifications/{$this->certification->id}/categories",
            [],
            $this->authHeaders()
        );

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('name');
    }

    public function test_分野を更新できる(): void
    {
        $category = Category::create([
            'certification_id' => $this->certification->id,
            'name'             => '更新前',
        ]);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => '更新後',
        ], $this->authHeaders());

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'name' => '更新後',
        ]);
    }

    public function test_分野を削除できる(): void
    {
        $category = Category::create([
            'certification_id' => $this->certification->id,
            'name'             => '削除対象',
        ]);

        $response = $this->deleteJson("/api/categories/{$category->id}", [], $this->authHeaders());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}

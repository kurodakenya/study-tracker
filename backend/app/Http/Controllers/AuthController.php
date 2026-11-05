<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ユーザー登録
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // メールアドレスの重複チェック（ハッシュで検索）
        $emailHash = hash('sha256', strtolower($validated['email']));
        if (User::where('email_hash', $emailHash)->exists()) {
            return response()->json([
                'message' => 'このメールアドレスは既に登録されています。',
            ], 422);
        }

        // ユーザー作成
        $user = User::create([
            'name'            => $validated['name'],
            'email_encrypted' => $validated['email'],
            'email_hash'      => $emailHash,
            'password'        => $validated['password'],
        ]);

        // トークン発行
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    // ログイン
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // email_hash で検索
        $emailHash = hash('sha256', strtolower($validated['email']));
        $user = User::where('email_hash', $emailHash)->first();

        // ユーザーが見つからない or パスワード不一致
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません。',
            ], 401);
        }

        // トークン発行
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    // ログアウト
    public function logout(Request $request)
    {
        // 現在のトークンを削除
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ログアウトしました。',
        ]);
    }
}

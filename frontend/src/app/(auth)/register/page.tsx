"use client";

import { useState } from "react";
import Link from "next/link";
import { Award } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useAuth } from "@/contexts/auth-context";

export default function RegisterPage() {
  const { register } = useAuth();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleRegister() {
    if (!name || !email || !password || !passwordConfirmation) {
      setError("すべての項目を入力してください");
      return;
    }
    if (password.length < 8) {
      setError("パスワードは8文字以上で入力してください");
      return;
    }
    if (password !== passwordConfirmation) {
      setError("パスワードが一致しません");
      return;
    }
    setError("");
    setLoading(true);
    try {
      await register(name, email, password, passwordConfirmation);
    } catch (err) {
      setError((err as Error).message || "登録に失敗しました");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex min-h-full items-center justify-center bg-muted px-4 py-12">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-primary">
            <Award className="h-6 w-6 text-primary-foreground" />
          </div>
          <CardTitle className="text-2xl">新規登録</CardTitle>
          <CardDescription>アカウントを作成して学習管理を始めましょう</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {error && (
            <p className="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
              {error}
            </p>
          )}
          <div className="space-y-2">
            <Label htmlFor="name">ユーザー名</Label>
            <Input
              id="name"
              placeholder="山田太郎"
              value={name}
              onChange={(e) => setName(e.target.value)}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="email">メールアドレス</Label>
            <Input
              id="email"
              type="email"
              placeholder="you@example.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password">パスワード</Label>
            <Input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password_confirmation">パスワード（確認）</Label>
            <Input
              id="password_confirmation"
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
            />
          </div>
        </CardContent>
        <CardFooter className="flex flex-col gap-4">
          <Button
            className="w-full"
            onClick={handleRegister}
            disabled={loading}
          >
            {loading ? "登録中..." : "登録"}
          </Button>
          <p className="text-sm text-muted-foreground">
            すでにアカウントをお持ちの方は
            <Link href="/login" className="ml-1 text-primary underline underline-offset-4">
              ログイン
            </Link>
          </p>
        </CardFooter>
      </Card>
    </div>
  );
}

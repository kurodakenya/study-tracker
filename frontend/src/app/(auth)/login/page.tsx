"use client";

import { useState } from "react";
import Link from "next/link";
import { Award } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useAuth } from "@/contexts/auth-context";

export default function LoginPage() {
  const { login } = useAuth();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleLogin() {
    if (!email || !password) {
      setError("メールアドレスとパスワードを入力してください");
      return;
    }
    setError("");
    setLoading(true);
    try {
      await login(email, password);
    } catch (err) {
      setError((err as Error).message || "ログインに失敗しました");
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
          <CardTitle className="text-2xl">ログイン</CardTitle>
          <CardDescription>資格管理アプリにログインします</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {error && (
            <p className="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
              {error}
            </p>
          )}
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
        </CardContent>
        <CardFooter className="flex flex-col gap-4">
          <Button
            className="w-full"
            onClick={handleLogin}
            disabled={loading}
          >
            {loading ? "ログイン中..." : "ログイン"}
          </Button>
          <p className="text-sm text-muted-foreground">
            アカウントをお持ちでない方は
            <Link href="/register" className="ml-1 text-primary underline underline-offset-4">
              新規登録
            </Link>
          </p>
        </CardFooter>
      </Card>
    </div>
  );
}

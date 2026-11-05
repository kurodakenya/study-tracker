"use client";

import { useState } from "react";
import { Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";

interface Section {
  id: string;
  title: string;
  content: string;
}

const sections: Section[] = [
  {
    id: "about",
    title: "このアプリについて",
    content:
      "資格管理アプリは、資格取得に向けた勉強の進捗を記録・管理するためのツールです。資格ごとの勉強時間を可視化し、学習のモチベーション維持に役立ちます。",
  },
  {
    id: "dashboard",
    title: "ダッシュボード",
    content:
      "ダッシュボードでは、登録した資格の数、累計勉強時間、資格あたりの平均勉強時間を確認できます。各資格の累計勉強時間もリストで表示されます。",
  },
  {
    id: "certifications",
    title: "資格管理",
    content:
      "「資格を追加」ボタンから新しい資格を登録できます。資格名、試験日、ステータス（勉強中・合格・不合格）、メモを設定できます。各資格には分野（カテゴリ）を追加でき、勉強記録と紐付けることができます。",
  },
  {
    id: "study-logs",
    title: "勉強記録",
    content:
      "日々の勉強内容を記録します。資格・分野を選択し、学習日・勉強時間・やった内容を入力して保存します。フィルタ機能で資格や日付範囲で絞り込めます。記録は後から編集・削除も可能です。",
  },
  {
    id: "calendar",
    title: "カレンダー",
    content:
      "月単位で勉強した日をカレンダー形式で確認できます。勉強した日は緑色で表示され、勉強時間が多いほど色が濃くなります。日付をクリックすると、その日の勉強記録の詳細が表示されます。",
  },
  {
    id: "account",
    title: "アカウント",
    content:
      "ログイン・ログアウトはサイドバーから行えます。新しいアカウントは「新規登録」画面から作成できます。メールアドレスとパスワードでログインします。",
  },
  {
    id: "tips",
    title: "活用のコツ",
    content:
      "・毎日の勉強後にすぐ記録する習慣をつけましょう\n・分野を細かく設定すると、苦手分野の把握に役立ちます\n・カレンダーで連続勉強日を確認し、モチベーションを維持しましょう\n・メモ欄に目標や試験対策のポイントを書いておくと便利です",
  },
];

export default function HelpPage() {
  const [query, setQuery] = useState("");

  const filtered = query
    ? sections.filter(
        (s) =>
          s.title.includes(query) ||
          s.content.includes(query)
      )
    : sections;

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">ヘルプ</h1>
        <p className="text-muted-foreground">アプリの使い方ガイド</p>
      </div>

      <div className="flex gap-8">
        {/* 左：目次 */}
        <nav className="hidden md:block w-48 shrink-0">
          <p className="mb-3 text-sm font-medium">目次</p>
          <ul className="space-y-1">
            {sections.map((s) => (
              <li key={s.id}>
                <a
                  href={`#${s.id}`}
                  className="block rounded-md px-3 py-1.5 text-sm text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
                >
                  {s.title}
                </a>
              </li>
            ))}
          </ul>
        </nav>

        {/* 右：本文 */}
        <div className="flex-1 space-y-6">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              className="pl-9"
              placeholder="キーワードで検索..."
              value={query}
              onChange={(e) => setQuery(e.target.value)}
            />
          </div>

          {filtered.length === 0 ? (
            <Card>
              <CardContent className="py-8 text-center">
                <p className="text-muted-foreground">
                  「{query}」に一致する項目が見つかりませんでした
                </p>
              </CardContent>
            </Card>
          ) : (
            filtered.map((s) => (
              <Card key={s.id} id={s.id}>
                <CardContent className="pt-6">
                  <h2 className="mb-3 text-lg font-semibold">{s.title}</h2>
                  <p className="text-sm leading-relaxed text-muted-foreground whitespace-pre-wrap">
                    {s.content}
                  </p>
                </CardContent>
              </Card>
            ))
          )}
        </div>
      </div>
    </div>
  );
}

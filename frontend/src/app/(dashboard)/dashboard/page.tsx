"use client";

import { useState, useEffect } from "react";
import { Award, BookOpen, Clock, TrendingUp } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  certifications as certApi,
  studyLogs as logApi,
  type Certification,
  type StudyLog,
} from "@/lib/api";

export default function DashboardPage() {
  const [certs, setCerts] = useState<Certification[]>([]);
  const [logs, setLogs] = useState<StudyLog[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetch() {
      try {
        const [certRes, logRes] = await Promise.all([
          certApi.list(),
          logApi.list(),
        ]);
        setCerts(certRes.data);
        setLogs(logRes.data);
      } catch {
        // handled gracefully with empty state
      } finally {
        setLoading(false);
      }
    }
    fetch();
  }, []);

  const now = new Date();
  const thisMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
  const monthlyMinutes = logs
    .filter((l) => l.studied_on.startsWith(thisMonth))
    .reduce((sum, l) => sum + l.duration_minutes, 0);
  const monthlyHours = (monthlyMinutes / 60).toFixed(1);

  // Simple streak: count consecutive days with logs from today backwards
  const logDates = new Set(logs.map((l) => l.studied_on));
  let streak = 0;
  const d = new Date();
  while (true) {
    const dateStr = d.toISOString().split("T")[0];
    if (logDates.has(dateStr)) {
      streak++;
      d.setDate(d.getDate() - 1);
    } else {
      break;
    }
  }

  const stats = [
    { title: "登録資格数", value: String(certs.length), icon: Award },
    { title: "学習記録数", value: String(logs.length), icon: BookOpen },
    { title: "今月の学習時間", value: `${monthlyHours}h`, icon: Clock },
    { title: "学習継続日数", value: `${streak}日`, icon: TrendingUp },
  ];

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-muted-foreground">読み込み中...</p>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">ダッシュボード</h1>
        <p className="text-muted-foreground">学習の進捗を確認しましょう</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <Card key={stat.title}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">{stat.title}</CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
            </CardContent>
          </Card>
        ))}
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-xl">最近の学習記録</CardTitle>
        </CardHeader>
        <CardContent>
          {logs.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              まだ学習記録がありません
            </p>
          ) : (
            <div className="space-y-4">
              {logs.slice(0, 5).map((log) => (
                <div
                  key={log.id}
                  className="flex items-center justify-between border-b border-border pb-4 last:border-0 last:pb-0"
                >
                  <div className="space-y-1">
                    <p className="text-sm font-medium">
                      {log.certification?.name || "—"}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {log.category?.name || "—"}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-medium">
                      {log.duration_minutes >= 60
                        ? `${(log.duration_minutes / 60).toFixed(1)}h`
                        : `${log.duration_minutes}分`}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      {log.studied_on}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}

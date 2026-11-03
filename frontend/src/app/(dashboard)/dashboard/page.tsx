"use client";

import { useState, useEffect } from "react";
import { Award, Clock, TrendingUp } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  dashboard as dashApi,
  type DashboardCertification,
  type DashboardResponse,
} from "@/lib/api";

const statusLabels: Record<string, string> = {
  studying: "勉強中",
  passed: "合格",
  failed: "不合格",
};

const statusColors: Record<string, string> = {
  studying: "bg-blue-100 text-blue-800",
  passed: "bg-green-100 text-green-800",
  failed: "bg-red-100 text-red-800",
};

function formatDuration(minutes: number): string {
  if (minutes === 0) return "0分";
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  if (h === 0) return `${m}分`;
  if (m === 0) return `${h}時間`;
  return `${h}時間${m}分`;
}

export default function DashboardPage() {
  const [data, setData] = useState<DashboardResponse | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetch() {
      try {
        const res = await dashApi.get();
        setData(res);
      } catch {
        // handled with empty state
      } finally {
        setLoading(false);
      }
    }
    fetch();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-muted-foreground">読み込み中...</p>
      </div>
    );
  }

  const certs = data?.data ?? [];
  const summary = data?.summary ?? { total_certifications: 0, total_minutes: 0, total_hours: 0 };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">ダッシュボード</h1>
        <p className="text-muted-foreground">学習の進捗を確認しましょう</p>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">登録資格数</CardTitle>
            <Award className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{summary.total_certifications}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">累計勉強時間</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{formatDuration(summary.total_minutes)}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">資格あたり平均</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {summary.total_certifications > 0
                ? formatDuration(Math.round(summary.total_minutes / summary.total_certifications))
                : "—"}
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-xl">資格別 累計勉強時間</CardTitle>
        </CardHeader>
        <CardContent>
          {certs.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              まだ資格が登録されていません
            </p>
          ) : (
            <div className="space-y-4">
              {certs.map((cert: DashboardCertification) => (
                <div
                  key={cert.id}
                  className="flex items-center justify-between border-b border-border pb-4 last:border-0 last:pb-0"
                >
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <p className="text-sm font-medium">{cert.name}</p>
                      <span
                        className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[cert.status] ?? ""}`}
                      >
                        {statusLabels[cert.status] ?? cert.status}
                      </span>
                    </div>
                    <p className="text-xs text-muted-foreground">
                      {cert.exam_date ? `試験日: ${cert.exam_date}` : "試験日未定"}
                      {cert.categories.length > 0 &&
                        ` · ${cert.categories.map((c) => c.name).join(", ")}`}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-lg font-bold">
                      {formatDuration(cert.total_minutes)}
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

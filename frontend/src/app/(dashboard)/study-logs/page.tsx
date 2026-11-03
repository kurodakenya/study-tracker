"use client";

import { useState, useEffect, useCallback } from "react";
import { Plus, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  studyLogs as logApi,
  certifications as certApi,
  type StudyLog,
  type Certification,
} from "@/lib/api";

export default function StudyLogsPage() {
  const [logs, setLogs] = useState<StudyLog[]>([]);
  const [certs, setCerts] = useState<Certification[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [error, setError] = useState("");

  // Form state
  const [formCertId, setFormCertId] = useState("");
  const [formCatId, setFormCatId] = useState("");
  const [formDate, setFormDate] = useState(
    new Date().toISOString().split("T")[0]
  );
  const [formMinutes, setFormMinutes] = useState("");
  const [formContent, setFormContent] = useState("");

  const fetchLogs = useCallback(async () => {
    try {
      const res = await logApi.list();
      setLogs(res.data);
    } catch {
      setError("学習記録の取得に失敗しました");
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchCerts = useCallback(async () => {
    try {
      const res = await certApi.list();
      setCerts(res.data);
    } catch {
      // ignore
    }
  }, []);

  useEffect(() => {
    fetchLogs();
    fetchCerts();
  }, [fetchLogs, fetchCerts]);

  const selectedCert = certs.find((c) => c.id === Number(formCertId));

  function resetForm() {
    setFormCertId("");
    setFormCatId("");
    setFormDate(new Date().toISOString().split("T")[0]);
    setFormMinutes("");
    setFormContent("");
    setShowForm(false);
    setError("");
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    try {
      await logApi.create({
        certification_id: Number(formCertId),
        category_id: formCatId ? Number(formCatId) : undefined,
        studied_on: formDate,
        duration_minutes: Number(formMinutes),
        content: formContent || undefined,
      });
      resetForm();
      fetchLogs();
    } catch (err) {
      setError((err as Error).message);
    }
  }

  async function handleDelete(id: number) {
    if (!confirm("この学習記録を削除しますか？")) return;
    try {
      await logApi.delete(id);
      fetchLogs();
    } catch (err) {
      setError((err as Error).message);
    }
  }

  function formatDuration(minutes: number): string {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    if (h === 0) return `${m}分`;
    if (m === 0) return `${h}時間`;
    return `${h}時間${m}分`;
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-muted-foreground">読み込み中...</p>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">学習記録</h1>
          <p className="text-muted-foreground">日々の学習を記録しましょう</p>
        </div>
        <Button onClick={() => { resetForm(); setShowForm(true); }}>
          <Plus className="h-4 w-4" />
          記録を追加
        </Button>
      </div>

      {error && (
        <p className="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
          {error}
        </p>
      )}

      {showForm && (
        <Card>
          <CardContent className="pt-6">
            <form onSubmit={handleSubmit}>
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="certification">資格</Label>
                  <select
                    id="certification"
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    value={formCertId}
                    onChange={(e) => {
                      setFormCertId(e.target.value);
                      setFormCatId("");
                    }}
                    required
                  >
                    <option value="">選択してください</option>
                    {certs.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="category">分野</Label>
                  <select
                    id="category"
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    value={formCatId}
                    onChange={(e) => setFormCatId(e.target.value)}
                    disabled={!selectedCert}
                  >
                    <option value="">選択してください（任意）</option>
                    {selectedCert?.categories.map((cat) => (
                      <option key={cat.id} value={cat.id}>
                        {cat.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="studied_on">学習日</Label>
                  <Input
                    id="studied_on"
                    type="date"
                    value={formDate}
                    onChange={(e) => setFormDate(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="minutes">学習時間（分）</Label>
                  <Input
                    id="minutes"
                    type="number"
                    min="1"
                    max="1440"
                    placeholder="90"
                    value={formMinutes}
                    onChange={(e) => setFormMinutes(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-2 sm:col-span-2">
                  <Label htmlFor="content">メモ</Label>
                  <Input
                    id="content"
                    placeholder="学習内容のメモ（任意）"
                    value={formContent}
                    onChange={(e) => setFormContent(e.target.value)}
                  />
                </div>
              </div>
              <div className="mt-4 flex gap-2">
                <Button type="submit">保存</Button>
                <Button type="button" variant="outline" onClick={resetForm}>
                  キャンセル
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      {logs.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <p className="text-muted-foreground">
              まだ学習記録がありません。「記録を追加」から始めましょう。
            </p>
          </CardContent>
        </Card>
      ) : (
        <Card>
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-border">
                    <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">日付</th>
                    <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">資格</th>
                    <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">分野</th>
                    <th className="px-4 py-3 text-right text-sm font-medium text-muted-foreground">時間</th>
                    <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">メモ</th>
                    <th className="px-4 py-3 text-right text-sm font-medium text-muted-foreground">操作</th>
                  </tr>
                </thead>
                <tbody>
                  {logs.map((log) => (
                    <tr key={log.id} className="border-b border-border last:border-0">
                      <td className="px-4 py-3 text-sm">{log.studied_on}</td>
                      <td className="px-4 py-3 text-sm">
                        {log.certification?.name || "—"}
                      </td>
                      <td className="px-4 py-3 text-sm">
                        {log.category ? (
                          <span className="rounded-md bg-secondary px-2 py-1 text-xs">
                            {log.category.name}
                          </span>
                        ) : (
                          "—"
                        )}
                      </td>
                      <td className="px-4 py-3 text-right text-sm">
                        {formatDuration(log.duration_minutes)}
                      </td>
                      <td className="px-4 py-3 text-sm text-muted-foreground">
                        {log.content || "—"}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8"
                          onClick={() => handleDelete(log.id)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}

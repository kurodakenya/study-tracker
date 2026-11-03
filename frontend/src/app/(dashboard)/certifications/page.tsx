"use client";

import { useState, useEffect, useCallback } from "react";
import { Plus, Pencil, Trash2, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  certifications as certApi,
  categories as catApi,
  type Certification,
} from "@/lib/api";

const statusLabels: Record<Certification["status"], string> = {
  studying: "学習中",
  passed: "合格",
  failed: "不合格",
};

const statusColors: Record<Certification["status"], string> = {
  studying: "bg-blue-100 text-blue-800",
  passed: "bg-green-100 text-green-800",
  failed: "bg-red-100 text-red-800",
};

export default function CertificationsPage() {
  const [certs, setCerts] = useState<Certification[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formName, setFormName] = useState("");
  const [formExamDate, setFormExamDate] = useState("");
  const [formStatus, setFormStatus] = useState<string>("studying");
  const [newCatName, setNewCatName] = useState("");
  const [addingCatFor, setAddingCatFor] = useState<number | null>(null);
  const [error, setError] = useState("");

  const fetchCerts = useCallback(async () => {
    try {
      const res = await certApi.list();
      setCerts(res.data);
    } catch {
      setError("資格の取得に失敗しました");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchCerts();
  }, [fetchCerts]);

  function startEdit(cert: Certification) {
    setEditingId(cert.id);
    setFormName(cert.name);
    setFormExamDate(cert.exam_date || "");
    setFormStatus(cert.status);
    setShowForm(false);
  }

  function resetForm() {
    setFormName("");
    setFormExamDate("");
    setFormStatus("studying");
    setShowForm(false);
    setEditingId(null);
    setError("");
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    try {
      if (editingId) {
        await certApi.update(editingId, {
          name: formName,
          exam_date: formExamDate || undefined,
          status: formStatus,
        });
      } else {
        await certApi.create({
          name: formName,
          exam_date: formExamDate || undefined,
          status: formStatus,
        });
      }
      resetForm();
      fetchCerts();
    } catch (err) {
      setError((err as Error).message);
    }
  }

  async function handleDelete(id: number) {
    if (!confirm("この資格を削除しますか？")) return;
    try {
      await certApi.delete(id);
      fetchCerts();
    } catch (err) {
      setError((err as Error).message);
    }
  }

  async function handleAddCategory(certId: number) {
    if (!newCatName.trim()) return;
    try {
      await catApi.create(certId, { name: newCatName.trim() });
      setNewCatName("");
      setAddingCatFor(null);
      fetchCerts();
    } catch (err) {
      setError((err as Error).message);
    }
  }

  async function handleDeleteCategory(catId: number) {
    if (!confirm("この分野を削除しますか？")) return;
    try {
      await catApi.delete(catId);
      fetchCerts();
    } catch (err) {
      setError((err as Error).message);
    }
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
          <h1 className="text-3xl font-bold tracking-tight">資格一覧</h1>
          <p className="text-muted-foreground">取得を目指す資格を管理します</p>
        </div>
        <Button onClick={() => { resetForm(); setShowForm(true); }}>
          <Plus className="h-4 w-4" />
          資格を追加
        </Button>
      </div>

      {error && (
        <p className="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
          {error}
        </p>
      )}

      {(showForm || editingId) && (
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">
              {editingId ? "資格を編集" : "新しい資格を追加"}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit}>
              <div className="grid gap-4 sm:grid-cols-3">
                <div className="space-y-2">
                  <Label htmlFor="name">資格名</Label>
                  <Input
                    id="name"
                    placeholder="例: 基本情報技術者"
                    value={formName}
                    onChange={(e) => setFormName(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="exam_date">試験日</Label>
                  <Input
                    id="exam_date"
                    type="date"
                    value={formExamDate}
                    onChange={(e) => setFormExamDate(e.target.value)}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="status">ステータス</Label>
                  <select
                    id="status"
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    value={formStatus}
                    onChange={(e) => setFormStatus(e.target.value)}
                  >
                    <option value="studying">学習中</option>
                    <option value="passed">合格</option>
                    <option value="failed">不合格</option>
                  </select>
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

      {certs.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <p className="text-muted-foreground">
              まだ資格が登録されていません。「資格を追加」から始めましょう。
            </p>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {certs.map((cert) => (
            <Card key={cert.id}>
              <CardHeader className="flex flex-row items-start justify-between space-y-0">
                <div className="space-y-1">
                  <CardTitle className="text-lg">{cert.name}</CardTitle>
                  <p className="text-sm text-muted-foreground">
                    {cert.exam_date
                      ? `試験日: ${cert.exam_date}`
                      : "試験日未定"}
                  </p>
                </div>
                <span
                  className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${statusColors[cert.status]}`}
                >
                  {statusLabels[cert.status]}
                </span>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <div>
                    <p className="mb-2 text-sm font-medium text-muted-foreground">
                      分野
                    </p>
                    <div className="flex flex-wrap gap-1.5">
                      {cert.categories.map((cat) => (
                        <span
                          key={cat.id}
                          className="group flex items-center gap-1 rounded-md bg-secondary px-2 py-1 text-xs"
                        >
                          {cat.name}
                          <button
                            onClick={() => handleDeleteCategory(cat.id)}
                            className="hidden text-muted-foreground hover:text-destructive group-hover:inline"
                          >
                            <X className="h-3 w-3" />
                          </button>
                        </span>
                      ))}
                      {addingCatFor === cert.id ? (
                        <div className="flex items-center gap-1">
                          <Input
                            className="h-7 w-28 text-xs"
                            placeholder="分野名"
                            value={newCatName}
                            onChange={(e) => setNewCatName(e.target.value)}
                            onKeyDown={(e) => {
                              if (e.key === "Enter") {
                                e.preventDefault();
                                handleAddCategory(cert.id);
                              }
                            }}
                            autoFocus
                          />
                          <Button
                            size="sm"
                            className="h-7 px-2 text-xs"
                            onClick={() => handleAddCategory(cert.id)}
                          >
                            追加
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            className="h-7 px-2 text-xs"
                            onClick={() => { setAddingCatFor(null); setNewCatName(""); }}
                          >
                            <X className="h-3 w-3" />
                          </Button>
                        </div>
                      ) : (
                        <button
                          onClick={() => setAddingCatFor(cert.id)}
                          className="rounded-md border border-dashed border-border px-2 py-1 text-xs text-muted-foreground hover:border-primary hover:text-primary"
                        >
                          + 追加
                        </button>
                      )}
                    </div>
                  </div>
                  <div className="flex gap-2 pt-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => startEdit(cert)}
                    >
                      <Pencil className="h-3.5 w-3.5" />
                      編集
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleDelete(cert.id)}
                    >
                      <Trash2 className="h-3.5 w-3.5" />
                      削除
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

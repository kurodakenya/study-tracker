"use client";

import { useState, useEffect, useCallback } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  calendar as calApi,
  type CalendarDay,
  type StudyLog,
} from "@/lib/api";

const WEEKDAY_LABELS = ["日", "月", "火", "水", "木", "金", "土"];

function getIntensity(minutes: number): number {
  if (minutes === 0) return 0;
  if (minutes <= 30) return 1;
  if (minutes <= 60) return 2;
  if (minutes <= 120) return 3;
  return 4;
}

const intensityClasses = [
  "bg-muted",
  "bg-green-200",
  "bg-green-400",
  "bg-green-500",
  "bg-green-700",
];

function formatDuration(minutes: number): string {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  if (h === 0) return `${m}分`;
  if (m === 0) return `${h}時間`;
  return `${h}時間${m}分`;
}

export default function CalendarPage() {
  const now = new Date();
  const [year, setYear] = useState(now.getFullYear());
  const [month, setMonth] = useState(now.getMonth() + 1);
  const [days, setDays] = useState<CalendarDay[]>([]);
  const [selectedDate, setSelectedDate] = useState<string | null>(null);
  const [selectedLogs, setSelectedLogs] = useState<StudyLog[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingLogs, setLoadingLogs] = useState(false);

  const fetchCalendar = useCallback(async () => {
    setLoading(true);
    try {
      const res = await calApi.list(year, month);
      setDays(res.days);
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  }, [year, month]);

  useEffect(() => {
    fetchCalendar();
    setSelectedDate(null);
    setSelectedLogs([]);
  }, [fetchCalendar]);

  async function handleDateClick(date: string) {
    setSelectedDate(date);
    setLoadingLogs(true);
    try {
      const res = await calApi.getByDate(date);
      setSelectedLogs(res.data);
    } catch {
      setSelectedLogs([]);
    } finally {
      setLoadingLogs(false);
    }
  }

  function prevMonth() {
    if (month === 1) {
      setYear(year - 1);
      setMonth(12);
    } else {
      setMonth(month - 1);
    }
  }

  function nextMonth() {
    if (month === 12) {
      setYear(year + 1);
      setMonth(1);
    } else {
      setMonth(month + 1);
    }
  }

  // Build calendar grid
  const firstDay = new Date(year, month - 1, 1);
  const lastDay = new Date(year, month, 0);
  const startWeekday = firstDay.getDay();
  const daysInMonth = lastDay.getDate();

  const dayMap = new Map(days.map((d) => [d.date, d]));

  const cells: { date: string; day: number; data: CalendarDay | undefined }[] = [];
  for (let i = 0; i < startWeekday; i++) {
    cells.push({ date: "", day: 0, data: undefined });
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
    cells.push({ date: dateStr, day: d, data: dayMap.get(dateStr) });
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">カレンダー</h1>
        <p className="text-muted-foreground">勉強した日を確認しましょう</p>
      </div>

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <Button variant="ghost" size="icon" onClick={prevMonth}>
              <ChevronLeft className="h-5 w-5" />
            </Button>
            <CardTitle className="text-lg">
              {year}年 {month}月
            </CardTitle>
            <Button variant="ghost" size="icon" onClick={nextMonth}>
              <ChevronRight className="h-5 w-5" />
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="flex items-center justify-center py-12">
              <p className="text-muted-foreground">読み込み中...</p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-7 gap-1 mb-1">
                {WEEKDAY_LABELS.map((label, i) => (
                  <div
                    key={label}
                    className={`text-center text-xs font-medium py-1 ${
                      i === 0 ? "text-red-500" : i === 6 ? "text-blue-500" : "text-muted-foreground"
                    }`}
                  >
                    {label}
                  </div>
                ))}
              </div>
              <div className="grid grid-cols-7 gap-1">
                {cells.map((cell, i) =>
                  cell.day === 0 ? (
                    <div key={`empty-${i}`} className="aspect-square" />
                  ) : (
                    <button
                      key={cell.date}
                      onClick={() => handleDateClick(cell.date)}
                      className={`aspect-square rounded-md flex flex-col items-center justify-center text-sm transition-colors
                        ${selectedDate === cell.date ? "ring-2 ring-primary" : ""}
                        ${cell.data ? intensityClasses[getIntensity(cell.data.total_minutes)] : "bg-muted/30 hover:bg-muted/60"}
                        ${cell.data ? "text-white font-medium" : "text-muted-foreground"}
                      `}
                      title={
                        cell.data
                          ? `${formatDuration(cell.data.total_minutes)}（${cell.data.count}件）`
                          : "記録なし"
                      }
                    >
                      <span className={`text-xs ${cell.data && getIntensity(cell.data.total_minutes) >= 2 ? "text-white" : "text-foreground"}`}>
                        {cell.day}
                      </span>
                    </button>
                  )
                )}
              </div>

              <div className="mt-4 flex items-center justify-end gap-2 text-xs text-muted-foreground">
                <span>少</span>
                {intensityClasses.slice(1).map((cls, i) => (
                  <div
                    key={i}
                    className={`h-3 w-3 rounded-sm ${cls}`}
                  />
                ))}
                <span>多</span>
              </div>
            </>
          )}
        </CardContent>
      </Card>

      {selectedDate && (
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">{selectedDate} の勉強記録</CardTitle>
          </CardHeader>
          <CardContent>
            {loadingLogs ? (
              <p className="text-sm text-muted-foreground">読み込み中...</p>
            ) : selectedLogs.length === 0 ? (
              <p className="text-sm text-muted-foreground">
                この日の記録はありません
              </p>
            ) : (
              <div className="space-y-3">
                {selectedLogs.map((log) => (
                  <div
                    key={log.id}
                    className="flex items-center justify-between border-b border-border pb-3 last:border-0 last:pb-0"
                  >
                    <div className="space-y-1">
                      <p className="text-sm font-medium">
                        {log.certification?.name || "—"}
                      </p>
                      {log.category && (
                        <span className="rounded-md bg-secondary px-2 py-0.5 text-xs">
                          {log.category.name}
                        </span>
                      )}
                      {log.content && (
                        <p className="text-xs text-muted-foreground">
                          {log.content}
                        </p>
                      )}
                    </div>
                    <p className="text-sm font-medium">
                      {formatDuration(log.duration_minutes)}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  );
}

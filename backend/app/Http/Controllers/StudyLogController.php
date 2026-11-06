<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudyLogRequest;
use App\Http\Requests\UpdateStudyLogRequest;
use App\Http\Resources\StudyLogResource;
use App\Models\StudyLog;
use Illuminate\Http\Request;

class StudyLogController extends Controller
{
    /**
     * 学習記録一覧（ページネーション付き）
     * GET /api/study-logs?certification_id=1&category_id=2&from=2026-01-01&to=2026-12-31
     *
     * 全フィルタは任意。組み合わせ自由。
     * 1ページ20件、日付降順で返す。
     */
    public function index(Request $request)
    {
        $query = $request->user()->studyLogs()
            ->with(['certification', 'category']);

        // 資格で絞り込み
        if ($request->filled('certification_id')) {
            $query->where('certification_id', $request->certification_id);
        }

        // 分野で絞り込み
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 期間で絞り込み（from〜to）
        if ($request->filled('from')) {
            $query->where('studied_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('studied_on', '<=', $request->to);
        }

        $logs = $query->orderBy('studied_on', 'desc')
                      ->paginate(20);

        return StudyLogResource::collection($logs);
    }

    /**
     * 学習記録詳細
     * GET /api/study-logs/{studyLog}
     */
    public function show(Request $request, StudyLog $studyLog)
    {
        if ($studyLog->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        return new StudyLogResource($studyLog->load(['certification', 'category']));
    }

    /**
     * 学習記録作成
     * POST /api/study-logs
     *
     * certification_idの所有権もチェック。
     */
    public function store(StoreStudyLogRequest $request)
    {
        // 指定された資格が自分のものかチェック
        $certification = $request->user()->certifications()
            ->find($request->certification_id);

        if (!$certification) {
            return response()->json([
                'message' => '指定された資格が見つかりません。',
            ], 422);
        }

        // category_idが指定されている場合、その資格に属するかチェック
        if ($request->filled('category_id')) {
            $categoryExists = $certification->categories()
                ->where('id', $request->category_id)
                ->exists();

            if (!$categoryExists) {
                return response()->json([
                    'message' => '指定された分野はこの資格に属していません。',
                ], 422);
            }
        }

        $log = $request->user()->studyLogs()->create(
            $request->validated()
        );

        return (new StudyLogResource($log->load(['certification', 'category'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 学習記録更新
     * PUT /api/study-logs/{studyLog}
     */
    public function update(UpdateStudyLogRequest $request, StudyLog $studyLog)
    {
        if ($studyLog->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $studyLog->update($request->validated());

        return new StudyLogResource($studyLog->load(['certification', 'category']));
    }

    /**
     * 学習記録削除
     * DELETE /api/study-logs/{studyLog}
     */
    public function destroy(Request $request, StudyLog $studyLog)
    {
        if ($studyLog->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $studyLog->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCertificationRequest;
use App\Http\Requests\UpdateCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    /**
     * 一覧取得
     * GET /api/certifications?status=studying
     *
     * ログインユーザーの資格のみ返す。
     * statusクエリで絞り込み可能。
     */
    public function index(Request $request)
    {
        $query = $request->user()->certifications()->with('categories');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $certifications = $query->orderBy('exam_date', 'asc')->get();

        return CertificationResource::collection($certifications);
    }

    /**
     * 新規作成
     * POST /api/certifications
     *
     * user_id はログインユーザーから自動セット（リクエストからは受け取らない）。
     */
    public function store(StoreCertificationRequest $request)
    {
        $certification = $request->user()->certifications()->create(
            $request->validated()
        );

        return (new CertificationResource($certification->load('categories')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 詳細取得
     * GET /api/certifications/{certification}
     *
     * カテゴリと学習記録もまとめて返す（Eager Loading）。
     */
    public function show(Request $request, Certification $certification)
    {
        // 自分の資格かチェック
        if ($certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $certification->load(['categories', 'studyLogs']);

        return new CertificationResource($certification);
    }

    /**
     * 更新
     * PUT /api/certifications/{certification}
     *
     * 送られたフィールドだけ更新（部分更新OK）。
     */
    public function update(UpdateCertificationRequest $request, Certification $certification)
    {
        if ($certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $certification->update($request->validated());

        return new CertificationResource($certification->load('categories'));
    }

    /**
     * 削除
     * DELETE /api/certifications/{certification}
     *
     * cascadeOnDelete により、紐づくcategories・study_logsも自動削除される。
     */
    public function destroy(Request $request, Certification $certification)
    {
        if ($certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $certification->delete();

        return response()->json(null, 204);
    }
}

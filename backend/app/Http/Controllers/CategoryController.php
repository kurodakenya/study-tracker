<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Certification;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * 分野一覧
     * GET /api/certifications/{certification}/categories
     *
     * 親の資格が自分のものかチェックしてから返す。
     */
    public function index(Request $request, Certification $certification)
    {
        if ($certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        return CategoryResource::collection($certification->categories);
    }

    /**
     * 分野作成
     * POST /api/certifications/{certification}/categories
     */
    public function store(StoreCategoryRequest $request, Certification $certification)
    {
        if ($certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $category = $certification->categories()->create(
            $request->validated()
        );

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 分野更新
     * PUT /api/categories/{category}
     *
     * カテゴリから辿って資格の所有者をチェック。
     */
    public function update(StoreCategoryRequest $request, Category $category)
    {
        if ($category->certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * 分野削除
     * DELETE /api/categories/{category}
     *
     * 削除すると、study_logsのcategory_idはNULLになる（nullOnDelete）。
     */
    public function destroy(Request $request, Category $category)
    {
        if ($category->certification->user_id !== $request->user()->id) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}

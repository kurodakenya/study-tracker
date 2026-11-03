<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $certifications = $user->certifications()
            ->with('categories')
            ->get()
            ->map(function ($cert) {
                $totalMinutes = $cert->studyLogs()->sum('duration_minutes');
                return [
                    'id' => $cert->id,
                    'name' => $cert->name,
                    'exam_date' => $cert->exam_date?->format('Y-m-d'),
                    'status' => $cert->status,
                    'memo' => $cert->memo,
                    'total_minutes' => (int) $totalMinutes,
                    'total_hours' => round($totalMinutes / 60, 1),
                    'categories' => $cert->categories->map(fn ($cat) => [
                        'id' => $cat->id,
                        'name' => $cat->name,
                    ]),
                ];
            });

        return response()->json([
            'data' => $certifications,
            'summary' => [
                'total_certifications' => $certifications->count(),
                'total_minutes' => $certifications->sum('total_minutes'),
                'total_hours' => round($certifications->sum('total_minutes') / 60, 1),
            ],
        ]);
    }
}

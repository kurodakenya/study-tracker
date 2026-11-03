<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudyLogResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->year;
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);

        $days = $request->user()->studyLogs()
            ->whereYear('studied_on', $year)
            ->whereMonth('studied_on', $month)
            ->select(
                DB::raw("TO_CHAR(studied_on, 'YYYY-MM-DD') as date"),
                DB::raw('SUM(duration_minutes) as total_minutes'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('studied_on')
            ->orderBy('studied_on')
            ->get();

        return response()->json([
            'year' => (int) $year,
            'month' => (int) $request->month,
            'days' => $days,
        ]);
    }

    public function show(Request $request, string $date)
    {
        $request->merge(['date' => $date]);
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $logs = $request->user()->studyLogs()
            ->with(['certification', 'category'])
            ->where('studied_on', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        return StudyLogResource::collection($logs);
    }
}

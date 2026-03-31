<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkLog::where('user_id', Auth::id());

        if ($request->type) {
            $query->where('log_type', $request->type);
        }

        if ($request->month) {
            $query->whereYear('log_date', substr($request->month, 0, 4))
                  ->whereMonth('log_date', substr($request->month, 5, 2));
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('section_a_items', 'like', '%' . $request->search . '%')
                  ->orWhere('section_b_items', 'like', '%' . $request->search . '%');
            });
        }

        $logs = $query->orderBy('log_date', 'desc')->orderBy('log_type', 'asc')->paginate(6);

        $userId = Auth::id();
        $total     = WorkLog::where('user_id', $userId)->count();
        $thisWeek  = WorkLog::where('user_id', $userId)
            ->whereBetween('log_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        $dayStarts = WorkLog::where('user_id', $userId)->where('log_type', 'day_start')->count();
        $dayEnds   = WorkLog::where('user_id', $userId)->where('log_type', 'day_end')->count();
        $streak    = $this->calculateStreak($userId);

        $stats = compact('total', 'thisWeek', 'dayStarts', 'dayEnds', 'streak');
        $stats['this_week']  = $thisWeek;
        $stats['day_starts'] = $dayStarts;
        $stats['day_ends']   = $dayEnds;

        return view('logs.index', compact('logs', 'stats'));
    }

    public function create()
    {
        return view('logs.create');
    }

    /**
     * AJAX save — returns JSON, stays on same page
     */
    public function store(Request $request)
    {
        $request->validate([
            'log_type' => 'required|in:day_start,day_end',
            'log_date' => 'required|date',
        ]);

        $log = WorkLog::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'log_type' => $request->log_type,
                'log_date' => $request->log_date,
            ],
            [
                'section_a_items' => $request->section_a_items,
                'section_b_items' => $request->section_b_items,
                'generated_text'  => $request->generated_text,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'log_id' => $log->id, 'message' => 'Work log saved successfully!']);
        }

        return redirect()->route('logs.index')->with('success', 'Work log saved!');
    }

    public function show(WorkLog $log)
    {
        $this->authorize_log($log);

        $oppositeType = $log->log_type === 'day_start' ? 'day_end' : 'day_start';
        $relatedLog   = WorkLog::where('user_id', Auth::id())
            ->where('log_date', $log->log_date)
            ->where('log_type', $oppositeType)
            ->first();

        return view('logs.show', compact('log', 'relatedLog'));
    }

    public function edit(WorkLog $log)
    {
        $this->authorize_log($log);
        return view('logs.create', compact('log'));
    }

    /**
     * AJAX update — returns JSON, stays on same page
     */
    public function update(Request $request, WorkLog $log)
    {
        $this->authorize_log($log);

        $request->validate([
            'log_type' => 'required|in:day_start,day_end',
            'log_date' => 'required|date',
        ]);

        $log->update([
            'log_type'        => $request->log_type,
            'log_date'        => $request->log_date,
            'section_a_items' => $request->section_a_items,
            'section_b_items' => $request->section_b_items,
            'generated_text'  => $request->generated_text,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'log_id' => $log->id, 'message' => 'Work log updated successfully!']);
        }

        return redirect()->route('logs.index')->with('success', 'Log updated!');
    }

    public function destroy(WorkLog $log)
    {
        $this->authorize_log($log);
        $log->delete();
        return redirect()->route('logs.index')->with('success', 'Log deleted.');
    }

    /**
     * API: Smart fill — called on page load AND on date/type change
     *
     * Priority:
     * 1. If a log already exists for this exact date + type → return it (user's saved work)
     * 2. Otherwise fall back to predecessor logic (previous day end / today's day start)
     */
    public function smartFill(Request $request)
    {
        $type   = $request->type ?? 'day_start';
        $date   = $request->date ?? date('Y-m-d');
        $userId = Auth::id();

        // ── Priority 1: existing saved log for this date + type ──────────
        $existing = WorkLog::where('user_id', $userId)
            ->where('log_type', $type)
            ->where('log_date', $date)
            ->first();

        if ($existing) {
            return response()->json([
                'section_a' => json_decode($existing->section_a_items, true) ?? [],
                'section_b' => json_decode($existing->section_b_items, true) ?? [],
                'from_saved' => true,
            ]);
        }

        // ── Priority 2: predecessor logic (no saved log yet) ─────────────
        $sectionA = [];
        $sectionB = [];

        if ($type === 'day_start') {
            // Section A = previous Day End "Today I worked with"
            // Section B = previous Day End "Tomorrow I will work with"
            $prevDayEnd = WorkLog::where('user_id', $userId)
                ->where('log_type', 'day_end')
                ->where('log_date', '<', $date)
                ->orderBy('log_date', 'desc')
                ->first();

            if ($prevDayEnd) {
                $sectionA = json_decode($prevDayEnd->section_a_items, true) ?? [];
                $sectionB = json_decode($prevDayEnd->section_b_items, true) ?? [];
            }

        } elseif ($type === 'day_end') {
            // Section A = same day's Day Start "Today I will work with"
            $todayStart = WorkLog::where('user_id', $userId)
                ->where('log_type', 'day_start')
                ->where('log_date', $date)
                ->first();

            if ($todayStart) {
                $sectionA = json_decode($todayStart->section_b_items, true) ?? [];
            }
        }

        return response()->json(['section_a' => $sectionA, 'section_b' => $sectionB, 'from_saved' => false]);
    }

    /**
     * API: Fetch a specific day's log for smart-fill chips (yesterday / 2 days ago / 3 days ago)
     * Returns the SAME type log for that date, so chips load exactly that day's entries
     */
    public function fetchDay(Request $request)
    {
        $request->validate(['days_ago' => 'required|integer|min:1|max:30', 'type' => 'required|in:day_start,day_end']);

        $userId  = Auth::id();
        $type    = $request->type;
        $date    = Carbon::parse($request->date ?? today())->subDays($request->days_ago)->format('Y-m-d');

        $log = WorkLog::where('user_id', $userId)
            ->where('log_type', $type)
            ->where('log_date', $date)
            ->first();

        if (!$log) {
            return response()->json(['found' => false, 'date' => $date]);
        }

        return response()->json([
            'found'     => true,
            'date'      => $date,
            'section_a' => json_decode($log->section_a_items, true) ?? [],
            'section_b' => json_decode($log->section_b_items, true) ?? [],
        ]);
    }

    private function calculateStreak(int $userId): int
    {
        $dates = WorkLog::where('user_id', $userId)
            ->orderBy('log_date', 'desc')
            ->pluck('log_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        if ($dates->isEmpty()) return 0;

        $streak  = 0;
        $current = Carbon::today();

        foreach ($dates as $date) {
            if (Carbon::parse($date)->isSameDay($current) || Carbon::parse($date)->isSameDay($current->copy()->subDay())) {
                $streak++;
                $current = Carbon::parse($date)->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    private function authorize_log(WorkLog $log)
    {
        if ($log->user_id !== Auth::id()) abort(403);
    }
}
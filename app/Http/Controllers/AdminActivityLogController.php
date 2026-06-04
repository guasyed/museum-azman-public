<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user:id,name');

        $search = trim((string) $request->string('q'));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('description', 'like', $like)
                  ->orWhere('user_name', 'like', $like)
                  ->orWhere('action', 'like', $like)
                  ->orWhere('subject_label', 'like', $like);
            });
        }

        $action = trim((string) $request->string('action'));
        if ($action !== '') {
            $query->where('action', $action);
        }

        $userId = $request->integer('user_id');
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $actionOptions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'search', 'action', 'userId', 'actionOptions'));
    }
}

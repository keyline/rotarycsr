<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('causer_email', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'search' => $request->string('search')->toString(),
        ]);
    }
}

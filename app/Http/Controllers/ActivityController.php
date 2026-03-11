<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of activities for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $activities = Activity::with(['user:id,name'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($activities);
    }

    /**
     * Display the activity page.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $activities = Activity::with(['user:id,name'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return inertia('Activity/Index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Get recent activities for dashboard.
     */
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $activities = Activity::with(['user:id,name'])
            ->where('user_id', $user->id)
            ->recent()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user' => $activity->user->name,
                    'action' => $activity->description,
                    'time' => $activity->created_at->diffForHumans(),
                    'icon' => $activity->icon,
                    'color' => $activity->color,
                ];
            });

        return response()->json($activities);
    }
}

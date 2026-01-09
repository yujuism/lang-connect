<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\LearningRequest;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $languages = Language::where('is_active', true)->get();
        $recentRequests = LearningRequest::with(['user', 'language'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_sessions' => PracticeSession::count(),
            'active_members' => 0,
            'languages_available' => Language::where('is_active', true)->count(),
        ];

        return view('home', compact('languages', 'recentRequests', 'stats'));
    }

    public function members(Request $request)
    {
        $query = User::with(['progress', 'languages'])
            ->where('users.id', '!=', Auth::id()); // Exclude current user

        // Filter by language if provided
        if ($request->language_id) {
            $query->whereHas('languages', function($q) use ($request) {
                $q->where('languages.id', $request->language_id);
            });
        }

        // Search by name
        if ($request->search) {
            $query->where('users.name', 'like', '%' . $request->search . '%');
        }

        // Sort by karma points (most active members first)
        $query->leftJoin('user_progress', 'users.id', '=', 'user_progress.user_id')
            ->orderByDesc('user_progress.karma_points')
            ->orderBy('users.name')
            ->select('users.*');

        $users = $query->paginate(12);
        $languages = Language::orderBy('name')->get();

        return view('members', compact('users', 'languages'));
    }
}

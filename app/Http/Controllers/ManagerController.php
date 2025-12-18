<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function assessments(Request $request): View
    {
        $assessments = Assessment::query()
            ->with(['creator', 'translations'])
            ->when($request->get('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->get('type'), fn ($query, $type) => $query->where('type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('manager.assessments', [
            'assessments' => $assessments,
        ]);
    }

    public function participants(Request $request): View
    {
        // Managers can only view participants, not edit
        $participants = User::query()
            ->with('language')
            ->where('role', 'participant')
            ->when($request->get('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->get('department'), fn ($query, $dept) => $query->where('department', $dept))
            ->orderBy('full_name')
            ->orderBy('username')
            ->paginate(10)
            ->withQueryString();

        $departments = User::where('role', 'participant')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        return view('manager.participants', [
            'participants' => $participants,
            'departments' => $departments,
        ]);
    }
}





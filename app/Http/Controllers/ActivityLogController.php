<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    function get() {
        return ActivityLog::orderBy('created_at', 'desc')->take(20)->get();
    }

    public static function createActivityLog($description, $action) {
        $activityLog = new ActivityLog;
        $activityLog->description = $description;
        $activityLog->action = $action;
        $activityLog->user_id = Auth::id();
        $activityLog->save();
        return $activityLog;
    }
}

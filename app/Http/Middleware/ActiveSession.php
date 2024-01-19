<?php

namespace App\Http\Middleware;

use App\Models\Session;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActiveSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user !== null) {

            // Get the number of active sessions for the user
            $activeSessionsCount = Session::where('user_id', $user->id)->count();

            // Define the maximum allowed sessions
            $maxSessions = 3;

            // Check if the user has exceeded the maximum allowed sessions
            if ($activeSessionsCount > $maxSessions) {
                $sessionsToInvalidate = Session::where('user_id', $user->id)
                    ->orderBy('last_activity', 'asc')
                    ->limit($activeSessionsCount - $maxSessions ) // Number of sessions to invalidate
                    ->get();

                foreach ($sessionsToInvalidate as $session) {
                    $session->delete();
                }
            }
        }


        return $next($request);
    }
}

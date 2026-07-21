<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. ලොග් වී නැත්නම් කෙලින්ම Login පේජ් එකට යවයි
        if (! Auth::check()) {
            return redirect('/login');
        }

        // 2. ලොග් වී සිටින පරිශීලකයාගේ Role එක අපිට අවශ්‍ය එකට වඩා වෙනස් නම්
        if (Auth::user()->role !== $role) {

            // Admin කෙනෙක් වැරදීමකින් Couple පිටුවකට ආවොත් -> Admin Panel එකට හරවා යවයි
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.index')->withErrors(['error' => 'Admins cannot access couple dashboards.']);
            }

            // Couple කෙනෙක් වැරදීමකින් Admin පිටුවකට ආවොත් -> Couple Dashboard එකට හරවා යවයි
            return redirect()->route('dashboard')->withErrors(['error' => 'Couples cannot access administration panels.']);
        }

        return $next($request);
    }
}

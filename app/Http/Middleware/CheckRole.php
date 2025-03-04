<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
{
    if (!Auth::check()) {
        return redirect()->route('home')
            ->with('error', 'Vous devez être connecté pour accéder à cette page');
    }
    
    // Convertir le paramètre de rôle en tableau si plusieurs rôles sont spécifiés
    $allowedRoles = explode(',', $role);
    
    // Récupérer les rôles de l'utilisateur directement de la base de données
    $hasRole = DB::table('role_user')
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->where('role_user.user_id', Auth::id())
        ->whereIn('roles.name', $allowedRoles)
        ->exists();
    
    if (!$hasRole) {
        return redirect()->route('home')
            ->with('error', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page');
    }

    return $next($request);
}
}
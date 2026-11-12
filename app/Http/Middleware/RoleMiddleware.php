<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class RoleMiddleware {
 public function handle(Request $request, Closure $next, ...$roles): Response {
  abort_unless(Auth::check() && in_array(Auth::user()->role,$roles,true),403,'Anda tidak memiliki hak akses.');
  if(Auth::user()->role==='employee' && !Auth::user()->chat_enabled && $request->is('chat*')) abort(403);
  return $next($request);
 }
}

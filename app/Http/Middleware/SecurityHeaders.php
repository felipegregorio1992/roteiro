<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Headers de segurança essenciais
        $headers = config('security.headers');
        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }
        
        // Content Security Policy
        $csp = config('security.content_security_policy');
        $cspString = collect($csp)->map(function ($value, $key) {
            return "{$key} {$value}";
        })->implode('; ');
        
        $response->headers->set('Content-Security-Policy', $cspString);
        
        return $response;
    }
}

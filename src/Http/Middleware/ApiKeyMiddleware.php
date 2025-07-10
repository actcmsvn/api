<?php

namespace ACTCMS\Api\Http\Middleware;

use ACTCMS\Api\Facades\ApiHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! ApiHelper::hasApiKey()) {
            return $next($request);
        }

        $apiKey = ApiHelper::getApiKey();
        $requestApiKey = $request->header('X-API-KEY');

        if (! $requestApiKey || $requestApiKey !== $apiKey) {
            return response()->json([
                'message' => 'Khóa API không hợp lệ hoặc bị thiếu. Vui lòng cung cấp tiêu đề X-API-KEY hợp lệ.',
                'error' => 'Không được phép',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

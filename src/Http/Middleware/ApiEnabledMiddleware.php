<?php

namespace ACTCMS\Api\Http\Middleware;

use ACTCMS\Api\Facades\ApiHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiEnabledMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! ApiHelper::enabled()) {
            return response()->json([
                'error' => true,
                'message' => 'API hiện đang bị vô hiệu hóa. Vui lòng liên hệ với quản trị viên để bật quyền truy cập API.',
                'data' => null,
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}

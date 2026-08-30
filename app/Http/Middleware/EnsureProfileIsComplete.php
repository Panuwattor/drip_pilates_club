<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * คนที่สมัครผ่าน LINE จะยังไม่มีเบอร์โทร ต้องกรอกให้ครบก่อนถึงจะจองได้
 * กันไม่ให้พิมพ์ URL ข้ามหน้ากรอกข้อมูลไปเอง
 */
class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if ($customer && $customer->needsProfileCompletion()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'กรุณากรอกข้อมูลสมาชิกให้ครบก่อนใช้งาน',
                ], 403);
            }

            return redirect()->route('customer.profile.complete');
        }

        return $next($request);
    }
}

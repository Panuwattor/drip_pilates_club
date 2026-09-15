<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * คู่มือการใช้งานระบบ — เนื้อหาคงที่ทั้งหมดอยู่ในวิว
 * ภาพประกอบเก็บที่ public/docs/manual/img
 */
class ManualController extends Controller
{
    public function index()
    {
        return view('admin.manual.index');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function root()
    {
        return view('pages.root');
    }

    public function permissionDenied()
    {
        // 如果当前用户有权限访问后台，直接跳转访问
        if (config('administrator.permission')()) {
            return redirect(url(config('administrator.uri')), 302);
        }
        // 否则使用视图
        return view('pages.permission_denied');
    }

    public function test()
    {
        echo 'test1';
    }

    public function test2()
    {
        echo 'test2';
    }


    public function test3()
    {
        echo 'test3';
        echo 'add';
    }

    public function test4()
    {
        echo 'test4';
    }
}

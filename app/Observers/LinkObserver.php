<?php
/**
 * Created by PhpStorm.
 * User: pengpn
 * Date: 2018/7/18
 * Time: 下午3:50
 */

namespace App\Observers;


use App\Models\Link;
use Illuminate\Support\Facades\Cache;

class LinkObserver
{
    // 在保存时清空 cache_key 对应的缓存
    public function saved(Link $link)
    {
        Cache::forget($link->cache_key);
    }
}
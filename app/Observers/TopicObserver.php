<?php

namespace App\Observers;

use App\Handlers\SlugTranslateHandler;
use App\Jobs\TranslateSlug;
use App\Models\Topic;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver
{
    public function saving(Topic $topic)
    {
        $topic->body = clean($topic->body, 'user_topic_body');
        $topic->excerpt = make_excerpt($topic->body);

        // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
//        if (!$topic->slug) {
//            //$topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
//            // 推送任务到队列
//            dispatch(new TranslateSlug($topic));
//        }
    }

    /*
     * 队列系统对于构造器里传入的 Eloquent 模型，
     * 将会只序列化 ID 字段，因为我们是在 Topic 模型监控器的 saving() 方法中分发队列任务的，
     * 此时传参的 $topic 变量还未在数据库里创建，所以 $topic->id 为 null。
     * 所以将代码挪到saved
     */
    public function saved(Topic $topic)
    {
        // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
        if ( ! $topic->slug) {

            // 推送任务到队列
            dispatch(new TranslateSlug($topic));
        }
    }


}
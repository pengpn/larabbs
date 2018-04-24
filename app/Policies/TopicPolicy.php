<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Topic;

class TopicPolicy extends Policy
{
    public function update(User $user, Topic $topic)
    {
        //将这个重复代码写到model上封装起来，这样就写一遍就可以了。
//         return $topic->user_id == $user->id;
        return $user->isAuthorOf($topic);
    }

    public function destroy(User $user, Topic $topic)
    {
//        return $topic->user_id == $user->id;
        return $user->isAuthorOf($topic);
    }
}

<?php

namespace App\Actions;

use App\Models\Post;

class UpdatePost
{
    /**
     * Create a new class instance.
     */

    public function execute(Post $post, array $data)
    {
        return $post->update($data);
    }
}

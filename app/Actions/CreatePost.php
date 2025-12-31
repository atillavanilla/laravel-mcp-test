<?php

namespace App\Actions;

use App\Models\Post;

class CreatePost
{
    //
    protected Post $post;

    public function __construct(Post $post)
    {
        //
        $this->post = $post;
    }

    public function execute(array $data)
    {
        //
        return $this->post->create($data);
    }
}
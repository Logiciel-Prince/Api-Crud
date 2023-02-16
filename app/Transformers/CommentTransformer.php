<?php

namespace App\Transformers;

use App\Models\Comment;
use League\Fractal\TransformerAbstract;
use App\Transformers\PostTransformer;
use App\Transformers\UserTransformer;

class CommentTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        // 'post'
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'post',
        'user'
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Comment $com)
    {
        return [
            'Comment' => $com->message,
        ];
    }

    public function includePost(Comment $com){
        $transformer = $com->post;
        return $this->item($transformer, new PostTransformer);
    }

    public function includeUser(Comment $com){
        $transformer = $com->user;
        return $this->item($transformer, new UserTransformer);
    }
}

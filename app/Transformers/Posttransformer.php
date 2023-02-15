<?php

namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;

class Posttransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'category',
        'comment',
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Post $post)
    {
        return [
            'title' => $post->title,
            'desc' => $post->desc,
            'Image' => $post->image,
        ];
    }

    public function includeCategory(Post $post)
    {
        if(!$post->category->isEmpty())
        {
            $transformer =  Posttransformer::setDefaultIncludes(['category']);
            return $this->collection($post->category, $transformer);
        }
    }

    public function includeComment(Post $post)
    {
        if(!$post->comment->isEmpty())
        {
            $transformer =  Posttransformer::setDefaultIncludes(['comment']);
            return $this->collection($post->comment, $transformer);
        }
    }
}

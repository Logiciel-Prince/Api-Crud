<?php

namespace App\Transformers;

use App\Models\Post;
use App\Transformers\CategoryTransformer;
use App\Transformers\CommentTransformer;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
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
        'comments',
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Post $post)
    {
        // dd($post);
        return [
            'id' => $post->id,
            'title' => $post->title,
            'desc' => $post->desc,
            'Image' => $post->image,
        ];
    }

    public function includeCategory(Post $post)
    {
        if(!empty($post->category->toArray()))
        {
            $transformer =  $post->category;
            return $this->item($transformer, new CategoryTransformer);
        }
    }

    public function includeComments(Post $post)
    {
        if(!$post->comments->isEmpty())
        {
            return $this->collection($post->comments, new CommentTransformer);
        }
       
    }
}

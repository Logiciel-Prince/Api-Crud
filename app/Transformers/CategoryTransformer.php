<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Category;

class CategoryTransformer extends TransformerAbstract
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
        'children',
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Category $cat)
    {
        return [
            'id' => $cat->id,
            'title' => $cat->title,
            'parent_id' => $cat->parent_id,
        ];
    }

    public function includeChildren(Category $cat)
    {
        if(!$cat->children->isEmpty())
        {
            $transformer =  CategoryTransformer::setDefaultIncludes(['children']);
            return $this->collection($cat->children, $transformer);
        }
    }

   
}

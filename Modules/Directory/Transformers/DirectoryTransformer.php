<?php

namespace Modules\Directory\Transformers;

use Modules\Directory\Entities\Directory;
use League\Fractal\TransformerAbstract;

class DirectoryTransformer extends TransformerAbstract
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
        'parent'
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Directory $folder)
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'parent_id' => $folder->parent_id,
            'path' => $folder->path,
        ];
    }

    public function includeParent(Directory $folder)
    {
        if(!$folder->parent->isEmpty())
        {
            $transformer =  DirectoryTransformer::setDefaultIncludes(['parent']);
            return $this->collection($folder->parent, $transformer);
        }
    }
}

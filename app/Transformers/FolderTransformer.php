<?php

namespace App\Transformers;

use App\Models\Folder;
use League\Fractal\TransformerAbstract;

class FolderTransformer extends TransformerAbstract
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
        'children'
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Folder $folder)
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'parent_id' => $folder->parent_id,
            'path' => $folder->path,
        ];
    }

    public function includeChildren(Folder $folder)
    {
        if(!$folder->children->isEmpty())
        {
            $transformer =  FolderTransformer::setDefaultIncludes(['children']);
            return $this->collection($folder->children, $transformer);
        }
    }
}

<?php

namespace Luinuxscl\AiPosts\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Luinuxscl\AiPosts\Models\AiPost create(array $attributes = [])
 * @method static \Luinuxscl\AiPosts\Models\AiPost find(int $id)
 * @method static \Illuminate\Database\Eloquent\Collection all()
 * @method static \Luinuxscl\AiPosts\Services\AiPostService service()
 * 
 * @see \Luinuxscl\AiPosts\AiPosts
 */
class AiPosts extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ai-posts';
    }
}

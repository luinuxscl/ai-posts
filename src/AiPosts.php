<?php

namespace Luinuxscl\AiPosts;

use Luinuxscl\AiPosts\Models\AiPost;
use Luinuxscl\AiPosts\Services\AiPostService;

class AiPosts
{
    /**
     * Crear un nuevo post AI.
     *
     * @param array $attributes
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function create(array $attributes = [])
    {
        return AiPost::create($attributes);
    }

    /**
     * Encontrar un post AI por su ID.
     *
     * @param int $id
     * @return \Luinuxscl\AiPosts\Models\AiPost|null
     */
    public function find(int $id)
    {
        return AiPost::find($id);
    }

    /**
     * Obtener todos los posts AI.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return AiPost::all();
    }

    /**
     * Obtener una instancia del servicio AiPost.
     *
     * @return \Luinuxscl\AiPosts\Services\AiPostService
     */
    public function service()
    {
        return app('ai-posts.service');
    }
}

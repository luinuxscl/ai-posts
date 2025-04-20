<?php

namespace Luinuxscl\AiPosts\Http\Controllers;

use Illuminate\Http\Request;
use Luinuxscl\AiPosts\Models\AiPost;
use Luinuxscl\AiPosts\Http\Resources\AiPostResource;
use Luinuxscl\AiPosts\Http\Requests\StoreAiPostRequest;
use Luinuxscl\AiPosts\Http\Requests\UpdateAiPostRequest;

class AiPostController
{
    /**
     * Mostrar listado de posts.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $posts = AiPost::latest()->paginate(15);
        return AiPostResource::collection($posts);
    }

    /**
     * Almacenar un nuevo post.
     *
     * @param  \Luinuxscl\AiPosts\Http\Requests\StoreAiPostRequest  $request
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function store(StoreAiPostRequest $request)
    {
        $post = AiPost::create($request->validated());
        return new AiPostResource($post);
    }

    /**
     * Mostrar un post específico.
     *
     * @param  \Luinuxscl\AiPosts\Models\AiPost  $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function show(AiPost $aiPost)
    {
        return new AiPostResource($aiPost);
    }

    /**
     * Actualizar un post específico.
     *
     * @param  \Luinuxscl\AiPosts\Http\Requests\UpdateAiPostRequest  $request
     * @param  \Luinuxscl\AiPosts\Models\AiPost  $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function update(UpdateAiPostRequest $request, AiPost $aiPost)
    {
        $aiPost->update($request->validated());
        return new AiPostResource($aiPost);
    }

    /**
     * Eliminar un post específico.
     *
     * @param  \Luinuxscl\AiPosts\Models\AiPost  $aiPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiPost $aiPost)
    {
        $aiPost->delete();
        return response()->json(['message' => 'Post eliminado correctamente'], 200);
    }
}

<?php

namespace Luinuxscl\AiPosts\Http\Controllers;

use Illuminate\Http\Request;
use Luinuxscl\AiPosts\Models\AiPost;
use Luinuxscl\AiPosts\Http\Resources\AiPostResource;
use Luinuxscl\AiPosts\Services\AiPostService;
use Luinuxscl\AiPosts\Exceptions\InvalidStateTransitionException;

class AiPostTransitionController
{
    /**
     * El servicio de posts.
     *
     * @var \Luinuxscl\AiPosts\Services\AiPostService
     */
    protected $postService;

    /**
     * Constructor.
     *
     * @param \Luinuxscl\AiPosts\Services\AiPostService $postService
     */
    public function __construct(AiPostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Avanzar al siguiente estado.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function advance(AiPost $aiPost)
    {
        try {
            $this->postService->advance($aiPost);
            return new AiPostResource($aiPost);
        } catch (InvalidStateTransitionException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Establecer el título del post.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function setTitle(Request $request, AiPost $aiPost)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $this->postService->setTitle($aiPost, $request->input('title'));
        return new AiPostResource($aiPost);
    }

    /**
     * Establecer el contenido del post.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function setContent(Request $request, AiPost $aiPost)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $this->postService->setContent($aiPost, $request->input('content'));
        return new AiPostResource($aiPost);
    }

    /**
     * Establecer el resumen del post.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function setSummary(Request $request, AiPost $aiPost)
    {
        $request->validate([
            'summary' => 'required|string|max:1000',
        ]);

        $this->postService->setSummary($aiPost, $request->input('summary'));
        return new AiPostResource($aiPost);
    }

    /**
     * Generar automáticamente un resumen.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function generateSummary(AiPost $aiPost)
    {
        $this->postService->generateSummary($aiPost);
        return new AiPostResource($aiPost);
    }

    /**
     * Establecer el prompt para la generación de imagen.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function setImagePrompt(Request $request, AiPost $aiPost)
    {
        $request->validate([
            'image_prompt' => 'required|string|max:1000',
        ]);

        $this->postService->setImagePrompt($aiPost, $request->input('image_prompt'));
        return new AiPostResource($aiPost);
    }

    /**
     * Marcar el post como exportado.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Luinuxscl\AiPosts\Http\Resources\AiPostResource
     */
    public function markAsExported(AiPost $aiPost)
    {
        try {
            $this->postService->markAsExported($aiPost);
            return new AiPostResource($aiPost);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Obtener las transiciones disponibles para un post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $aiPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTransitions(AiPost $aiPost)
    {
        $transitions = $this->postService->getAvailableTransitions($aiPost);
        return response()->json([
            'data' => $transitions,
            'post' => new AiPostResource($aiPost),
        ]);
    }
}

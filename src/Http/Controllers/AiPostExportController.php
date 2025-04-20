<?php

namespace Luinuxscl\AiPosts\Http\Controllers;

use Illuminate\Http\Request;
use Luinuxscl\AiPosts\Models\AiPost;
use Luinuxscl\AiPosts\Services\ExportService;
use Luinuxscl\AiPosts\Http\Resources\AiPostResource;

class AiPostExportController
{
    /**
     * El servicio de exportación.
     *
     * @var \Luinuxscl\AiPosts\Services\ExportService
     */
    protected $exportService;

    /**
     * Constructor.
     *
     * @param \Luinuxscl\AiPosts\Services\ExportService $exportService
     */
    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Obtener los posts listos para exportar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getReadyPosts(Request $request)
    {
        $onlyUnexported = $request->boolean('only_unexported', true);
        $posts = $this->exportService->getPostsReadyToExport($onlyUnexported);
        
        return AiPostResource::collection($posts);
    }

    /**
     * Exportar posts como JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportAsJson(Request $request)
    {
        $onlyUnexported = $request->boolean('only_unexported', true);
        $includeMetadata = $request->boolean('include_metadata', true);
        $prettyPrint = $request->boolean('pretty_print', true);
        $markAsExported = $request->boolean('mark_as_exported', false);
        
        $posts = $this->exportService->getPostsReadyToExport($onlyUnexported);
        
        if ($posts->isEmpty()) {
            return response()->json([
                'message' => 'No hay posts disponibles para exportar'
            ], 404);
        }
        
        $json = $this->exportService->exportToJson($posts, $includeMetadata, $prettyPrint);
        
        if ($markAsExported) {
            $this->exportService->markBatchAsExported($posts);
        }
        
        return response($json)->header('Content-Type', 'application/json');
    }

    /**
     * Marcar un lote de posts como exportados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markBatchAsExported(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'integer|exists:' . (new AiPost)->getTable() . ',id',
        ]);
        
        $postIds = $request->input('post_ids', []);
        $posts = AiPost::whereIn('id', $postIds)
                      ->where('status', AiPost::STATUS_READY_TO_PUBLISH)
                      ->get();
        
        $count = $this->exportService->markBatchAsExported($posts);
        
        return response()->json([
            'success' => true,
            'message' => "Se marcaron {$count} posts como exportados",
            'count' => $count,
            'posts' => AiPostResource::collection($posts)
        ]);
    }

    /**
     * Filtrar posts por varios criterios.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function filter(Request $request)
    {
        $query = AiPost::query();
        
        // Filtrar por status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filtrar por exportados/no exportados
        if ($request->boolean('exported', false)) {
            $query->whereNotNull('exported_at');
        } elseif ($request->boolean('unexported', false)) {
            $query->whereNull('exported_at');
        }
        
        // Filtrar por fecha de creación
        if ($request->has('created_after')) {
            $query->where('created_at', '>=', $request->input('created_after'));
        }
        
        if ($request->has('created_before')) {
            $query->where('created_at', '<=', $request->input('created_before'));
        }
        
        // Buscar por título o contenido
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Ordenar
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        if (in_array($sortBy, ['id', 'title', 'created_at', 'updated_at', 'exported_at'])) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }
        
        // Paginación
        $perPage = $request->input('per_page', 15);
        
        return AiPostResource::collection($query->paginate($perPage));
    }
}

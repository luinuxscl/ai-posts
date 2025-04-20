<?php

namespace Luinuxscl\AiPosts\Services;

use Luinuxscl\AiPosts\Models\AiPost;
use Illuminate\Database\Eloquent\Collection;

class ExportService
{
    /**
     * Obtener todos los posts que están listos para exportar y no han sido exportados aún.
     *
     * @param  bool  $onlyUnexported  Si solo se deben incluir posts no exportados
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPostsReadyToExport(bool $onlyUnexported = true): Collection
    {
        $query = AiPost::where('status', AiPost::STATUS_READY_TO_PUBLISH);
        
        if ($onlyUnexported) {
            $query->whereNull('exported_at');
        }
        
        return $query->latest()->get();
    }
    
    /**
     * Exportar un conjunto de posts a formato JSON.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array  $posts
     * @param  bool  $includeMetadata  Si se deben incluir los metadatos
     * @param  bool  $prettyPrint  Si el JSON debe tener formato legible
     * @return string  JSON con los posts exportados
     */
    public function exportToJson($posts, bool $includeMetadata = true, bool $prettyPrint = false): string
    {
        $options = $prettyPrint ? JSON_PRETTY_PRINT : 0;
        
        if ($posts instanceof Collection) {
            $data = $posts->map(function ($post) use ($includeMetadata) {
                return $post->toExportArray($includeMetadata);
            })->all();
        } else {
            $data = collect($posts)->map(function ($post) use ($includeMetadata) {
                return $post->toExportArray($includeMetadata);
            })->all();
        }
        
        return json_encode(['posts' => $data], $options);
    }
    
    /**
     * Exportar todos los posts listos para publicar a formato JSON.
     *
     * @param  bool  $onlyUnexported  Si solo se deben incluir posts no exportados
     * @param  bool  $includeMetadata  Si se deben incluir los metadatos
     * @param  bool  $prettyPrint  Si el JSON debe tener formato legible
     * @return string  JSON con los posts exportados
     */
    public function exportReadyPosts(bool $onlyUnexported = true, bool $includeMetadata = true, bool $prettyPrint = false): string
    {
        $posts = $this->getPostsReadyToExport($onlyUnexported);
        return $this->exportToJson($posts, $includeMetadata, $prettyPrint);
    }
    
    /**
     * Marcar un conjunto de posts como exportados.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array  $posts
     * @return int  Número de posts marcados como exportados
     */
    public function markBatchAsExported($posts): int
    {
        $now = now();
        $count = 0;
        
        if ($posts instanceof Collection) {
            $posts->each(function ($post) use ($now, &$count) {
                if ($post->status === AiPost::STATUS_READY_TO_PUBLISH) {
                    $post->exported_at = $now;
                    $post->save();
                    $count++;
                }
            });
        } else {
            foreach ($posts as $post) {
                if ($post->status === AiPost::STATUS_READY_TO_PUBLISH) {
                    $post->exported_at = $now;
                    $post->save();
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Exportar un lote de posts a un archivo JSON y marcarlos como exportados.
     *
     * @param  string  $filePath  Ruta donde guardar el archivo JSON
     * @param  bool  $onlyUnexported  Si solo se deben incluir posts no exportados
     * @param  bool  $includeMetadata  Si se deben incluir los metadatos
     * @param  bool  $prettyPrint  Si el JSON debe tener formato legible
     * @return array  Información sobre la exportación
     */
    public function exportBatchToFile(string $filePath, bool $onlyUnexported = true, bool $includeMetadata = true, bool $prettyPrint = true): array
    {
        $posts = $this->getPostsReadyToExport($onlyUnexported);
        
        if ($posts->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No hay posts listos para exportar',
                'count' => 0
            ];
        }
        
        $json = $this->exportToJson($posts, $includeMetadata, $prettyPrint);
        
        // Asegurar el directorio
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $result = file_put_contents($filePath, $json);
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Error al escribir en el archivo',
                'count' => 0
            ];
        }
        
        $count = $this->markBatchAsExported($posts);
        
        return [
            'success' => true,
            'message' => "Se exportaron {$count} posts al archivo {$filePath}",
            'count' => $count,
            'file_path' => $filePath
        ];
    }
}

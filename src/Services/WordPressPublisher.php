<?php

namespace Luinuxscl\AiPosts\Services;

use Luinuxscl\AiPosts\Models\AiPost;
use Illuminate\Support\Facades\Http;

class WordPressPublisher
{
    /**
     * Configuración de WordPress.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Publicar un post en WordPress.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return int El ID del post en WordPress
     * @throws \Exception Si hay un error al publicar
     */
    public function publish(AiPost $post)
    {
        try {
            $endpoint = $this->config['endpoint'] . '/posts';
            
            // Autenticación básica o con Application Password
            $response = Http::withBasicAuth(
                $this->config['username'],
                $this->config['application_password'] ?? $this->config['password']
            )->post($endpoint, [
                'title' => $post->title,
                'content' => $post->content,
                'excerpt' => $post->summary,
                'status' => 'publish',
                'meta' => $post->metadata ?? [],
                // Si hay una imagen destacada, habría que subirla primero y luego referenciarla
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['id'] ?? 0;
            }

            throw new \Exception('Error al publicar en WordPress: ' . $response->body());
        } catch (\Exception $e) {
            // En un entorno de producción, deberíamos manejar esto mejor
            // Por ahora, simplemente reenviamos la excepción
            throw $e;
        }
    }

    /**
     * Subir una imagen destacada a WordPress.
     *
     * @param string $imageUrl URL de la imagen a subir
     * @return int|null ID del adjunto en WordPress o null si falló
     */
    public function uploadFeaturedImage(string $imageUrl)
    {
        // Esta implementación es un placeholder
        // En una implementación real, descargaríamos la imagen y la subiríamos a WordPress
        return null;
    }

    /**
     * Actualizar un post existente en WordPress.
     *
     * @param int $wordpressPostId
     * @param array $data
     * @return bool
     */
    public function update(int $wordpressPostId, array $data)
    {
        try {
            $endpoint = $this->config['endpoint'] . '/posts/' . $wordpressPostId;
            
            $response = Http::withBasicAuth(
                $this->config['username'],
                $this->config['application_password'] ?? $this->config['password']
            )->put($endpoint, $data);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Eliminar un post de WordPress.
     *
     * @param int $wordpressPostId
     * @return bool
     */
    public function delete(int $wordpressPostId)
    {
        try {
            $endpoint = $this->config['endpoint'] . '/posts/' . $wordpressPostId;
            
            $response = Http::withBasicAuth(
                $this->config['username'],
                $this->config['application_password'] ?? $this->config['password']
            )->delete($endpoint);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener las categorías disponibles en WordPress.
     *
     * @return array
     */
    public function getCategories()
    {
        try {
            $endpoint = $this->config['endpoint'] . '/categories';
            
            $response = Http::withBasicAuth(
                $this->config['username'],
                $this->config['application_password'] ?? $this->config['password']
            )->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener las etiquetas disponibles en WordPress.
     *
     * @return array
     */
    public function getTags()
    {
        try {
            $endpoint = $this->config['endpoint'] . '/tags';
            
            $response = Http::withBasicAuth(
                $this->config['username'],
                $this->config['application_password'] ?? $this->config['password']
            )->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}

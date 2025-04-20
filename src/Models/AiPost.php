<?php

namespace Luinuxscl\AiPosts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Luinuxscl\AiPosts\Services\AiPostService;

class AiPost extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'summary',
        'image_prompt',
        'featured_image',
        'status',
        'metadata',
        'exported_at',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'exported_at' => 'datetime',
    ];

    /**
     * Las constantes que definen los posibles estados.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_TITLE_CREATED = 'title_created';
    public const STATUS_CONTENT_CREATED = 'content_created';
    public const STATUS_SUMMARY_CREATED = 'summary_created';
    public const STATUS_IMAGE_PROMPT_CREATED = 'image_prompt_created';
    public const STATUS_READY_TO_PUBLISH = 'ready_to_publish';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = self::STATUS_DRAFT;
            }
            
            if (empty($model->metadata)) {
                $model->metadata = [];
            }
        });
    }

    /**
     * Obtener el nombre de la tabla.
     *
     * @return string
     */
    public function getTable()
    {
        return config('ai-posts.table_names.posts', 'ai_posts');
    }

    /**
     * Establecer el título del post.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        app('ai-posts.service')->setTitle($this, $title);
        return $this;
    }

    /**
     * Establecer el contenido del post.
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content)
    {
        app('ai-posts.service')->setContent($this, $content);
        return $this;
    }

    /**
     * Establecer o generar el resumen del post.
     *
     * @param string|null $summary
     * @return $this
     */
    public function setSummary(?string $summary = null)
    {
        app('ai-posts.service')->setSummary($this, $summary);
        return $this;
    }

    /**
     * Generar automáticamente un resumen basado en el contenido.
     *
     * @return $this
     */
    public function generateSummary()
    {
        app('ai-posts.service')->generateSummary($this);
        return $this;
    }

    /**
     * Establecer el prompt para la generación de imagen.
     *
     * @param string $prompt
     * @return $this
     */
    public function setImagePrompt(string $prompt)
    {
        app('ai-posts.service')->setImagePrompt($this, $prompt);
        return $this;
    }

    /**
     * Establecer la URL de la imagen destacada.
     *
     * @param string $url
     * @return $this
     */
    public function setFeaturedImage(string $url)
    {
        app('ai-posts.service')->setFeaturedImage($this, $url);
        return $this;
    }

    /**
     * Establecer los metadatos del post.
     *
     * @param array $metadata
     * @return $this
     */
    public function setMetadata(array $metadata)
    {
        app('ai-posts.service')->setMetadata($this, $metadata);
        return $this;
    }

    /**
     * Avanzar al siguiente estado.
     *
     * @return $this
     */
    public function advance()
    {
        app('ai-posts.service')->advance($this);
        return $this;
    }

    /**
     * Marcar el post como exportado.
     *
     * @return $this
     */
    public function markAsExported()
    {
        app('ai-posts.service')->markAsExported($this);
        return $this;
    }

    /**
     * Verificar si el post puede avanzar al siguiente estado.
     *
     * @return bool
     */
    public function canAdvance()
    {
        return app('ai-posts.service')->canAdvance($this);
    }

    /**
     * Obtener el siguiente estado disponible.
     *
     * @return string|null
     */
    public function getNextState()
    {
        return app('ai-posts.service')->getNextState($this);
    }

    /**
     * Obtener las transiciones disponibles para este post.
     *
     * @return array
     */
    public function getAvailableTransitions()
    {
        return app('ai-posts.service')->getAvailableTransitions($this);
    }
    
    /**
     * Exportar el post a formato JSON.
     *
     * @param bool $includeMetadata Si se deben incluir los metadatos
     * @return array
     */
    public function toExportArray(bool $includeMetadata = true): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'image_prompt' => $this->image_prompt,
            'featured_image' => $this->featured_image,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'exported_at' => $this->exported_at?->toIso8601String(),
        ];
        
        if ($includeMetadata && !empty($this->metadata)) {
            $data['metadata'] = $this->metadata;
        }
        
        return $data;
    }
    
    /**
     * Exportar el post a una cadena JSON.
     *
     * @param bool $includeMetadata Si se deben incluir los metadatos
     * @param int $options Opciones JSON para json_encode
     * @return string
     */
    public function toJson($includeMetadata = true, $options = 0): string
    {
        return json_encode($this->toExportArray($includeMetadata), $options);
    }
}

<?php

namespace Luinuxscl\AiPosts\Services;

use Luinuxscl\AiPosts\Models\AiPost;
use Luinuxscl\AiPosts\Exceptions\InvalidStateTransitionException;
use Illuminate\Support\Str;
use Luinuxscl\AiPosts\Events\AiPostStateChanged;
use Luinuxscl\AiPosts\Events\AiPostTitleCreated;
use Luinuxscl\AiPosts\Events\AiPostContentCreated;
use Luinuxscl\AiPosts\Events\AiPostSummaryCreated;
use Luinuxscl\AiPosts\Events\AiPostImagePromptCreated;
use Luinuxscl\AiPosts\Events\AiPostReadyToPublish;
use Luinuxscl\AiPosts\Events\AiPostMarkedAsExported;

class AiPostService
{
    /**
     * La máquina de estados.
     *
     * @var \Luinuxscl\AiPosts\Services\StateMachine
     */
    protected $stateMachine;

    /**
     * Constructor.
     *
     * @param \Luinuxscl\AiPosts\Services\StateMachine $stateMachine
     */
    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    /**
     * Establecer el título del post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string $title
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function setTitle(AiPost $post, string $title)
    {
        $post->title = $title;
        $post->save();

        return $post;
    }

    /**
     * Establecer el contenido del post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string $content
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function setContent(AiPost $post, string $content)
    {
        $post->content = $content;
        $post->save();

        return $post;
    }

    /**
     * Establecer el resumen del post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string|null $summary
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function setSummary(AiPost $post, ?string $summary = null)
    {
        $post->summary = $summary ?? $this->generateSummaryFromContent($post);
        $post->save();

        return $post;
    }

    /**
     * Generar automáticamente un resumen basado en el contenido.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function generateSummary(AiPost $post)
    {
        $post->summary = $this->generateSummaryFromContent($post);
        $post->save();

        return $post;
    }

    /**
     * Generar un resumen a partir del contenido.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return string
     */
    protected function generateSummaryFromContent(AiPost $post)
    {
        if (empty($post->content)) {
            return '';
        }

        $maxLength = config('ai-posts.auto_generation.summary.max_length', 150);
        
        // Método simple: tomar las primeras frases
        $summary = Str::limit(strip_tags($post->content), $maxLength);
        
        return $summary;
    }

    /**
     * Establecer el prompt para la generación de imagen.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string $prompt
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function setImagePrompt(AiPost $post, string $prompt)
    {
        $post->image_prompt = $prompt;
        $post->save();

        return $post;
    }

    /**
     * Establecer la URL de la imagen destacada.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string $url
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function setFeaturedImage(AiPost $post, string $url)
    {
        $post->featured_image = $url;
        $post->save();

        return $post;
    }

    /**
     * Establecer los metadatos del post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param array $metadata
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function setMetadata(AiPost $post, array $metadata)
    {
        $post->metadata = array_merge($post->metadata ?? [], $metadata);
        $post->save();

        return $post;
    }

    /**
     * Avanzar al siguiente estado.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return \Luinuxscl\AiPosts\Models\AiPost
     * @throws \Luinuxscl\AiPosts\Exceptions\InvalidStateTransitionException
     */
    public function advance(AiPost $post)
    {
        $nextState = $this->stateMachine->getNextState($post->status);

        if ($nextState === null) {
            throw new InvalidStateTransitionException(
                "No hay un estado siguiente para '{$post->status}'"
            );
        }

        $this->validateTransition($post, $nextState);

        $oldState = $post->status;
        $post->status = $nextState;
        $post->save();
        
        // Disparar evento genérico para cualquier cambio de estado
        event(new AiPostStateChanged($post, $oldState, $nextState));
        
        // Disparar eventos específicos según el nuevo estado
        $this->fireStateSpecificEvent($post, $nextState);

        return $post;
    }

    /**
     * Marcar el post como exportado.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return \Luinuxscl\AiPosts\Models\AiPost
     */
    public function markAsExported(AiPost $post)
    {
        if ($post->status !== AiPost::STATUS_READY_TO_PUBLISH) {
            throw new InvalidStateTransitionException(
                "El post debe estar en estado 'ready_to_publish' para ser marcado como exportado"
            );
        }

        $post->exported_at = now();
        $post->save();
        
        // Disparar evento para post marcado como exportado
        event(new AiPostMarkedAsExported($post));

        return $post;
    }

    /**
     * Verificar si el post puede avanzar al siguiente estado.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return bool
     */
    public function canAdvance(AiPost $post)
    {
        $nextState = $this->stateMachine->getNextState($post->status);

        if ($nextState === null) {
            return false;
        }

        try {
            $this->validateTransition($post, $nextState);
            return true;
        } catch (InvalidStateTransitionException $e) {
            return false;
        }
    }

    /**
     * Obtener el siguiente estado disponible.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return string|null
     */
    public function getNextState(AiPost $post)
    {
        return $this->stateMachine->getNextState($post->status);
    }

    /**
     * Obtener las transiciones disponibles para este post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return array
     */
    public function getAvailableTransitions(AiPost $post)
    {
        $transitions = [];
        $nextState = $this->stateMachine->getNextState($post->status);

        if ($nextState !== null) {
            $transitions[] = [
                'action' => 'advance',
                'target_state' => $nextState,
                'name' => "Avanzar a {$this->stateMachine->getStateName($nextState)}",
            ];
        }

        // Añadir otras acciones disponibles según el estado actual
        switch ($post->status) {
            case AiPost::STATUS_DRAFT:
                $transitions[] = [
                    'action' => 'setTitle',
                    'name' => 'Establecer título',
                ];
                break;

            case AiPost::STATUS_TITLE_CREATED:
                $transitions[] = [
                    'action' => 'setContent',
                    'name' => 'Establecer contenido',
                ];
                break;

            case AiPost::STATUS_CONTENT_CREATED:
                $transitions[] = [
                    'action' => 'setSummary',
                    'name' => 'Establecer resumen',
                ];
                $transitions[] = [
                    'action' => 'generateSummary',
                    'name' => 'Generar resumen automáticamente',
                ];
                break;

            case AiPost::STATUS_SUMMARY_CREATED:
                $transitions[] = [
                    'action' => 'setImagePrompt',
                    'name' => 'Establecer prompt para imagen',
                ];
                break;

            case AiPost::STATUS_READY_TO_PUBLISH:
                $transitions[] = [
                    'action' => 'markAsExported',
                    'name' => 'Marcar como exportado',
                ];
                break;
        }

        return $transitions;
    }

    /**
     * Validar si una transición es válida.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string $targetState
     * @return bool
     * @throws \Luinuxscl\AiPosts\Exceptions\InvalidStateTransitionException
     */
    /**
     * Disparar eventos específicos según el estado al que ha cambiado el post.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string $state
     * @return void
     */
    protected function fireStateSpecificEvent(AiPost $post, string $state): void
    {
        switch ($state) {
            case AiPost::STATUS_TITLE_CREATED:
                event(new AiPostTitleCreated($post));
                break;
                
            case AiPost::STATUS_CONTENT_CREATED:
                event(new AiPostContentCreated($post));
                break;
                
            case AiPost::STATUS_SUMMARY_CREATED:
                event(new AiPostSummaryCreated($post));
                break;
                
            case AiPost::STATUS_IMAGE_PROMPT_CREATED:
                event(new AiPostImagePromptCreated($post));
                break;
                
            case AiPost::STATUS_READY_TO_PUBLISH:
                event(new AiPostReadyToPublish($post));
                break;
        }
    }
    
    protected function validateTransition(AiPost $post, string $targetState)
    {
        if (!$this->stateMachine->canTransition($post->status, $targetState)) {
            throw new InvalidStateTransitionException(
                "No se puede hacer la transición desde '{$post->status}' a '{$targetState}'"
            );
        }

        // Validar que el post tenga los datos necesarios para la transición
        switch ($targetState) {
            case AiPost::STATUS_TITLE_CREATED:
                if (empty($post->title)) {
                    throw new InvalidStateTransitionException(
                        "El post debe tener un título para avanzar a 'title_created'"
                    );
                }
                break;

            case AiPost::STATUS_CONTENT_CREATED:
                if (empty($post->content)) {
                    throw new InvalidStateTransitionException(
                        "El post debe tener contenido para avanzar a 'content_created'"
                    );
                }
                break;

            case AiPost::STATUS_SUMMARY_CREATED:
                if (empty($post->summary)) {
                    throw new InvalidStateTransitionException(
                        "El post debe tener un resumen para avanzar a 'summary_created'"
                    );
                }
                break;

            case AiPost::STATUS_IMAGE_PROMPT_CREATED:
                if (empty($post->image_prompt)) {
                    throw new InvalidStateTransitionException(
                        "El post debe tener un prompt para imagen para avanzar a 'image_prompt_created'"
                    );
                }
                break;
        }

        return true;
    }
}

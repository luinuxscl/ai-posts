<?php

namespace Luinuxscl\AiPosts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Luinuxscl\AiPosts\Models\AiPost;

class AiPostStateChanged
{
    use Dispatchable, SerializesModels;

    /**
     * El post que ha cambiado de estado.
     *
     * @var \Luinuxscl\AiPosts\Models\AiPost
     */
    public $post;

    /**
     * El estado anterior del post.
     *
     * @var string|null
     */
    public $oldState;

    /**
     * El nuevo estado del post.
     *
     * @var string
     */
    public $newState;

    /**
     * Crear una nueva instancia del evento.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @param string|null $oldState
     * @param string $newState
     * @return void
     */
    public function __construct(AiPost $post, ?string $oldState, string $newState)
    {
        $this->post = $post;
        $this->oldState = $oldState;
        $this->newState = $newState;
    }
}

<?php

namespace Luinuxscl\AiPosts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Luinuxscl\AiPosts\Models\AiPost;

class AiPostReadyToPublish
{
    use Dispatchable, SerializesModels;

    /**
     * El post que está listo para publicar.
     *
     * @var \Luinuxscl\AiPosts\Models\AiPost
     */
    public $post;

    /**
     * Crear una nueva instancia del evento.
     *
     * @param \Luinuxscl\AiPosts\Models\AiPost $post
     * @return void
     */
    public function __construct(AiPost $post)
    {
        $this->post = $post;
    }
}

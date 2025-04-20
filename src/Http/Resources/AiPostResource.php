<?php

namespace Luinuxscl\AiPosts\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'image_prompt' => $this->image_prompt,
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'exported_at' => $this->exported_at,
            'next_actions' => $this->whenLoaded('nextActions', function () {
                return $this->getAvailableTransitions();
            }),
            'links' => [
                'self' => route('ai-posts.show', $this->id),
                'advance' => route('ai-posts.advance', $this->id),
                'transitions' => route('ai-posts.transitions', $this->id),
            ]
        ];
    }
}

<?php

namespace Luinuxscl\AiPosts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'summary' => 'sometimes|string|max:1000',
            'image_prompt' => 'sometimes|string|max:1000',
            'featured_image' => 'sometimes|string|max:2048',
            'metadata' => 'sometimes|array',
            'status' => 'sometimes|string|in:draft,title_created,content_created,summary_created,image_prompt_created,ready_to_publish,published',
        ];
    }
}

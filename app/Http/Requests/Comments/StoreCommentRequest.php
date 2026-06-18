<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {

        if ($this->file('file')) {
            $this->merge([
                'type' => 'voice',
            ]);
        }
    }

    public function rules(): array
    {
        if ($this->file('file')) {
            return [
                'commentable_type' => ['required', 'string'],
                'commentable_id' => ['required', 'integer'],
                'type' => ['required', 'string', 'in:voice'],
                'file' => [
                    'required',
                    'file',
                    'max:51200',
                    'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/x-m4a,audio/wav,video/webm',
                ],
                'duration_seconds' => ['sometimes', 'integer', 'min:0', 'max:30'],
            ];
        } else {
            return [
                'commentable_type' => ['required', 'string'],
                'commentable_id' => ['required', 'integer'],
                'type' => ['required', 'string', 'in:text'],
                'body' => ['required', 'string'],
            ];
        }
        

    }


}

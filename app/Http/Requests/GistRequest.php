<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'required|string|max:1048576', // 1MB limit
            'language' => 'required|string|max:50',
            'filename' => 'nullable|string|max:255',
            'is_public' => 'boolean',
            'sync_to_github' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => '标题',
            'description' => '描述',
            'content' => '内容',
            'language' => '编程语言',
            'filename' => '文件名',
            'is_public' => '可见性',
            'sync_to_github' => '同步到 GitHub',
            'tags' => '标签',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过 255 个字符',
            'content.required' => '内容不能为空',
            'content.max' => '内容不能超过 1MB',
            'language.required' => '请选择编程语言',
            'description.max' => '描述不能超过 1000 个字符',
            'filename.max' => '文件名不能超过 255 个字符',
            'tags.*.max' => '标签名称不能超过 50 个字符',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // 如果没有提供文件名，根据语言生成默认文件名
        if (!$this->filename && $this->language) {
            $extensions = [
                'php' => 'php',
                'javascript' => 'js',
                'python' => 'py',
                'html' => 'html',
                'css' => 'css',
                'java' => 'java',
                'c' => 'c',
                'cpp' => 'cpp',
                'ruby' => 'rb',
                'go' => 'go',
                'rust' => 'rs',
                'swift' => 'swift',
                'sql' => 'sql',
                'shell' => 'sh',
                'markdown' => 'md',
                'json' => 'json',
                'xml' => 'xml',
                'yaml' => 'yml',
            ];

            $extension = $extensions[strtolower($this->language)] ?? 'txt';
            $this->merge([
                'filename' => 'gist.' . $extension
            ]);
        }

        // 处理标签数组
        if ($this->tags && is_string($this->tags)) {
            $tags = array_filter(array_map('trim', explode(',', $this->tags)));
            $this->merge(['tags' => $tags]);
        }
    }
}

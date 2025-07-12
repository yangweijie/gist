<?php

namespace App\View\Components;

use App\Models\Tag;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TagSelector extends Component
{
    public string $name;
    public array $selectedTags;
    public bool $multiple;
    public bool $allowCreate;
    public string $placeholder;
    public int $maxTags;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name = 'tags',
        array $selectedTags = [],
        bool $multiple = true,
        bool $allowCreate = true,
        string $placeholder = '选择或创建标签...',
        int $maxTags = 10
    ) {
        $this->name = $name;
        $this->selectedTags = $selectedTags;
        $this->multiple = $multiple;
        $this->allowCreate = $allowCreate;
        $this->placeholder = $placeholder;
        $this->maxTags = $maxTags;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $popularTags = Tag::getPopularTags(20);

        return view('components.tag-selector', [
            'popularTags' => $popularTags
        ]);
    }
}

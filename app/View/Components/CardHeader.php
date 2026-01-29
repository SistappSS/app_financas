<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardHeader extends Component
{
    public $prevRoute;
    public $title;
    public $iconRight;
    public $description;

    public function __construct($prevRoute, $title, $iconRight = null, $description = null)
    {
        $this->prevRoute = $prevRoute;
        $this->title = $title;
        $this->iconRight = $iconRight;
        $this->description = $description;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.card-header');
    }
}

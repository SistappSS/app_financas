<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Select extends Component
{
    public $col;
    public $set;
    public $title;
    public $id;
    public $name;
    public $disabled;

    public function __construct($col, $title, $name, $id, $set = null, bool $disabled = false)
    {
        $this->col = $col;
        $this->title = $title;
        $this->name = $name;
        $this->id = $id;
        $this->set = $set;
        $this->disabled = $disabled;
    }

    public function render(): View|Closure|string
    {
        return view('components.select');
    }
}

<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputCheck extends Component
{
    public $col;
    public $set;
    public $title;
    public $id;
    public $name;
    public $checked;
    public $disabled;
    public $value;

    public function __construct($col, $title, $name, $id, $value = null, $set = null, bool $checked = true, bool $disabled = true)
    {
        $this->col = $col;
        $this->title = $title;
        $this->name = $name;
        $this->id = $id;
        $this->value = $value;
        $this->set = $set;
        $this->checked = $checked;
        $this->disabled = $disabled;
    }

    public function render(): View|Closure|string
    {
        return view('components.input-check');
    }
}

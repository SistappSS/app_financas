<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputRange extends Component
{
    public $title;
    public $name;
    public $rangeInput;
    public $rangeValue;
    public $min;
    public $max;
    public $value;
    public $col;
    public $set;

    public function __construct($title, $name, $rangeInput, $rangeValue, $min, $max, $col = null, $set = null)
    {
        $this->title = $title;
        $this->name = $name;
        $this->rangeInput = $rangeInput;
        $this->rangeValue = $rangeValue;
        $this->min = $min;
        $this->max = $max;
        $this->col = $col;
        $this->set = $set;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.input-range');
    }
}

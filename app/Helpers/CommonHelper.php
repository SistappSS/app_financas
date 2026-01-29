<?php

use Illuminate\Support\Facades\Route;

function currentRoute(): array
{
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();

    if ($currentRoute) {
        $route = explode('.', $currentRoute);

        return $route;
    }

    return [];
}

function routeCreate()
{
    $route = currentRoute()[0] . '.create';

    return Route::has($route)
        ? route($route)
        : '#';
}

function routeEdit($id): string
{
    $name = currentRoute()[0] . '.edit';

    return Route::has($name)
        ? route($name, $id)
        : '#';
}

function routeShow($id): string
{
    $name = currentRoute()[0] . '.show';

    return Route::has($name)
        ? route($name, $id)
        : '#';
}

function brlPrice($value) {
    $value = number_format($value, 2, ',', '.');

    $value = "R$ {$value}";

    return $value;
}

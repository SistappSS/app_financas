<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartialRender
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (!($request->headers->has('X-Partial') || $request->headers->has('HX-Request'))) {
            return $response;
        }

        // Só processa respostas de view Blade
        if (method_exists($response, 'getOriginalContent')) {
            $content = $response->getOriginalContent();
            if ($content instanceof View) {
                // renderSections retorna todas as sections do Blade
                $sections = $content->renderSections();
                if (isset($sections['content'])) {
                    return response($sections['content']);
                }
                // fallback: render normal
                return response($content->render());
            }
        }

        return $response;
    }
}

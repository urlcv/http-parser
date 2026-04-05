<?php

declare(strict_types=1);

namespace URLCV\HttpParser\Laravel;

use Illuminate\Support\ServiceProvider;

class HttpParserServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'http-parser');
    }
}

<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ShortcodeServiceProvider::class,
    Src\AutoInfoCenter\Providers\AutoInfoCenterServiceProvider::class,
    App\Providers\ViewComponentServiceProvider::class,
    App\Providers\CustomRouteServiceProvider::class,
    Torann\LaravelMetaTags\MetaTagsServiceProvider::class,
    Src\ArticleGenerator\Infrastructure\Providers\CommandServiceProvider::class,
    Src\ArticleGenerator\Infrastructure\Providers\ArticleCorrectionServiceProvider::class,
    Src\Shared\Providers\ViewComponentServiceProvider::class,
    Src\Sitemap\Provider\SitemapServiceProvider::class,
    Src\Rss\Provider\RssServiceProvider::class,
    Src\Export\Provider\ExportServiceProvider::class,

    Src\ContentGeneration\ReviewSchedule\Providers\ReviewScheduleServiceProvider::class,
    Src\ContentGeneration\TireSchedule\Provider\TireCorrectionServiceProvider::class,
    Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Providers\WhenToChangeTiresServiceProvider::class,
    Src\ContentGeneration\TirePressureGuide\Infrastructure\Providers\TirePressureGuideServiceProvider::class,
];

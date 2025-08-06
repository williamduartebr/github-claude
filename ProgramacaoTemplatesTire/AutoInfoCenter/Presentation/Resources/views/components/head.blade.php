<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ MetaTag::get('title') }}</title>
    {!! MetaTag::tag('description') !!}
    {!! MetaTag::openGraph() !!}
    {!! MetaTag::tag('og:image') !!}
    {!! MetaTag::tag('og:image:width') !!}
    {!! MetaTag::tag('og:image:height') !!}
    @if (Route::is('info.article.show'))
    {!! MetaTag::tag('article:author') !!}
    {!! MetaTag::tag('article:section') !!}
    {!! MetaTag::tag('article:published_time') !!}
    {!! MetaTag::tag('article:modified_time') !!} 
    @endif
    {!! MetaTag::tag('robots') !!}
    @stack('head')
    {!! generate_meta_favicon() !!}

    @production
        <x-g-tag />  
        <x-google-ads-tag />
    @endproduction
    
    <!-- CSS não crítico -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
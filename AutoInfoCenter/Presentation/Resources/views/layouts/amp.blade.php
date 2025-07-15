<!doctype html>
<html amp lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>{{ MetaTag::get('title') }}</title>
    {!! MetaTag::tag('description') !!}
    {!! MetaTag::openGraph() !!}
    {!! MetaTag::tag('og:image') !!}
    {!! MetaTag::tag('og:image:width') !!}
    {!! MetaTag::tag('og:image:height') !!}
    {!! MetaTag::tag('article:author') !!}
    {!! MetaTag::tag('article:section') !!}
    {!! MetaTag::tag('article:published_time') !!}
    {!! MetaTag::tag('article:modified_time') !!} 
    <link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">
    {!! MetaTag::tag('robots') !!}
    {!! generate_meta_favicon() !!}
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
    <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

    <style amp-boilerplate>
        body {
            -webkit-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            -moz-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            -ms-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            animation: -amp-start 8s steps(1, end) 0s 1 normal both
        }

        @-webkit-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-moz-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-ms-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-o-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }
    </style>

    <noscript>
        <style amp-boilerplate>
            body {
                -webkit-animation: none;
                -moz-animation: none;
                -ms-animation: none;
                animation: none
            }
        </style>
    </noscript>

    @yield('amp-head')
</head>

<body>
    @yield('content')
    <!-- Analytics -->
    <x-g-tag-amp />
    @production
        <x-g-tag-amp />  
    @endproduction

</body>

</html>
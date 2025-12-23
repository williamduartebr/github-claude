{{--
Template: guide.specific.blade.php
Renderiza guia completo com includes organizados

@version 4.0 - Arquitetura de includes
--}}

@extends('shared::layouts.app')

{{-- ✅ STRUCTURED DATA (Schema.org) --}}
@if(!empty($structured_data))
@push('head')
<script type="application.ld+json">
{!! json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
@endif

@section('content')

{{-- BREADCRUMBS --}}
@include('guide-data-center::guide.specific.partials.breadcrumbs')

{{-- HERO / TÍTULO --}}
@include('guide-data-center::guide.decision.decisao')


{{-- BANNER --}}
@include('guide-data-center::guide.specific.partials.banner-button')

{{-- CRÉDITOS EQUIPE EDITORIAL --}}
@include('guide-data-center::guide.specific.partials.editorial-info')

@endsection
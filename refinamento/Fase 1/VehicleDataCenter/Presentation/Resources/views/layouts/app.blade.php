<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-info::head />

<body class="bg-gray-50">

    <!-- Skip link para acessibilidade -->
    <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>

    <!-- Cabeçalho -->
    <x-info::header />

    @yield('breadcrumbs')
    @yield('content')

    <!-- Rodapé -->
    <x-info::footer />

    <!-- Botão de Voltar ao Topo -->
    <x-info::back-to-top />

    <!-- Inclui os scripts padrão do layout -->
    @include('vehicle-data-center::layouts.scripts-app')

    <!-- Inclui as scripts na stack -->
    @stack('scripts')
</body>

</html>
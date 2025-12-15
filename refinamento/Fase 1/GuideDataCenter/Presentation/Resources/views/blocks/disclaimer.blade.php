{{--
Componente: DISCLAIMER (Aviso Importante)

Uso: Avisos, notas e disclaimers

Estrutura esperada:
- text: string
- type: string (warning|info|danger) - default: warning

@author Claude Sonnet 4.5
@version 1.0
--}}

@php
$type = $block['data']['type'] ?? 'warning';
$configs = [
    'warning' => [
        'bg' => 'bg-yellow-50',
        'border' => 'border-yellow-400',
        'text' => 'text-yellow-800',
        'icon_color' => 'text-yellow-400',
    ],
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-400',
        'text' => 'text-blue-800',
        'icon_color' => 'text-blue-400',
    ],
    'danger' => [
        'bg' => 'bg-red-50',
        'border' => 'border-red-400',
        'text' => 'text-red-800',
        'icon_color' => 'text-red-400',
    ],
];
$config = $configs[$type] ?? $configs['warning'];
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 my-6">
    <div class="{{ $config['bg'] }} border-l-4 {{ $config['border'] }} p-4 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 {{ $config['icon_color'] }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-semibold {{ $config['text'] }}">
                    {{ $block['data']['text'] ?? '' }}
                </p>
            </div>
        </div>
    </div>
</div>

{{--
Componente: ALERT (Caixa de Alerta)

Uso: Avisos importantes (info, warning, danger, success)

Estrutura esperada:
{
  "block_type": "alert",
  "content": {
    "alert_type": "info | warning | danger | success",
    "alert_type_label": "Informação | Aviso | Perigo | Sucesso",
    "title": "Título do Alerta",
    "message": "Mensagem detalhada do alerta"
  }
}

@author Claude Sonnet 4
@version 1.0
--}}

@php
    $alertType = $block['content']['alert_type'] ?? 'info';
    
    // Configuração de cores por tipo
    $alertConfig = [
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-500',
            'text' => 'text-blue-800',
            'icon_color' => 'text-blue-500',
            'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-400',
            'text' => 'text-yellow-800',
            'icon_color' => 'text-yellow-400',
            'icon' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />'
        ],
        'danger' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-500',
            'text' => 'text-red-800',
            'icon_color' => 'text-red-500',
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />'
        ],
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-500',
            'text' => 'text-green-800',
            'icon_color' => 'text-green-500',
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />'
        ]
    ];
    
    $config = $alertConfig[$alertType] ?? $alertConfig['info'];
@endphp

<div class="{{ $config['bg'] }} border-l-4 {{ $config['border'] }} p-4 mb-8 rounded-r-md shadow-sm">
    <div class="flex">
        {{-- Icon --}}
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 {{ $config['icon_color'] }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                {!! $config['icon'] !!}
            </svg>
        </div>

        {{-- Content --}}
        <div class="ml-3 flex-1">
            {{-- Title --}}
            @if(!empty($block['content']['title']))
                <h3 class="text-sm font-semibold {{ $config['text'] }} mb-2">
                    {{ $block['content']['title'] }}
                </h3>
            @endif

            {{-- Message --}}
            @if(!empty($block['content']['message']))
                <div class="text-sm {{ $config['text'] }} leading-relaxed">
                    {!! nl2br(e($block['content']['message'])) !!}
                </div>
            @endif
        </div>
    </div>
</div>
@if(!empty($article->change_procedure) && is_array($article->change_procedure) && count($article->change_procedure) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Procedimento de
        Troca</h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <ol class="space-y-6">
            @foreach($article->change_procedure as $index => $step)
            @if(!empty($step['passo']))
            <li class="flex">
                <div
                    class="flex-shrink-0 h-8 w-8 rounded-full bg-[#0E368A] text-white flex items-center justify-center font-medium">
                    {{ $index + 1 }}</div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $step['passo'] }}</h3>
                    @if(!empty($step['descricao']))
                    <p class="text-gray-700">{{ $step['descricao'] }}</p>
                    @endif
                </div>
            </li>
            @endif
            @endforeach
        </ol>

        @if(!empty($article->environmental_note))
        <div class="mt-6 bg-[#0E368A]/5 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="ml-3 text-sm text-gray-700">
                    {{ $article->environmental_note }}
                </p>
            </div>
        </div>
        @endif
    </div>
</section>
@endif
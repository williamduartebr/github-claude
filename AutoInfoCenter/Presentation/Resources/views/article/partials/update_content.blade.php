<div class="text-xs text-gray-500 flex justify-between items-center pt-4 border-t border-gray-200">
    <span>Atualizado em: {{ $article->formated_updated_at }}</span>
    <div class="flex space-x-4">
        {{-- <a href="/contato/correcoes/" class="hover:text-[#0E368A] hover:underline">Reportar erro</a> --}}
        <a href="{{ route('info.article.show.amp', $article->slug) }}">Ver versão mobile</a>
    </div>
</div>
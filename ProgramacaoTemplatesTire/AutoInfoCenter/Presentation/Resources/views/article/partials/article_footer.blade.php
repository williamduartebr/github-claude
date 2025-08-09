<!-- Footer info -->
<div class="article-footer">
    @if(!empty($article->formated_updated_at))
    <p><strong>Atualizado em:</strong> {{ $article->formated_updated_at }}</p>
    @endif
    <p><strong>Por:</strong> Equipe Editorial Mercado Veículos</p>
    <p><a href="{{ route('info.article.show', $article->slug) }}">Ver versão completa do artigo</a></p>
</div>
{{--
    Conteúdo do modal "Pré-visualizar" do backoffice. Os estilos imitam o site público
    (public/css/site.css), porque o Filament apaga os estilos por defeito de listas e títulos.
--}}
<div class="faq-pre-visualizacao">
    <style>
        .faq-pre-visualizacao { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.6; color: #1d2329; background: #fff; border: 1px solid #c9d3db; border-radius: .5rem; padding: 1.25rem 1.5rem; }
        .faq-pre-visualizacao h1 { font-size: 1.6rem; font-weight: 700; line-height: 1.25; margin: 0 0 1rem; }
        .faq-pre-visualizacao h2 { font-size: 1.3rem; font-weight: 700; margin: 1.5rem 0 .75rem; }
        .faq-pre-visualizacao h3 { font-size: 1.1rem; font-weight: 700; margin: 1.25rem 0 .5rem; }
        .faq-pre-visualizacao p { margin: 0 0 1rem; }
        .faq-pre-visualizacao ul { list-style: disc; padding-left: 1.5rem; margin: 0 0 1rem; }
        .faq-pre-visualizacao ol { list-style: decimal; padding-left: 1.5rem; margin: 0 0 1rem; }
        .faq-pre-visualizacao a { color: #0b4f6c; text-decoration: underline; }
        .faq-pre-visualizacao .vazio { color: #4a5560; font-style: italic; }
    </style>

    <h1>{{ filled($pergunta) ? $pergunta : 'Pergunta ainda por escrever' }}</h1>

    @if (filled(strip_tags($resposta)))
        {!! $resposta !!}
    @else
        <p class="vazio">A resposta ainda está vazia.</p>
    @endif
</div>

/*
 * Pesquisa enquanto se escreve.
 *
 * Sem este ficheiro a pesquisa continua a funcionar: o formulário envia para /pesquisa.
 * Com ele, ao escrever, pede ao servidor só o bloco dos resultados (cabeçalho
 * X-Fragmento) e mostra-o por baixo da caixa, sem recarregar a página.
 */
(function () {
    'use strict';

    var ESPERA_MS = 300; // debounce: espera que a pessoa pare de escrever
    var MINIMO = 2;

    document.querySelectorAll('[data-pesquisa-ao-vivo]').forEach(function (campo) {
        var formulario = campo.form;
        var alvo = document.getElementById(campo.getAttribute('aria-controls'));
        var estado = document.getElementById('estado-pesquisa');
        var naPaginaDePesquisa = window.location.pathname === new URL(formulario.action).pathname;
        var temporizador = null;
        var pedidoAtual = null;

        campo.addEventListener('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(atualizar, ESPERA_MS);
        });

        function atualizar() {
            var termo = campo.value.trim();
            var url = new URL(formulario.action);
            url.searchParams.set('q', termo);

            if (pedidoAtual) {
                pedidoAtual.abort();
            }

            if (termo.length < MINIMO) {
                if (!naPaginaDePesquisa) {
                    alvo.innerHTML = '';
                    estado.textContent = '';
                }
                return;
            }

            pedidoAtual = new AbortController();

            fetch(url, { headers: { 'X-Fragmento': '1' }, signal: pedidoAtual.signal })
                .then(function (resposta) {
                    if (!resposta.ok) {
                        throw new Error('Pesquisa falhou');
                    }
                    return resposta.text();
                })
                .then(function (html) {
                    alvo.innerHTML = html;

                    var bloco = alvo.querySelector('[data-total]');
                    var total = bloco ? Number(bloco.getAttribute('data-total')) : 0;
                    estado.textContent = total === 1 ? '1 resultado.' : total + ' resultados.';

                    if (naPaginaDePesquisa) {
                        history.replaceState(null, '', url);
                    }
                })
                .catch(function (erro) {
                    if (erro.name !== 'AbortError') {
                        estado.textContent = 'Não foi possível pesquisar. Carregue em Pesquisar para tentar de novo.';
                    }
                });
        }
    });
})();

/*
 * Pesquisa enquanto se escreve.
 *
 * Sem este ficheiro a pesquisa continua a funcionar: o formulário envia para /pesquisa.
 * Com ele, ao escrever, pede ao servidor só o bloco dos resultados (cabeçalho
 * X-Fragmento) e mostra-o por baixo da caixa, sem recarregar a página.
 *
 * Só quando a pessoa pára de escrever é que a pesquisa fica registada para as
 * estatísticas (cabeçalho X-Registar), para não guardar pedaços de palavras.
 */
(function () {
    'use strict';

    var ESPERA_MS = 300; // debounce: espera que a pessoa pare de escrever
    var REGISTO_MS = 1500; // pausa a partir da qual a pesquisa conta para as estatísticas
    var MINIMO = 2;

    document.querySelectorAll('[data-pesquisa-ao-vivo]').forEach(function (campo) {
        var formulario = campo.form;
        var alvo = document.getElementById(campo.getAttribute('aria-controls'));
        var estado = document.getElementById('estado-pesquisa');
        var naPaginaDePesquisa = window.location.pathname === new URL(formulario.action).pathname;
        var temporizador = null;
        var temporizadorRegisto = null;
        var pedidoAtual = null;
        var ultimoRegistado = null;

        campo.addEventListener('input', function () {
            clearTimeout(temporizador);
            clearTimeout(temporizadorRegisto);
            temporizador = setTimeout(function () {
                atualizar(false);
            }, ESPERA_MS);
            temporizadorRegisto = setTimeout(function () {
                atualizar(true);
            }, REGISTO_MS);
        });

        function atualizar(registar) {
            var termo = campo.value.trim();
            var url = new URL(formulario.action);
            url.searchParams.set('q', termo);

            if (termo.length < MINIMO) {
                if (pedidoAtual) {
                    pedidoAtual.abort();
                }
                if (!naPaginaDePesquisa && !registar) {
                    alvo.innerHTML = '';
                    estado.textContent = '';
                }
                return;
            }

            if (registar && termo === ultimoRegistado) {
                return;
            }

            var cabecalhos = { 'X-Fragmento': '1' };
            var controlador = new AbortController();

            if (registar) {
                // O pedido de registo não é cancelado por novas teclas.
                cabecalhos['X-Registar'] = '1';
                ultimoRegistado = termo;
            } else {
                if (pedidoAtual) {
                    pedidoAtual.abort();
                }
                pedidoAtual = controlador;
            }

            fetch(url, { headers: cabecalhos, signal: controlador.signal })
                .then(function (resposta) {
                    if (!resposta.ok) {
                        throw new Error('Pesquisa falhou');
                    }
                    return resposta.text();
                })
                .then(function (html) {
                    // O pedido de registo devolve os mesmos resultados; não é preciso repetir o anúncio.
                    if (registar) {
                        return;
                    }

                    alvo.innerHTML = html;

                    var bloco = alvo.querySelector('[data-total]');
                    var total = bloco ? Number(bloco.getAttribute('data-total')) : 0;
                    estado.textContent = total === 1 ? '1 resultado.' : total + ' resultados.';

                    if (naPaginaDePesquisa) {
                        history.replaceState(null, '', url);
                    }
                })
                .catch(function (erro) {
                    if (erro.name !== 'AbortError' && !registar) {
                        estado.textContent = 'Não foi possível pesquisar. Carregue em Pesquisar para tentar de novo.';
                    }
                });
        }
    });
})();

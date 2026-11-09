/*
 * "Esta resposta foi útil?" sem recarregar a página.
 * Sem este ficheiro o formulário é enviado normalmente.
 */
(function () {
    'use strict';

    document.querySelectorAll('form[data-avaliacao]').forEach(function (formulario) {
        var estado = formulario.parentElement.querySelector('.avaliacao-estado');

        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();

            var dados = new FormData(formulario);
            dados.set('util', evento.submitter ? evento.submitter.value : '1');

            formulario.querySelectorAll('button').forEach(function (botao) {
                botao.disabled = true;
            });

            fetch(formulario.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: dados,
            })
                .then(function (resposta) {
                    if (!resposta.ok) {
                        throw new Error('Avaliação falhou');
                    }
                    return resposta.json();
                })
                .then(function (json) {
                    formulario.hidden = true;
                    formulario.parentElement.classList.add('avaliacao-respondida');
                    estado.textContent = json.mensagem;

                    var ajuda = formulario.parentElement.querySelector('[data-avaliacao-ajuda]');
                    if (ajuda && dados.get('util') === '0') {
                        ajuda.hidden = false;
                    }
                })
                .catch(function () {
                    formulario.querySelectorAll('button').forEach(function (botao) {
                        botao.disabled = false;
                    });
                    estado.textContent = 'Não foi possível registar a sua opinião. Tente novamente.';
                });
        });
    });
})();

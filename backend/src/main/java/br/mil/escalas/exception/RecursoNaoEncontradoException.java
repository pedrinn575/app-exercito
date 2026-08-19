package br.mil.escalas.exception;

/**
 * Exceção lançada quando um recurso não é encontrado no banco.
 */
public class RecursoNaoEncontradoException extends RuntimeException {

    public RecursoNaoEncontradoException(String mensagem) {
        super(mensagem);
    }
}

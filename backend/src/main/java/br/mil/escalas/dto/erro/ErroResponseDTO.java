package br.mil.escalas.dto.erro;

import java.time.OffsetDateTime;

/**
 * DTO padronizado de resposta de erro da API.
 *
 * @param timestamp momento do erro
 * @param status    código HTTP
 * @param erro      tipo do erro
 * @param mensagem  descrição detalhada
 * @param caminho   URI da requisição
 */
public record ErroResponseDTO(
        OffsetDateTime timestamp,
        int status,
        String erro,
        String mensagem,
        String caminho
) {
    /**
     * Factory para criar resposta de erro.
     */
    public static ErroResponseDTO of(int status, String erro, String mensagem, String caminho) {
        return new ErroResponseDTO(OffsetDateTime.now(), status, erro, mensagem, caminho);
    }
}

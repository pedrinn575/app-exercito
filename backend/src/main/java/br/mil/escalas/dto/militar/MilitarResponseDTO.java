package br.mil.escalas.dto.militar;

import br.mil.escalas.entity.Militar;
import br.mil.escalas.entity.enums.TipoMilitar;
import java.util.UUID;

/**
 * DTO de resposta com dados do militar.
 */
public record MilitarResponseDTO(
        UUID id,
        Integer numero,
        String nome,
        TipoMilitar tipo,
        String posto,
        Boolean reserva,
        Boolean ativo
) {
    /**
     * Converte entidade Militar para DTO de resposta.
     *
     * @param militar entidade JPA
     * @return DTO populado
     */
    public static MilitarResponseDTO fromEntity(Militar militar) {
        return new MilitarResponseDTO(
                militar.getId(),
                militar.getNumero(),
                militar.getNome(),
                militar.getTipo(),
                militar.getPosto(),
                militar.getReserva(),
                militar.getAtivo()
        );
    }
}

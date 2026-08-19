package br.mil.escalas.dto.escala;

import br.mil.escalas.entity.enums.TipoEscala;
import java.util.List;

/**
 * DTO de resposta para consulta de escala.
 *
 * @param tipo     tipo da escala (PRETA ou VERMELHA)
 * @param dias     lista de dias com atribuições (vazia no scaffold)
 * @param mensagem mensagem informativa quando em desenvolvimento
 */
public record EscalaResponseDTO(
        TipoEscala tipo,
        List<EscalaDiaResponseDTO> dias,
        String mensagem
) {}

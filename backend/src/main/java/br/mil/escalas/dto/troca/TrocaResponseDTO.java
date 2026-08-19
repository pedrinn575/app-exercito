package br.mil.escalas.dto.troca;

import br.mil.escalas.entity.enums.StatusTroca;
import java.util.UUID;

/**
 * DTO de resposta de troca de serviço.
 */
public record TrocaResponseDTO(
        UUID id,
        Integer numeroSolicitante,
        Integer numeroAlvo,
        UUID escalaDiaId,
        StatusTroca status,
        String motivo
) {}

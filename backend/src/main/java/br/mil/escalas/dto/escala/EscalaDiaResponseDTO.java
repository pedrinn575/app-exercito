package br.mil.escalas.dto.escala;

import br.mil.escalas.entity.enums.FuncaoEscala;
import java.time.LocalDate;
import java.util.List;
import java.util.UUID;

/**
 * DTO de um dia específico da escala.
 */
public record EscalaDiaResponseDTO(
        UUID id,
        LocalDate data,
        Integer diaNumero,
        List<AtribuicaoResponseDTO> atribuicoes
) {}

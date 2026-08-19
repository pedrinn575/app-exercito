package br.mil.escalas.dto.falta;

import br.mil.escalas.entity.enums.TipoFalta;
import jakarta.validation.constraints.NotNull;
import java.util.UUID;

/**
 * DTO de entrada para registro de falta.
 */
public record FaltaRequestDTO(
        @NotNull UUID escalaDiaId,
        @NotNull UUID militarId,
        @NotNull TipoFalta tipo,
        UUID substitutoId,
        String observacao
) {}

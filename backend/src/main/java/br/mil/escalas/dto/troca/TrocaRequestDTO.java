package br.mil.escalas.dto.troca;

import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;
import java.util.UUID;

/**
 * DTO de entrada para solicitação de troca de serviço.
 */
public record TrocaRequestDTO(
        @NotNull @Positive Integer numeroSolicitante,
        @NotNull @Positive Integer numeroAlvo,
        @NotNull UUID escalaDiaId,
        String motivo
) {}

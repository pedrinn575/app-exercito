package br.mil.escalas.dto.militar;

import br.mil.escalas.entity.enums.TipoMilitar;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;

/**
 * DTO de entrada para cadastro/edição de militar.
 */
public record MilitarRequestDTO(
        @NotNull @Positive Integer numero,
        @NotBlank String nome,
        @NotNull TipoMilitar tipo,
        @NotBlank String posto,
        Boolean reserva
) {}

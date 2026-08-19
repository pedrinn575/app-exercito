package br.mil.escalas.dto.escala;

import br.mil.escalas.entity.enums.FuncaoEscala;
import java.util.UUID;

/**
 * DTO de atribuição de militar a uma função no dia.
 */
public record AtribuicaoResponseDTO(
        UUID militarId,
        String militarNome,
        Integer militarNumero,
        FuncaoEscala funcao
) {}

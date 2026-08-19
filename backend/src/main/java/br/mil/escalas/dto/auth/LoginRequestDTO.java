package br.mil.escalas.dto.auth;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;

/**
 * DTO de entrada para autenticação.
 *
 * @param email e-mail do usuário
 * @param senha senha em texto plano (será validada com BCrypt no service)
 */
public record LoginRequestDTO(
        @NotBlank(message = "E-mail é obrigatório")
        @Email(message = "E-mail inválido")
        String email,

        @NotBlank(message = "Senha é obrigatória")
        String senha
) {}

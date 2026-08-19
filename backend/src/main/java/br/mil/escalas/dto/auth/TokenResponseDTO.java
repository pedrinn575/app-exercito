package br.mil.escalas.dto.auth;

/**
 * DTO de resposta após login bem-sucedido.
 *
 * @param token JWT de acesso
 * @param tipo  tipo do token (Bearer)
 * @param usuario dados do usuário autenticado
 */
public record TokenResponseDTO(
        String token,
        String tipo,
        UsuarioResponseDTO usuario
) {}

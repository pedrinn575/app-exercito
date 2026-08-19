package br.mil.escalas.dto.auth;

import br.mil.escalas.entity.enums.Papel;
import java.util.UUID;

/**
 * DTO de resposta com dados públicos do usuário.
 *
 * @param id    identificador UUID
 * @param email e-mail
 * @param papel papel de acesso
 * @param nome  nome de exibição (do militar vinculado ou e-mail)
 */
public record UsuarioResponseDTO(
        UUID id,
        String email,
        Papel papel,
        String nome
) {}

package br.mil.escalas.repository;

import br.mil.escalas.entity.Usuario;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.Optional;
import java.util.UUID;

/**
 * Repositório JPA para a entidade Usuario.
 * Responsabilidade: acesso a dados de usuários.
 */
@Repository
public interface UsuarioRepository extends JpaRepository<Usuario, UUID> {

    /**
     * Busca usuário ativo pelo e-mail.
     *
     * @param email e-mail do usuário
     * @return Optional com o usuário encontrado
     */
    Optional<Usuario> findByEmailAndAtivoTrue(String email);
}

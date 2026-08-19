package br.mil.escalas.repository;

import br.mil.escalas.entity.Militar;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;
import java.util.UUID;

/**
 * Repositório JPA para a entidade Militar.
 * Responsabilidade: acesso a dados de militares.
 */
@Repository
public interface MilitarRepository extends JpaRepository<Militar, UUID> {

    Optional<Militar> findByNumero(Integer numero);

    Optional<Militar> findByUsuarioId(UUID usuarioId);

    List<Militar> findByAtivoTrueOrderByNumeroAsc();

    List<Militar> findByReservaTrueAndAtivoTrue();
}

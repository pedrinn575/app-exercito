package br.mil.escalas.repository;

import br.mil.escalas.entity.Escala;
import br.mil.escalas.entity.enums.TipoEscala;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.Optional;
import java.util.UUID;

/**
 * Repositório JPA para a entidade Escala.
 */
@Repository
public interface EscalaRepository extends JpaRepository<Escala, UUID> {

    Optional<Escala> findByTipoAndAtivaTrue(TipoEscala tipo);
}

package br.mil.escalas.repository;

import br.mil.escalas.entity.EscalaDia;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.UUID;

/**
 * Repositório JPA para dias de escala.
 */
@Repository
public interface EscalaDiaRepository extends JpaRepository<EscalaDia, UUID> {

    List<EscalaDia> findByEscalaIdOrderByDiaNumeroAsc(UUID escalaId);
}

package br.mil.escalas.repository;

import br.mil.escalas.entity.Falta;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.UUID;

/**
 * Repositório JPA para faltas.
 */
@Repository
public interface FaltaRepository extends JpaRepository<Falta, UUID> {
}

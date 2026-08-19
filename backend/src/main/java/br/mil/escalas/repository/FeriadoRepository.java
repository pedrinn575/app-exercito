package br.mil.escalas.repository;

import br.mil.escalas.entity.Feriado;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.time.LocalDate;
import java.util.Optional;
import java.util.UUID;

/**
 * Repositório JPA para feriados.
 */
@Repository
public interface FeriadoRepository extends JpaRepository<Feriado, UUID> {

    Optional<Feriado> findByData(LocalDate data);

    boolean existsByData(LocalDate data);
}

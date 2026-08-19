package br.mil.escalas.repository;

import br.mil.escalas.entity.TrocaServico;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.UUID;

/**
 * Repositório JPA para trocas de serviço.
 */
@Repository
public interface TrocaServicoRepository extends JpaRepository<TrocaServico, UUID> {
}

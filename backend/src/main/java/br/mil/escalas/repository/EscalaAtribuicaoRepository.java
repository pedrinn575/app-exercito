package br.mil.escalas.repository;

import br.mil.escalas.entity.EscalaAtribuicao;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.UUID;

/**
 * Repositório JPA para atribuições de militares em dias de escala.
 */
@Repository
public interface EscalaAtribuicaoRepository extends JpaRepository<EscalaAtribuicao, UUID> {

    List<EscalaAtribuicao> findByEscalaDiaId(UUID escalaDiaId);
}

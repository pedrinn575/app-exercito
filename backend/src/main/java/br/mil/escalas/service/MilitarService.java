package br.mil.escalas.service;

import br.mil.escalas.dto.militar.MilitarRequestDTO;
import br.mil.escalas.dto.militar.MilitarResponseDTO;
import br.mil.escalas.exception.FuncionalidadeNaoImplementadaException;
import br.mil.escalas.repository.MilitarRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;
import java.util.UUID;

/**
 * Service de militares.
 * Responsabilidade: regras de negócio do cadastro e consulta de militares.
 * Status scaffold: listagem implementada; cadastro pendente.
 */
@Service
public class MilitarService {

    private final MilitarRepository militarRepository;

    public MilitarService(MilitarRepository militarRepository) {
        this.militarRepository = militarRepository;
    }

    /**
     * Lista todos os militares ativos ordenados por número.
     *
     * @return lista de DTOs
     */
    @Transactional(readOnly = true)
    public List<MilitarResponseDTO> listarTodos() {
        return militarRepository.findByAtivoTrueOrderByNumeroAsc()
                .stream()
                .map(MilitarResponseDTO::fromEntity)
                .toList();
    }

    /**
     * Busca militar por ID.
     * Stub: lança exceção de não implementado.
     */
    @Transactional(readOnly = true)
    public MilitarResponseDTO buscarPorId(UUID id) {
        throw new FuncionalidadeNaoImplementadaException(
                "Busca de militar por ID será implementada na próxima fase");
    }

    /**
     * Cadastra novo militar.
     * Stub: lança exceção de não implementado.
     */
    @Transactional
    public MilitarResponseDTO cadastrar(MilitarRequestDTO request) {
        throw new FuncionalidadeNaoImplementadaException(
                "Cadastro de militar será implementado na próxima fase");
    }
}

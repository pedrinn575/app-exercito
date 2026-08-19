package br.mil.escalas.service;

import br.mil.escalas.dto.troca.TrocaRequestDTO;
import br.mil.escalas.dto.troca.TrocaResponseDTO;
import br.mil.escalas.exception.FuncionalidadeNaoImplementadaException;
import br.mil.escalas.repository.TrocaServicoRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.Collections;
import java.util.List;
import java.util.UUID;

/**
 * Service de trocas de serviço.
 * Responsabilidade: fluxo solicitante → alvo aceita → admin aprova.
 * Status scaffold: listagem vazia; operações pendentes.
 */
@Service
public class TrocaService {

    private final TrocaServicoRepository trocaRepository;

    public TrocaService(TrocaServicoRepository trocaRepository) {
        this.trocaRepository = trocaRepository;
    }

    @Transactional(readOnly = true)
    public List<TrocaResponseDTO> listarTodas() {
        return Collections.emptyList();
    }

    @Transactional
    public TrocaResponseDTO solicitar(TrocaRequestDTO request) {
        throw new FuncionalidadeNaoImplementadaException(
                "Solicitação de troca será implementada na próxima fase");
    }

    @Transactional
    public TrocaResponseDTO aceitar(UUID id) {
        throw new FuncionalidadeNaoImplementadaException(
                "Aceite de troca pelo alvo será implementado na próxima fase");
    }

    @Transactional
    public TrocaResponseDTO aprovar(UUID id) {
        throw new FuncionalidadeNaoImplementadaException(
                "Aprovação de troca pelo admin será implementada na próxima fase");
    }

    @Transactional
    public TrocaResponseDTO rejeitar(UUID id) {
        throw new FuncionalidadeNaoImplementadaException(
                "Rejeição de troca será implementada na próxima fase");
    }
}

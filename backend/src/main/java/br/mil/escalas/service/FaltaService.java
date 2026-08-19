package br.mil.escalas.service;

import br.mil.escalas.dto.falta.FaltaRequestDTO;
import br.mil.escalas.exception.FuncionalidadeNaoImplementadaException;
import br.mil.escalas.repository.FaltaRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.Collections;
import java.util.List;

/**
 * Service de controle de faltas.
 * Responsabilidade: registrar faltas justificadas/não justificadas e substituições.
 * Status scaffold: pendente de implementação.
 */
@Service
public class FaltaService {

    private final FaltaRepository faltaRepository;

    public FaltaService(FaltaRepository faltaRepository) {
        this.faltaRepository = faltaRepository;
    }

    @Transactional(readOnly = true)
    public List<Object> listarTodas() {
        return Collections.emptyList();
    }

    @Transactional
    public Object registrar(FaltaRequestDTO request) {
        throw new FuncionalidadeNaoImplementadaException(
                "Registro de falta será implementado na próxima fase");
    }
}

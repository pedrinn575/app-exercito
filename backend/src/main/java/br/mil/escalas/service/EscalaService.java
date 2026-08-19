package br.mil.escalas.service;

import br.mil.escalas.dto.escala.EscalaResponseDTO;
import br.mil.escalas.entity.enums.TipoEscala;
import br.mil.escalas.exception.FuncionalidadeNaoImplementadaException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.Collections;

/**
 * Service de escalas (preta e vermelha).
 * Responsabilidade: geração, rotação e consulta de escalas.
 * Status scaffold: retorna estrutura vazia; geração pendente.
 */
@Service
public class EscalaService {

    /**
     * Consulta escala preta ativa.
     * Stub: retorna estrutura vazia com mensagem informativa.
     */
    @Transactional(readOnly = true)
    public EscalaResponseDTO consultarPreta() {
        return new EscalaResponseDTO(
                TipoEscala.PRETA,
                Collections.emptyList(),
                "Funcionalidade em desenvolvimento"
        );
    }

    /**
     * Consulta escala vermelha ativa.
     * Stub: retorna estrutura vazia com mensagem informativa.
     */
    @Transactional(readOnly = true)
    public EscalaResponseDTO consultarVermelha() {
        return new EscalaResponseDTO(
                TipoEscala.VERMELHA,
                Collections.emptyList(),
                "Funcionalidade em desenvolvimento"
        );
    }

    /**
     * Gera nova escala preta com rotação 1-150.
     * Stub: pendente de implementação.
     */
    @Transactional
    public EscalaResponseDTO gerarPreta() {
        throw new FuncionalidadeNaoImplementadaException(
                "Geração da escala preta será implementada na próxima fase");
    }

    /**
     * Gera nova escala vermelha para fins de semana/feriados.
     * Stub: pendente de implementação.
     */
    @Transactional
    public EscalaResponseDTO gerarVermelha() {
        throw new FuncionalidadeNaoImplementadaException(
                "Geração da escala vermelha será implementada na próxima fase");
    }
}

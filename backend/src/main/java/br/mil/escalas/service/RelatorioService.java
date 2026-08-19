package br.mil.escalas.service;

import br.mil.escalas.exception.FuncionalidadeNaoImplementadaException;
import org.springframework.stereotype.Service;

/**
 * Service de relatórios.
 * Responsabilidade: gerar relatórios de faltas e serviços por período.
 * Status scaffold: pendente de implementação.
 */
@Service
public class RelatorioService {

    public Object relatorioFaltas(String inicio, String fim) {
        throw new FuncionalidadeNaoImplementadaException(
                "Relatório de faltas será implementado na próxima fase");
    }

    public Object relatorioServicos(String inicio, String fim) {
        throw new FuncionalidadeNaoImplementadaException(
                "Relatório de serviços será implementado na próxima fase");
    }
}

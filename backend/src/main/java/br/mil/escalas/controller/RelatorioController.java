package br.mil.escalas.controller;

import br.mil.escalas.service.RelatorioService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

/**
 * Controller de relatórios.
 * Responsabilidade: expor endpoints de relatórios de faltas e serviços.
 */
@RestController
@RequestMapping("/relatorios")
public class RelatorioController {

    private final RelatorioService relatorioService;

    public RelatorioController(RelatorioService relatorioService) {
        this.relatorioService = relatorioService;
    }

    @GetMapping("/faltas")
    public ResponseEntity<Object> relatorioFaltas(
            @RequestParam String inicio,
            @RequestParam String fim) {
        return ResponseEntity.ok(relatorioService.relatorioFaltas(inicio, fim));
    }

    @GetMapping("/servicos")
    public ResponseEntity<Object> relatorioServicos(
            @RequestParam String inicio,
            @RequestParam String fim) {
        return ResponseEntity.ok(relatorioService.relatorioServicos(inicio, fim));
    }
}

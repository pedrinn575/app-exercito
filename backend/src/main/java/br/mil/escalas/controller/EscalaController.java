package br.mil.escalas.controller;

import br.mil.escalas.dto.escala.EscalaResponseDTO;
import br.mil.escalas.service.EscalaService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

/**
 * Controller de escalas (preta e vermelha).
 * Responsabilidade: consulta e geração de escalas.
 */
@RestController
@RequestMapping("/escalas")
public class EscalaController {

    private final EscalaService escalaService;

    public EscalaController(EscalaService escalaService) {
        this.escalaService = escalaService;
    }

    @GetMapping("/preta")
    public ResponseEntity<EscalaResponseDTO> consultarPreta() {
        return ResponseEntity.ok(escalaService.consultarPreta());
    }

    @GetMapping("/vermelha")
    public ResponseEntity<EscalaResponseDTO> consultarVermelha() {
        return ResponseEntity.ok(escalaService.consultarVermelha());
    }

    @PostMapping("/preta/gerar")
    public ResponseEntity<EscalaResponseDTO> gerarPreta() {
        return ResponseEntity.ok(escalaService.gerarPreta());
    }

    @PostMapping("/vermelha/gerar")
    public ResponseEntity<EscalaResponseDTO> gerarVermelha() {
        return ResponseEntity.ok(escalaService.gerarVermelha());
    }
}

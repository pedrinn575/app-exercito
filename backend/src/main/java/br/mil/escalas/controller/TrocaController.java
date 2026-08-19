package br.mil.escalas.controller;

import br.mil.escalas.dto.troca.TrocaRequestDTO;
import br.mil.escalas.dto.troca.TrocaResponseDTO;
import br.mil.escalas.service.TrocaService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;
import java.util.UUID;

/**
 * Controller de trocas de serviço.
 * Responsabilidade: expor fluxo de solicitação, aceite e aprovação.
 */
@RestController
@RequestMapping("/trocas")
public class TrocaController {

    private final TrocaService trocaService;

    public TrocaController(TrocaService trocaService) {
        this.trocaService = trocaService;
    }

    @GetMapping
    public ResponseEntity<List<TrocaResponseDTO>> listar() {
        return ResponseEntity.ok(trocaService.listarTodas());
    }

    @PostMapping
    public ResponseEntity<TrocaResponseDTO> solicitar(@Valid @RequestBody TrocaRequestDTO request) {
        return ResponseEntity.ok(trocaService.solicitar(request));
    }

    @PatchMapping("/{id}/aceitar")
    public ResponseEntity<TrocaResponseDTO> aceitar(@PathVariable UUID id) {
        return ResponseEntity.ok(trocaService.aceitar(id));
    }

    @PatchMapping("/{id}/aprovar")
    public ResponseEntity<TrocaResponseDTO> aprovar(@PathVariable UUID id) {
        return ResponseEntity.ok(trocaService.aprovar(id));
    }

    @PatchMapping("/{id}/rejeitar")
    public ResponseEntity<TrocaResponseDTO> rejeitar(@PathVariable UUID id) {
        return ResponseEntity.ok(trocaService.rejeitar(id));
    }
}

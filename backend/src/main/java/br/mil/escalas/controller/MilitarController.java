package br.mil.escalas.controller;

import br.mil.escalas.dto.militar.MilitarRequestDTO;
import br.mil.escalas.dto.militar.MilitarResponseDTO;
import br.mil.escalas.service.MilitarService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;
import java.util.UUID;

/**
 * Controller de militares.
 * Responsabilidade: expor CRUD de militares via REST.
 * Regra: sem acesso direto ao repository — apenas MilitarService.
 */
@RestController
@RequestMapping("/militares")
public class MilitarController {

    private final MilitarService militarService;

    public MilitarController(MilitarService militarService) {
        this.militarService = militarService;
    }

    @GetMapping
    public ResponseEntity<List<MilitarResponseDTO>> listar() {
        return ResponseEntity.ok(militarService.listarTodos());
    }

    @GetMapping("/{id}")
    public ResponseEntity<MilitarResponseDTO> buscar(@PathVariable UUID id) {
        return ResponseEntity.ok(militarService.buscarPorId(id));
    }

    @PostMapping
    public ResponseEntity<MilitarResponseDTO> cadastrar(@Valid @RequestBody MilitarRequestDTO request) {
        return ResponseEntity.ok(militarService.cadastrar(request));
    }
}

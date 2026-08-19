package br.mil.escalas.controller;

import br.mil.escalas.dto.falta.FaltaRequestDTO;
import br.mil.escalas.service.FaltaService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

/**
 * Controller de faltas.
 * Responsabilidade: registro e consulta de faltas no serviço.
 */
@RestController
@RequestMapping("/faltas")
public class FaltaController {

    private final FaltaService faltaService;

    public FaltaController(FaltaService faltaService) {
        this.faltaService = faltaService;
    }

    @GetMapping
    public ResponseEntity<List<Object>> listar() {
        return ResponseEntity.ok(faltaService.listarTodas());
    }

    @PostMapping
    public ResponseEntity<Object> registrar(@Valid @RequestBody FaltaRequestDTO request) {
        return ResponseEntity.ok(faltaService.registrar(request));
    }
}

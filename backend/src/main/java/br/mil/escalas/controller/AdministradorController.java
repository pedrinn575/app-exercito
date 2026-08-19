package br.mil.escalas.controller;

import br.mil.escalas.dto.auth.UsuarioResponseDTO;
import br.mil.escalas.service.AdministradorService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import java.util.List;

/**
 * Controller de administradores.
 * Responsabilidade: listagem e cadastro de administradores (Subtenente).
 */
@RestController
@RequestMapping("/administradores")
public class AdministradorController {

    private final AdministradorService administradorService;

    public AdministradorController(AdministradorService administradorService) {
        this.administradorService = administradorService;
    }

    @GetMapping
    public ResponseEntity<List<UsuarioResponseDTO>> listar() {
        return ResponseEntity.ok(administradorService.listarTodos());
    }

    @PostMapping
    public ResponseEntity<UsuarioResponseDTO> cadastrar() {
        return ResponseEntity.ok(administradorService.cadastrar(null));
    }
}

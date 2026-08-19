package br.mil.escalas.controller;

import br.mil.escalas.dto.auth.LoginRequestDTO;
import br.mil.escalas.dto.auth.TokenResponseDTO;
import br.mil.escalas.dto.auth.UsuarioResponseDTO;
import br.mil.escalas.service.AuthService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

/**
 * Controller de autenticação.
 * Responsabilidade: expor endpoints de login e perfil.
 * Não acessa repository — delega tudo ao AuthService.
 */
@RestController
@RequestMapping("/auth")
public class AuthController {

    private final AuthService authService;

    public AuthController(AuthService authService) {
        this.authService = authService;
    }

    /**
     * POST /auth/login — autentica e retorna JWT.
     */
    @PostMapping("/login")
    public ResponseEntity<TokenResponseDTO> login(@Valid @RequestBody LoginRequestDTO request) {
        return ResponseEntity.ok(authService.login(request));
    }

    /**
     * GET /auth/me — retorna perfil do usuário autenticado.
     */
    @GetMapping("/me")
    public ResponseEntity<UsuarioResponseDTO> me(@AuthenticationPrincipal String email) {
        return ResponseEntity.ok(authService.obterPerfil(email));
    }
}

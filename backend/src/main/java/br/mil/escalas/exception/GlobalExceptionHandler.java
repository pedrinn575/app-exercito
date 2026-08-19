package br.mil.escalas.exception;

import br.mil.escalas.dto.erro.ErroResponseDTO;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import jakarta.servlet.http.HttpServletRequest;

/**
 * Handler global de exceções da API.
 * Responsabilidade: converter exceções em respostas JSON padronizadas.
 * Fluxo: exceção lançada no service → capturada aqui → ErroResponseDTO retornado.
 */
@RestControllerAdvice
public class GlobalExceptionHandler {

    @ExceptionHandler(RecursoNaoEncontradoException.class)
    public ResponseEntity<ErroResponseDTO> handleNaoEncontrado(
            RecursoNaoEncontradoException ex, HttpServletRequest request) {
        return ResponseEntity.status(HttpStatus.NOT_FOUND)
                .body(ErroResponseDTO.of(404, "Não encontrado", ex.getMessage(), request.getRequestURI()));
    }

    @ExceptionHandler(RegraNegocioException.class)
    public ResponseEntity<ErroResponseDTO> handleRegraNegocio(
            RegraNegocioException ex, HttpServletRequest request) {
        return ResponseEntity.status(HttpStatus.CONFLICT)
                .body(ErroResponseDTO.of(409, "Conflito", ex.getMessage(), request.getRequestURI()));
    }

    @ExceptionHandler(FuncionalidadeNaoImplementadaException.class)
    public ResponseEntity<ErroResponseDTO> handleNaoImplementada(
            FuncionalidadeNaoImplementadaException ex, HttpServletRequest request) {
        return ResponseEntity.status(HttpStatus.NOT_IMPLEMENTED)
                .body(ErroResponseDTO.of(501, "Não implementado", ex.getMessage(), request.getRequestURI()));
    }

    @ExceptionHandler(BadCredentialsException.class)
    public ResponseEntity<ErroResponseDTO> handleCredenciais(
            BadCredentialsException ex, HttpServletRequest request) {
        return ResponseEntity.status(HttpStatus.UNAUTHORIZED)
                .body(ErroResponseDTO.of(401, "Não autorizado", "Credenciais inválidas", request.getRequestURI()));
    }

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ErroResponseDTO> handleValidacao(
            MethodArgumentNotValidException ex, HttpServletRequest request) {
        // Concatena erros de validação dos campos
        String mensagem = ex.getBindingResult().getFieldErrors().stream()
                .map(FieldError::getDefaultMessage)
                .reduce((a, b) -> a + "; " + b)
                .orElse("Erro de validação");

        return ResponseEntity.status(HttpStatus.BAD_REQUEST)
                .body(ErroResponseDTO.of(400, "Erro de validação", mensagem, request.getRequestURI()));
    }

    @ExceptionHandler(Exception.class)
    public ResponseEntity<ErroResponseDTO> handleGenerico(
            Exception ex, HttpServletRequest request) {
        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(ErroResponseDTO.of(500, "Erro interno", ex.getMessage(), request.getRequestURI()));
    }
}

package br.mil.escalas.service;

import br.mil.escalas.dto.auth.UsuarioResponseDTO;
import br.mil.escalas.entity.Usuario;
import br.mil.escalas.entity.enums.Papel;
import br.mil.escalas.exception.FuncionalidadeNaoImplementadaException;
import br.mil.escalas.repository.UsuarioRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

/**
 * Service de administradores.
 * Responsabilidade: gestão de usuários com papel ADMINISTRADOR.
 * Status scaffold: listagem básica implementada.
 */
@Service
public class AdministradorService {

    private final UsuarioRepository usuarioRepository;

    public AdministradorService(UsuarioRepository usuarioRepository) {
        this.usuarioRepository = usuarioRepository;
    }

    /**
     * Lista administradores ativos.
     *
     * @return lista de DTOs
     */
    @Transactional(readOnly = true)
    public List<UsuarioResponseDTO> listarTodos() {
        return usuarioRepository.findAll().stream()
                .filter(u -> u.getPapel() == Papel.ADMINISTRADOR && Boolean.TRUE.equals(u.getAtivo()))
                .map(u -> new UsuarioResponseDTO(u.getId(), u.getEmail(), u.getPapel(), "Subtenente Admin"))
                .toList();
    }

    /**
     * Cadastra novo administrador.
     * Stub: pendente de implementação.
     */
    @Transactional
    public UsuarioResponseDTO cadastrar(Usuario usuario) {
        throw new FuncionalidadeNaoImplementadaException(
                "Cadastro de administrador será implementado na próxima fase");
    }
}

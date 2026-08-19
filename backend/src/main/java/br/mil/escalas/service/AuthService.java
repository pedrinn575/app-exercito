package br.mil.escalas.service;

import br.mil.escalas.dto.auth.LoginRequestDTO;
import br.mil.escalas.dto.auth.TokenResponseDTO;
import br.mil.escalas.dto.auth.UsuarioResponseDTO;
import br.mil.escalas.entity.Usuario;
import br.mil.escalas.entity.enums.Papel;
import br.mil.escalas.exception.RecursoNaoEncontradoException;
import br.mil.escalas.repository.MilitarRepository;
import br.mil.escalas.repository.UsuarioRepository;
import br.mil.escalas.util.JwtUtil;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.UUID;

/**
 * Service de autenticação.
 * Responsabilidade: validar credenciais, gerar JWT e retornar perfil do usuário.
 * Fluxo: login → busca usuário → valida BCrypt → gera token → retorna DTO.
 */
@Service
public class AuthService {

    private final UsuarioRepository usuarioRepository;
    private final MilitarRepository militarRepository;
    private final PasswordEncoder passwordEncoder;
    private final JwtUtil jwtUtil;

    public AuthService(UsuarioRepository usuarioRepository,
                       MilitarRepository militarRepository,
                       PasswordEncoder passwordEncoder,
                       JwtUtil jwtUtil) {
        this.usuarioRepository = usuarioRepository;
        this.militarRepository = militarRepository;
        this.passwordEncoder = passwordEncoder;
        this.jwtUtil = jwtUtil;
    }

    /**
     * Autentica usuário e retorna token JWT.
     *
     * @param request e-mail e senha
     * @return token e dados do usuário
     * @throws BadCredentialsException se credenciais inválidas
     */
    @Transactional(readOnly = true)
    public TokenResponseDTO login(LoginRequestDTO request) {
        Usuario usuario = usuarioRepository
                .findByEmailAndAtivoTrue(request.email())
                .orElseThrow(() -> new BadCredentialsException("Credenciais inválidas"));

        // Valida senha com hash BCrypt armazenado
        if (!passwordEncoder.matches(request.senha(), usuario.getSenhaHash())) {
            throw new BadCredentialsException("Credenciais inválidas");
        }

        String token = jwtUtil.gerarToken(
                usuario.getId(),
                usuario.getEmail(),
                usuario.getPapel().name()
        );

        UsuarioResponseDTO usuarioDto = montarUsuarioResponse(usuario);

        return new TokenResponseDTO(token, "Bearer", usuarioDto);
    }

    /**
     * Retorna perfil do usuário autenticado pelo e-mail.
     *
     * @param email e-mail do token JWT
     * @return dados públicos do usuário
     */
    @Transactional(readOnly = true)
    public UsuarioResponseDTO obterPerfil(String email) {
        Usuario usuario = usuarioRepository
                .findByEmailAndAtivoTrue(email)
                .orElseThrow(() -> new RecursoNaoEncontradoException("Usuário não encontrado"));

        return montarUsuarioResponse(usuario);
    }

    /**
     * Monta DTO de resposta com nome do militar vinculado ou e-mail como fallback.
     */
    private UsuarioResponseDTO montarUsuarioResponse(Usuario usuario) {
        String nome = militarRepository.findByUsuarioId(usuario.getId())
                .map(m -> m.getNome())
                .orElse(usuario.getEmail());

        // Administrador sem militar vinculado usa rótulo padrão
        if (usuario.getPapel() == Papel.ADMINISTRADOR && nome.equals(usuario.getEmail())) {
            nome = "Subtenente Admin";
        }

        return new UsuarioResponseDTO(
                usuario.getId(),
                usuario.getEmail(),
                usuario.getPapel(),
                nome
        );
    }
}

package br.mil.escalas.config;

import br.mil.escalas.entity.Militar;
import br.mil.escalas.entity.Usuario;
import br.mil.escalas.entity.enums.Papel;
import br.mil.escalas.entity.enums.TipoMilitar;
import br.mil.escalas.repository.MilitarRepository;
import br.mil.escalas.repository.UsuarioRepository;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.context.event.ApplicationReadyEvent;
import org.springframework.context.event.EventListener;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Seeder de dados de desenvolvimento.
 * Responsabilidade: popular usuários e militares com senhas BCrypt corretas
 * quando o banco está vazio e seed está habilitado.
 * Fluxo: app inicia → verifica count → insere admin + 3 militares.
 */
@Component
public class DevDataSeeder {

    private final UsuarioRepository usuarioRepository;
    private final MilitarRepository militarRepository;
    private final PasswordEncoder passwordEncoder;

    @Value("${app.seed.enabled:true}")
    private boolean seedEnabled;

    public DevDataSeeder(UsuarioRepository usuarioRepository,
                         MilitarRepository militarRepository,
                         PasswordEncoder passwordEncoder) {
        this.usuarioRepository = usuarioRepository;
        this.militarRepository = militarRepository;
        this.passwordEncoder = passwordEncoder;
    }

    /**
     * Executa seed após a aplicação estar pronta.
     */
    @EventListener(ApplicationReadyEvent.class)
    @Transactional
    public void seed() {
        if (!seedEnabled || usuarioRepository.count() > 0) {
            return;
        }

        // Administrador (Subtenente)
        Usuario admin = new Usuario();
        admin.setEmail("admin@escala.local");
        admin.setSenhaHash(passwordEncoder.encode("admin123"));
        admin.setPapel(Papel.ADMINISTRADOR);
        admin.setAtivo(true);
        usuarioRepository.save(admin);

        // Militares de exemplo
        criarMilitar("militar1@escala.local", "militar123", 1, "Soldado Silva", TipoMilitar.ATIRADOR, "Soldado");
        criarMilitar("militar2@escala.local", "militar123", 2, "Soldado Santos", TipoMilitar.ATIRADOR, "Soldado");
        criarMilitar("militar3@escala.local", "militar123", 3, "Cabo Oliveira", TipoMilitar.MONITOR, "Cabo");
    }

    /**
     * Cria usuário + militar vinculado.
     */
    private void criarMilitar(String email, String senha, int numero,
                              String nome, TipoMilitar tipo, String posto) {
        Usuario usuario = new Usuario();
        usuario.setEmail(email);
        usuario.setSenhaHash(passwordEncoder.encode(senha));
        usuario.setPapel(Papel.MILITAR);
        usuario.setAtivo(true);
        usuario = usuarioRepository.save(usuario);

        Militar militar = new Militar();
        militar.setUsuario(usuario);
        militar.setNumero(numero);
        militar.setNome(nome);
        militar.setTipo(tipo);
        militar.setPosto(posto);
        militar.setReserva(false);
        militar.setAtivo(true);
        militarRepository.save(militar);
    }
}

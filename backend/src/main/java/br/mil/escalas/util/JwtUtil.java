package br.mil.escalas.util;

import br.mil.escalas.config.JwtProperties;
import io.jsonwebtoken.Claims;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.security.Keys;
import org.springframework.stereotype.Component;
import javax.crypto.SecretKey;
import java.nio.charset.StandardCharsets;
import java.util.Date;
import java.util.UUID;

/**
 * Utilitário para geração e validação de tokens JWT.
 * Responsabilidade: criar e parsear tokens de autenticação.
 */
@Component
public class JwtUtil {

    private final JwtProperties jwtProperties;
    private final SecretKey secretKey;

    public JwtUtil(JwtProperties jwtProperties) {
        this.jwtProperties = jwtProperties;
        // Deriva chave HMAC a partir do secret configurado
        this.secretKey = Keys.hmacShaKeyFor(
                jwtProperties.getSecret().getBytes(StandardCharsets.UTF_8));
    }

    /**
     * Gera token JWT para o usuário.
     *
     * @param usuarioId ID do usuário
     * @param email     e-mail (subject)
     * @param papel     papel de acesso
     * @return token JWT assinado
     */
    public String gerarToken(UUID usuarioId, String email, String papel) {
        Date agora = new Date();
        Date expiracao = new Date(agora.getTime() + jwtProperties.getExpirationMs());

        return Jwts.builder()
                .subject(email)
                .claim("id", usuarioId.toString())
                .claim("papel", papel)
                .issuedAt(agora)
                .expiration(expiracao)
                .signWith(secretKey)
                .compact();
    }

    /**
     * Extrai claims do token JWT.
     *
     * @param token token Bearer (sem prefixo)
     * @return claims parseados
     */
    public Claims extrairClaims(String token) {
        return Jwts.parser()
                .verifyWith(secretKey)
                .build()
                .parseSignedClaims(token)
                .getPayload();
    }

    /**
     * Valida se o token é válido e não expirou.
     *
     * @param token token JWT
     * @return true se válido
     */
    public boolean validarToken(String token) {
        try {
            extrairClaims(token);
            return true;
        } catch (Exception e) {
            return false;
        }
    }

    /**
     * Extrai e-mail (subject) do token.
     */
    public String extrairEmail(String token) {
        return extrairClaims(token).getSubject();
    }
}

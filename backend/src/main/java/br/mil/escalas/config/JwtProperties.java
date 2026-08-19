package br.mil.escalas.config;

import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.context.annotation.Configuration;

/**
 * Propriedades de configuração JWT carregadas do application.yml.
 */
@Configuration
@ConfigurationProperties(prefix = "jwt")
public class JwtProperties {

    /** Chave secreta para assinar tokens */
    private String secret;

    /** Tempo de expiração em milissegundos */
    private long expirationMs;

    public String getSecret() { return secret; }
    public void setSecret(String secret) { this.secret = secret; }

    public long getExpirationMs() { return expirationMs; }
    public void setExpirationMs(long expirationMs) { this.expirationMs = expirationMs; }
}

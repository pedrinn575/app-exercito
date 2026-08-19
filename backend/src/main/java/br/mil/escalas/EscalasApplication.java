package br.mil.escalas;

import br.mil.escalas.config.JwtProperties;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.properties.EnableConfigurationProperties;

/**
 * Ponto de entrada da aplicação Spring Boot.
 * Responsabilidade: inicializar o contexto da aplicação e subir o servidor HTTP.
 */
@SpringBootApplication
@EnableConfigurationProperties(JwtProperties.class)
public class EscalasApplication {

    /**
     * Inicia a aplicação.
     *
     * @param args argumentos da linha de comando
     */
    public static void main(String[] args) {
        SpringApplication.run(EscalasApplication.class, args);
    }
}

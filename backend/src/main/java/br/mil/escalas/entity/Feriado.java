package br.mil.escalas.entity;

import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de feriado.
 * Responsabilidade: datas que disparam escala vermelha automaticamente.
 */
@Entity
@Table(name = "feriados")
public class Feriado {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @Column(nullable = false, unique = true)
    private LocalDate data;

    @Column(nullable = false)
    private String nome;

    @Column(name = "criado_em", nullable = false, updatable = false)
    private OffsetDateTime criadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = OffsetDateTime.now();
    }

    public UUID getId() { return id; }
    public LocalDate getData() { return data; }
    public String getNome() { return nome; }
}

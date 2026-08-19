package br.mil.escalas.entity;

import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de um dia específico dentro de uma escala.
 * Responsabilidade: representar cada dia numerado (1-150) com sua data.
 */
@Entity
@Table(name = "escala_dias")
public class EscalaDia {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "escala_id", nullable = false)
    private Escala escala;

    @Column(nullable = false)
    private LocalDate data;

    @Column(name = "dia_numero", nullable = false)
    private Integer diaNumero;

    @Column(name = "criado_em", nullable = false, updatable = false)
    private OffsetDateTime criadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = OffsetDateTime.now();
    }

    public UUID getId() { return id; }
    public Escala getEscala() { return escala; }
    public LocalDate getData() { return data; }
    public Integer getDiaNumero() { return diaNumero; }
}

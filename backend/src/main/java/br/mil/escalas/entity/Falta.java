package br.mil.escalas.entity;

import br.mil.escalas.entity.enums.TipoFalta;
import jakarta.persistence.*;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de falta no serviço.
 * Responsabilidade: registrar tipo de falta e substituto opcional.
 */
@Entity
@Table(name = "faltas")
public class Falta {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @Column(name = "escala_dia_id", nullable = false)
    private UUID escalaDiaId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "militar_id", nullable = false)
    private Militar militar;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    private TipoFalta tipo;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "substituto_id")
    private Militar substituto;

    @Column(columnDefinition = "TEXT")
    private String observacao;

    @Column(name = "criado_em", nullable = false, updatable = false)
    private OffsetDateTime criadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = OffsetDateTime.now();
    }

    public UUID getId() { return id; }
    public UUID getEscalaDiaId() { return escalaDiaId; }
    public Militar getMilitar() { return militar; }
    public TipoFalta getTipo() { return tipo; }
    public Militar getSubstituto() { return substituto; }
}

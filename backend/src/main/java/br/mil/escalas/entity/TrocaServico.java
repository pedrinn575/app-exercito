package br.mil.escalas.entity;

import br.mil.escalas.entity.enums.StatusTroca;
import jakarta.persistence.*;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de troca de serviço entre militares.
 * Responsabilidade: registrar solicitação e status do fluxo de aprovação.
 */
@Entity
@Table(name = "trocas_servico")
public class TrocaServico {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "solicitante_id", nullable = false)
    private Militar solicitante;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "alvo_id", nullable = false)
    private Militar alvo;

    @Column(name = "escala_dia_id", nullable = false)
    private UUID escalaDiaId;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 30)
    private StatusTroca status = StatusTroca.SOLICITADA;

    @Column(columnDefinition = "TEXT")
    private String motivo;

    @Column(name = "criado_em", nullable = false, updatable = false)
    private OffsetDateTime criadoEm;

    @Column(name = "atualizado_em", nullable = false)
    private OffsetDateTime atualizadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = OffsetDateTime.now();
        atualizadoEm = OffsetDateTime.now();
    }

    public UUID getId() { return id; }
    public Militar getSolicitante() { return solicitante; }
    public Militar getAlvo() { return alvo; }
    public UUID getEscalaDiaId() { return escalaDiaId; }
    public StatusTroca getStatus() { return status; }
}

package br.mil.escalas.entity;

import br.mil.escalas.entity.enums.TipoEscala;
import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de escala (preta ou vermelha).
 * Responsabilidade: representar uma instância de escala com período definido.
 */
@Entity
@Table(name = "escalas")
public class Escala {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    private TipoEscala tipo;

    @Column(name = "numero_escala", nullable = false)
    private Integer numeroEscala;

    @Column(name = "data_inicio", nullable = false)
    private LocalDate dataInicio;

    @Column(name = "data_fim", nullable = false)
    private LocalDate dataFim;

    @Column(nullable = false)
    private Boolean ativa = true;

    @Column(name = "criado_em", nullable = false, updatable = false)
    private OffsetDateTime criadoEm;

    @Column(name = "atualizado_em", nullable = false)
    private OffsetDateTime atualizadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = OffsetDateTime.now();
        atualizadoEm = OffsetDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        atualizadoEm = OffsetDateTime.now();
    }

    public UUID getId() { return id; }
    public TipoEscala getTipo() { return tipo; }
    public void setTipo(TipoEscala tipo) { this.tipo = tipo; }
    public Integer getNumeroEscala() { return numeroEscala; }
    public LocalDate getDataInicio() { return dataInicio; }
    public LocalDate getDataFim() { return dataFim; }
    public Boolean getAtiva() { return ativa; }
}

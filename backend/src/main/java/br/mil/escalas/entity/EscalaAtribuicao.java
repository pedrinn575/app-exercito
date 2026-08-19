package br.mil.escalas.entity;

import br.mil.escalas.entity.enums.FuncaoEscala;
import jakarta.persistence.*;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de atribuição de militar a uma função em um dia de escala.
 * Responsabilidade: vincular militar + função (comandante, cabo, atirador, monitor).
 */
@Entity
@Table(name = "escala_atribuicoes")
public class EscalaAtribuicao {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "escala_dia_id", nullable = false)
    private EscalaDia escalaDia;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "militar_id", nullable = false)
    private Militar militar;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 30)
    private FuncaoEscala funcao;

    @Column(name = "criado_em", nullable = false, updatable = false)
    private OffsetDateTime criadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = OffsetDateTime.now();
    }

    public UUID getId() { return id; }
    public EscalaDia getEscalaDia() { return escalaDia; }
    public Militar getMilitar() { return militar; }
    public FuncaoEscala getFuncao() { return funcao; }
}

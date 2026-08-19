package br.mil.escalas.entity;

import br.mil.escalas.entity.enums.TipoMilitar;
import jakarta.persistence.*;
import java.time.OffsetDateTime;
import java.util.UUID;

/**
 * Entidade de militar escalado.
 * Responsabilidade: dados do militar (número, nome, tipo, posto).
 * Pode estar vinculado a um Usuario para login.
 */
@Entity
@Table(name = "militares")
public class Militar {

    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "usuario_id")
    private Usuario usuario;

    @Column(nullable = false, unique = true)
    private Integer numero;

    @Column(nullable = false)
    private String nome;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false, length = 20)
    private TipoMilitar tipo;

    @Column(nullable = false, length = 50)
    private String posto;

    @Column(nullable = false)
    private Boolean reserva = false;

    @Column(nullable = false)
    private Boolean ativo = true;

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
    public void setId(UUID id) { this.id = id; }

    public Usuario getUsuario() { return usuario; }
    public void setUsuario(Usuario usuario) { this.usuario = usuario; }

    public Integer getNumero() { return numero; }
    public void setNumero(Integer numero) { this.numero = numero; }

    public String getNome() { return nome; }
    public void setNome(String nome) { this.nome = nome; }

    public TipoMilitar getTipo() { return tipo; }
    public void setTipo(TipoMilitar tipo) { this.tipo = tipo; }

    public String getPosto() { return posto; }
    public void setPosto(String posto) { this.posto = posto; }

    public Boolean getReserva() { return reserva; }
    public void setReserva(Boolean reserva) { this.reserva = reserva; }

    public Boolean getAtivo() { return ativo; }
    public void setAtivo(Boolean ativo) { this.ativo = ativo; }
}

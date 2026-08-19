-- =============================================================================
-- V001__schema_inicial.sql
-- Schema inicial do Sistema de Escalas Militares
-- Responsabilidade: criar todas as tabelas, índices, constraints e enums
-- =============================================================================

-- Extensão para UUIDs
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- -----------------------------------------------------------------------------
-- Tabela: usuarios
-- Armazena credenciais e papel de acesso (ADMINISTRADOR ou MILITAR)
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email           VARCHAR(255) NOT NULL,
    senha_hash      VARCHAR(255) NOT NULL,
    papel           VARCHAR(20)  NOT NULL,
    ativo           BOOLEAN      NOT NULL DEFAULT TRUE,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT uk_usuarios_email UNIQUE (email),
    CONSTRAINT chk_usuarios_papel CHECK (papel IN ('ADMINISTRADOR', 'MILITAR'))
);

CREATE INDEX idx_usuarios_email ON usuarios (email);
CREATE INDEX idx_usuarios_papel ON usuarios (papel);
CREATE INDEX idx_usuarios_ativo ON usuarios (ativo);

-- -----------------------------------------------------------------------------
-- Tabela: militares
-- Dados dos militares escalados (vinculados opcionalmente a um usuario)
-- -----------------------------------------------------------------------------
CREATE TABLE militares (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id      UUID,
    numero          INTEGER      NOT NULL,
    nome            VARCHAR(255) NOT NULL,
    tipo            VARCHAR(20)  NOT NULL,
    posto           VARCHAR(50)  NOT NULL,
    reserva         BOOLEAN      NOT NULL DEFAULT FALSE,
    ativo           BOOLEAN      NOT NULL DEFAULT TRUE,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_militares_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE SET NULL,
    CONSTRAINT uk_militares_numero UNIQUE (numero),
    CONSTRAINT chk_militares_tipo CHECK (tipo IN ('ATIRADOR', 'MONITOR'))
);

CREATE INDEX idx_militares_usuario_id ON militares (usuario_id);
CREATE INDEX idx_militares_numero ON militares (numero);
CREATE INDEX idx_militares_tipo ON militares (tipo);
CREATE INDEX idx_militares_reserva ON militares (reserva);
CREATE INDEX idx_militares_ativo ON militares (ativo);

-- -----------------------------------------------------------------------------
-- Tabela: feriados
-- Datas que disparam escala vermelha
-- -----------------------------------------------------------------------------
CREATE TABLE feriados (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    data            DATE         NOT NULL,
    nome            VARCHAR(255) NOT NULL,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT uk_feriados_data UNIQUE (data)
);

CREATE INDEX idx_feriados_data ON feriados (data);

-- -----------------------------------------------------------------------------
-- Tabela: escalas
-- Instância de escala (preta ou vermelha) com período definido
-- -----------------------------------------------------------------------------
CREATE TABLE escalas (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tipo            VARCHAR(20)  NOT NULL,
    numero_escala   INTEGER      NOT NULL,
    data_inicio     DATE         NOT NULL,
    data_fim        DATE         NOT NULL,
    ativa           BOOLEAN      NOT NULL DEFAULT TRUE,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_escalas_tipo CHECK (tipo IN ('PRETA', 'VERMELHA')),
    CONSTRAINT chk_escalas_periodo CHECK (data_fim >= data_inicio)
);

CREATE INDEX idx_escalas_tipo ON escalas (tipo);
CREATE INDEX idx_escalas_ativa ON escalas (ativa);
CREATE INDEX idx_escalas_data_inicio ON escalas (data_inicio);

-- -----------------------------------------------------------------------------
-- Tabela: escala_dias
-- Cada dia dentro de uma escala (numeração 1-150 para preta)
-- -----------------------------------------------------------------------------
CREATE TABLE escala_dias (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    escala_id       UUID         NOT NULL,
    data            DATE         NOT NULL,
    dia_numero      INTEGER      NOT NULL,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_escala_dias_escala FOREIGN KEY (escala_id)
        REFERENCES escalas (id) ON DELETE CASCADE,
    CONSTRAINT uk_escala_dias_escala_data UNIQUE (escala_id, data),
    CONSTRAINT chk_escala_dias_numero CHECK (dia_numero >= 1 AND dia_numero <= 150)
);

CREATE INDEX idx_escala_dias_escala_id ON escala_dias (escala_id);
CREATE INDEX idx_escala_dias_data ON escala_dias (data);
CREATE INDEX idx_escala_dias_dia_numero ON escala_dias (dia_numero);

-- -----------------------------------------------------------------------------
-- Tabela: escala_atribuicoes
-- Militar atribuído a uma função em um dia específico
-- -----------------------------------------------------------------------------
CREATE TABLE escala_atribuicoes (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    escala_dia_id   UUID         NOT NULL,
    militar_id      UUID         NOT NULL,
    funcao          VARCHAR(30)  NOT NULL,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_atribuicoes_dia FOREIGN KEY (escala_dia_id)
        REFERENCES escala_dias (id) ON DELETE CASCADE,
    CONSTRAINT fk_atribuicoes_militar FOREIGN KEY (militar_id)
        REFERENCES militares (id) ON DELETE RESTRICT,
    CONSTRAINT uk_atribuicoes_dia_militar UNIQUE (escala_dia_id, militar_id),
    CONSTRAINT chk_atribuicoes_funcao CHECK (
        funcao IN ('COMANDANTE_GUARDA', 'CABO_DIA', 'ATIRADOR', 'MONITOR')
    )
);

CREATE INDEX idx_atribuicoes_escala_dia_id ON escala_atribuicoes (escala_dia_id);
CREATE INDEX idx_atribuicoes_militar_id ON escala_atribuicoes (militar_id);
CREATE INDEX idx_atribuicoes_funcao ON escala_atribuicoes (funcao);

-- -----------------------------------------------------------------------------
-- Tabela: trocas_servico
-- Solicitações de troca entre militares com fluxo de aprovação
-- -----------------------------------------------------------------------------
CREATE TABLE trocas_servico (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    solicitante_id      UUID         NOT NULL,
    alvo_id             UUID         NOT NULL,
    escala_dia_id       UUID         NOT NULL,
    status              VARCHAR(30)  NOT NULL DEFAULT 'SOLICITADA',
    motivo              TEXT,
    criado_em           TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    atualizado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_trocas_solicitante FOREIGN KEY (solicitante_id)
        REFERENCES militares (id) ON DELETE RESTRICT,
    CONSTRAINT fk_trocas_alvo FOREIGN KEY (alvo_id)
        REFERENCES militares (id) ON DELETE RESTRICT,
    CONSTRAINT fk_trocas_escala_dia FOREIGN KEY (escala_dia_id)
        REFERENCES escala_dias (id) ON DELETE RESTRICT,
    CONSTRAINT chk_trocas_status CHECK (
        status IN ('SOLICITADA', 'ACEITA_ALVO', 'APROVADA', 'REJEITADA')
    ),
    CONSTRAINT chk_trocas_militares_diferentes CHECK (solicitante_id <> alvo_id)
);

CREATE INDEX idx_trocas_solicitante_id ON trocas_servico (solicitante_id);
CREATE INDEX idx_trocas_alvo_id ON trocas_servico (alvo_id);
CREATE INDEX idx_trocas_escala_dia_id ON trocas_servico (escala_dia_id);
CREATE INDEX idx_trocas_status ON trocas_servico (status);

-- -----------------------------------------------------------------------------
-- Tabela: faltas
-- Registro de faltas com tipo e substituto opcional
-- -----------------------------------------------------------------------------
CREATE TABLE faltas (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    escala_dia_id   UUID         NOT NULL,
    militar_id      UUID         NOT NULL,
    tipo            VARCHAR(20)  NOT NULL,
    substituto_id   UUID,
    observacao      TEXT,
    criado_em       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    atualizado_em   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_faltas_escala_dia FOREIGN KEY (escala_dia_id)
        REFERENCES escala_dias (id) ON DELETE RESTRICT,
    CONSTRAINT fk_faltas_militar FOREIGN KEY (militar_id)
        REFERENCES militares (id) ON DELETE RESTRICT,
    CONSTRAINT fk_faltas_substituto FOREIGN KEY (substituto_id)
        REFERENCES militares (id) ON DELETE SET NULL,
    CONSTRAINT uk_faltas_dia_militar UNIQUE (escala_dia_id, militar_id),
    CONSTRAINT chk_faltas_tipo CHECK (tipo IN ('JUSTIFICADA', 'NAO_JUSTIFICADA'))
);

CREATE INDEX idx_faltas_escala_dia_id ON faltas (escala_dia_id);
CREATE INDEX idx_faltas_militar_id ON faltas (militar_id);
CREATE INDEX idx_faltas_tipo ON faltas (tipo);
CREATE INDEX idx_faltas_substituto_id ON faltas (substituto_id);

-- -----------------------------------------------------------------------------
-- Trigger: atualizar updated_at automaticamente
-- -----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION atualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_usuarios_updated
    BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_militares_updated
    BEFORE UPDATE ON militares
    FOR EACH ROW EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_escalas_updated
    BEFORE UPDATE ON escalas
    FOR EACH ROW EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_trocas_updated
    BEFORE UPDATE ON trocas_servico
    FOR EACH ROW EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_faltas_updated
    BEFORE UPDATE ON faltas
    FOR EACH ROW EXECUTE FUNCTION atualizar_timestamp();

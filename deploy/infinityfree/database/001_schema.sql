-- =============================================================================
-- Schema do Sistema de Escalas Militares
-- Todas as tabelas possuem PK, FK, índices e constraints
-- =============================================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    nome          TEXT    NOT NULL,
    numero         TEXT    NOT NULL UNIQUE,
    numero_monitor TEXT    UNIQUE,
    email         TEXT    UNIQUE,
    senha_hash    TEXT    NOT NULL,
    perfil        TEXT    NOT NULL CHECK (perfil IN ('admin', 'atirador', 'monitor')),
    ativo         INTEGER NOT NULL DEFAULT 1,
    criado_em     TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    atualizado_em TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
);

CREATE INDEX IF NOT EXISTS idx_usuarios_numero ON usuarios(numero);
CREATE INDEX IF NOT EXISTS idx_usuarios_perfil ON usuarios(perfil);

CREATE TABLE IF NOT EXISTS escalas (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    tipo          TEXT    NOT NULL CHECK (tipo IN ('preta', 'vermelha')),
    data_servico  TEXT    NOT NULL,
    observacao    TEXT,
    -- Posição da fila em que cada função começou neste dia
    inicio_monitor  INTEGER NOT NULL DEFAULT 0,
    inicio_atirador INTEGER NOT NULL DEFAULT 0,
    criado_por    INTEGER REFERENCES usuarios(id),
    criado_em     TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    UNIQUE (tipo, data_servico)
);

CREATE INDEX IF NOT EXISTS idx_escalas_tipo_data ON escalas(tipo, data_servico);

CREATE TABLE IF NOT EXISTS escala_postos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    escala_id     INTEGER NOT NULL REFERENCES escalas(id) ON DELETE CASCADE,
    usuario_id    INTEGER NOT NULL REFERENCES usuarios(id),
    funcao        TEXT    NOT NULL CHECK (funcao IN ('monitor', 'atirador', 'reserva')),
    status        TEXT    NOT NULL DEFAULT 'escalado' CHECK (status IN ('escalado', 'falta_injustificada', 'falta_justificada', 'substituido')),
    UNIQUE (escala_id, usuario_id)
);

CREATE INDEX IF NOT EXISTS idx_postos_escala ON escala_postos(escala_id);
CREATE INDEX IF NOT EXISTS idx_postos_usuario ON escala_postos(usuario_id);

CREATE TABLE IF NOT EXISTS trocas (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    escala_posto_id   INTEGER NOT NULL REFERENCES escala_postos(id) ON DELETE CASCADE,
    solicitante_id    INTEGER NOT NULL REFERENCES usuarios(id),
    destino_id        INTEGER NOT NULL REFERENCES usuarios(id),
    status            TEXT    NOT NULL DEFAULT 'pendente'
                      CHECK (status IN ('pendente', 'aceito_destino', 'aprovado', 'recusado', 'cancelado')),
    motivo            TEXT,
    criado_em         TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    atualizado_em     TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    CHECK (solicitante_id <> destino_id)
);

CREATE INDEX IF NOT EXISTS idx_trocas_status ON trocas(status);

CREATE TABLE IF NOT EXISTS faltas (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    escala_posto_id INTEGER NOT NULL REFERENCES escala_postos(id) ON DELETE CASCADE,
    tipo            TEXT    NOT NULL CHECK (tipo IN ('justificada', 'injustificada')),
    substituto_id   INTEGER REFERENCES usuarios(id),
    registrado_por  INTEGER REFERENCES usuarios(id),
    observacao      TEXT,
    criado_em       TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
);

CREATE INDEX IF NOT EXISTS idx_faltas_posto ON faltas(escala_posto_id);

CREATE TABLE IF NOT EXISTS marmitas (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id    INTEGER NOT NULL REFERENCES usuarios(id),
    data_pedido   TEXT    NOT NULL,
    quantidade    INTEGER NOT NULL DEFAULT 1,
    status        TEXT    NOT NULL DEFAULT 'pendente' CHECK (status IN ('pendente', 'confirmado', 'cancelado')),
    criado_em     TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
);

CREATE INDEX IF NOT EXISTS idx_marmitas_data ON marmitas(data_pedido);

-- =============================================================================
-- V002__dados_seed_dev.sql
-- Dados de desenvolvimento: feriados de exemplo
-- Usuários e militares são criados pelo DevDataSeeder (BCrypt correto)
-- Execute V001 antes deste script
-- =============================================================================

INSERT INTO feriados (data, nome) VALUES
    ('2026-01-01', 'Confraternização Universal'),
    ('2026-04-21', 'Tiradentes'),
    ('2026-05-01', 'Dia do Trabalho'),
    ('2026-09-07', 'Independência do Brasil'),
    ('2026-12-25', 'Natal')
ON CONFLICT (data) DO NOTHING;

<?php
/**
 * Configurações gerais da aplicação.
 */

return [
    'name'       => 'Sistema de Escalas',
    'unit'       => 'Tiro de Guerra',
    'timezone'   => 'America/Sao_Paulo',
    'session_key'=> 'escalas_user',
    // Intervalo mínimo entre serviços (escala preta/vermelha)
    'intervalo_horas' => 48,
    // 11 pessoas por dia: 2 monitores + 9 atiradores
    'vagas_por_dia' => 11,
    'monitores_por_dia' => 2,
    // Texto da previsão impressa, no modelo do TG.
    'fiscal_servico' => 'ST MARTINS',
    'quartel'        => 'Ribeirão Preto',
    'assinatura'     => 'ADRIANO MARTINS DO NASCIMENTO – ST',
    'cargo_assinatura' => 'Chefe de Instrução do TG 02-031',
];

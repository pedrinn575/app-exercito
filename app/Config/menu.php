<?php
/**
 * Telas do sistema e as permissões que as liberam.
 *
 * 'fixo'     → sempre visível para quem está logado
 * 'so_tropa' → não faz sentido para o administrador (ele não tira serviço)
 * 'so_admin' → nunca pode ser liberado para a tropa
 */

return [
    'dashboard' => [
        'rota'  => '/dashboard',
        'label' => 'Dashboard',
        'fixo'  => true,
    ],
    'meus_servicos' => [
        'rota'     => '/meus-servicos',
        'label'    => 'Meus serviços',
        'so_tropa' => true,
    ],
    'calendario' => [
        'rota'  => '/calendario',
        'label' => 'Calendário',
    ],
    'escala_preta' => [
        'rota'  => '/escalas/preta',
        'label' => 'Escala Preta',
    ],
    'escala_vermelha' => [
        'rota'  => '/escalas/vermelha',
        'label' => 'Escala Vermelha',
    ],
    'trocas' => [
        'rota'  => '/trocas',
        'label' => 'Trocas',
    ],
    'faltas' => [
        'rota'  => '/faltas',
        'label' => 'Faltas',
    ],
    'marmitas' => [
        'rota'  => '/marmitas',
        'label' => 'Marmitas',
    ],
    'militares' => [
        'rota'  => '/militares',
        'label' => 'Militares',
    ],
    'relatorios' => [
        'rota'  => '/relatorios',
        'label' => 'Relatórios',
    ],
    'permissoes' => [
        'rota'     => '/permissoes',
        'label'    => 'Permissões',
        'so_admin' => true,
    ],
];

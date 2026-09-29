<?php
/**
 * Definição de rotas HTTP.
 */

use App\Controllers\AuthController;
use App\Controllers\CalendarioController;
use App\Controllers\DashboardController;
use App\Controllers\EscalaController;
use App\Controllers\FaltaController;
use App\Controllers\MarmitaController;
use App\Controllers\MilitarController;
use App\Controllers\PermissaoController;
use App\Controllers\RelatorioController;
use App\Controllers\ServicoController;
use App\Controllers\TrocaController;

/** @var \App\Config\Router $router */

$router->get('/', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/meus-servicos', [ServicoController::class, 'index']);

$router->get('/calendario', [CalendarioController::class, 'index']);
$router->post('/calendario/gerar', [CalendarioController::class, 'gerarMes']);
$router->post('/feriados', [CalendarioController::class, 'salvarFeriado']);
$router->post('/feriados/{id}/remover', [CalendarioController::class, 'removerFeriado']);

$router->get('/marmitas', [MarmitaController::class, 'index']);
$router->post('/marmitas', [MarmitaController::class, 'store']);
$router->post('/marmitas/{id}/atualizar', [MarmitaController::class, 'atualizar']);
$router->post('/marmitas/{id}/aprovar', [MarmitaController::class, 'aprovar']);
$router->post('/marmitas/{id}/negar', [MarmitaController::class, 'negar']);
$router->post('/marmitas/{id}/alteracao', [MarmitaController::class, 'pedirAlteracao']);
$router->post('/marmitas/{id}/cancelar', [MarmitaController::class, 'cancelar']);

$router->get('/militares', [MilitarController::class, 'index']);
$router->get('/militares/criar', [MilitarController::class, 'create']);
$router->post('/militares', [MilitarController::class, 'store']);
$router->get('/militares/{id}/editar', [MilitarController::class, 'edit']);
$router->post('/militares/{id}', [MilitarController::class, 'update']);
$router->post('/militares/{id}/desativar', [MilitarController::class, 'destroy']);

$router->get('/escalas/preta', [EscalaController::class, 'preta']);
$router->get('/escalas/vermelha', [EscalaController::class, 'vermelha']);
$router->post('/escalas/gerar', [EscalaController::class, 'gerar']);
$router->post('/escalas/excluir', [EscalaController::class, 'excluir']);
$router->post('/escalas/{id}/postos', [EscalaController::class, 'adicionarPosto']);
$router->post('/postos/{id}/funcao', [EscalaController::class, 'alterarFuncao']);
$router->post('/postos/{id}/remover', [EscalaController::class, 'removerPosto']);
$router->get('/escalas/{tipo}/{data}', [EscalaController::class, 'show']);

$router->get('/trocas', [TrocaController::class, 'index']);
$router->post('/trocas', [TrocaController::class, 'store']);
$router->post('/trocas/{id}/aceitar', [TrocaController::class, 'aceitar']);
$router->post('/trocas/{id}/aprovar', [TrocaController::class, 'aprovar']);
$router->post('/trocas/{id}/recusar', [TrocaController::class, 'recusar']);

$router->get('/faltas', [FaltaController::class, 'index']);
$router->post('/faltas/justificada', [FaltaController::class, 'justificada']);
$router->post('/faltas/injustificada', [FaltaController::class, 'injustificada']);

$router->get('/relatorios', [RelatorioController::class, 'index']);

$router->get('/permissoes', [PermissaoController::class, 'index']);
$router->post('/permissoes', [PermissaoController::class, 'salvar']);

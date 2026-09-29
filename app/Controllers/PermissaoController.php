<?php
namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Services\PermissaoService;
use App\Utils\Auth;
use App\Utils\View;

final class PermissaoController
{
    public function __construct(
        private PermissaoService $service = new PermissaoService()
    ) {}

    public function index(): void
    {
        Auth::requireAdmin();

        View::render('permissoes/index', [
            'telas'     => $this->service->telasConfiguraveis(),
            'perfis'    => PermissaoService::PERFIS_TROPA,
            'matriz'    => $this->service->matriz(),
            'pageTitle' => 'Permissões',
        ]);
    }

    public function salvar(): void
    {
        Auth::requireAdmin();
        try {
            $this->service->salvar($_POST['permissoes'] ?? []);
            View::redirect('/permissoes', 'Permissões atualizadas.');
        } catch (HttpException $e) {
            View::redirect('/permissoes', null, $e->getMessage());
        }
    }
}

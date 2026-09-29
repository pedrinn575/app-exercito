<?php
/**
 * PermissaoService — decide quais telas cada perfil enxerga.
 *
 * O administrador vê tudo, menos o que é exclusivo da tropa.
 * Os demais perfis seguem o que estiver marcado na tela de Permissões.
 */

namespace App\Services;

use App\Repositories\PermissaoRepository;

final class PermissaoService
{
    /** Perfis que o administrador configura */
    public const PERFIS_TROPA = ['atirador', 'monitor'];

    /** @var array<string, array<string, bool>>|null */
    private static ?array $cache = null;

    public function __construct(
        private PermissaoRepository $repo = new PermissaoRepository()
    ) {}

    /**
     * Telas do sistema, na ordem do menu.
     *
     * @return array<string, array<string, mixed>>
     */
    public function telas(): array
    {
        return require APP_PATH . '/Config/menu.php';
    }

    /**
     * Telas que o administrador pode liberar ou bloquear.
     *
     * @return array<string, array<string, mixed>>
     */
    public function telasConfiguraveis(): array
    {
        return array_filter(
            $this->telas(),
            fn(array $item) => empty($item['fixo']) && empty($item['so_admin'])
        );
    }

    public function permite(string $perfil, string $chave): bool
    {
        $telas = $this->telas();
        $item = $telas[$chave] ?? null;
        if ($item === null) {
            return false;
        }

        if (!empty($item['fixo'])) {
            return true;
        }

        if ($perfil === 'admin') {
            // O admin não tira serviço, então não tem escala própria
            return empty($item['so_tropa']);
        }

        if (!empty($item['so_admin'])) {
            return false;
        }

        return $this->mapa()[$perfil][$chave] ?? false;
    }

    /**
     * Itens de menu liberados para o perfil.
     *
     * @return list<array{rota:string,label:string}>
     */
    public function menuDoPerfil(string $perfil): array
    {
        $itens = [];
        foreach ($this->telas() as $chave => $item) {
            if ($this->permite($perfil, $chave)) {
                $itens[] = ['rota' => $item['rota'], 'label' => $item['label']];
            }
        }
        return $itens;
    }

    /** @return array<string, array<string, bool>> */
    public function matriz(): array
    {
        $mapa = $this->mapa();
        $matriz = [];

        foreach (self::PERFIS_TROPA as $perfil) {
            foreach ($this->telasConfiguraveis() as $chave => $_) {
                $matriz[$perfil][$chave] = $mapa[$perfil][$chave] ?? false;
            }
        }
        return $matriz;
    }

    /**
     * Salva o que veio da tela de permissões (checkbox marcado = liberado).
     *
     * @param array<string, array<string, mixed>> $enviado
     */
    public function salvar(array $enviado): void
    {
        $mapa = [];
        foreach (self::PERFIS_TROPA as $perfil) {
            foreach ($this->telasConfiguraveis() as $chave => $_) {
                $mapa[$perfil][$chave] = !empty($enviado[$perfil][$chave]);
            }
        }

        $this->repo->salvar($mapa);
        self::$cache = null;
    }

    /** @return array<string, array<string, bool>> */
    private function mapa(): array
    {
        if (self::$cache === null) {
            self::$cache = $this->repo->todas();
        }
        return self::$cache;
    }
}

/**
 * Cabeçalho da aplicação com nome do usuário e logout.
 */

import { useAuth } from '../services/AuthContext';
import { Button } from './Button';

export function Header() {
  const { usuario, logout } = useAuth();

  return (
    <header className="header">
      <div className="header-brand">
        <span className="header-icon">⚔</span>
        <h1>Sistema de Escalas</h1>
      </div>
      <div className="header-user">
        <span>{usuario?.nome}</span>
        <span className="header-papel">{usuario?.papel}</span>
        <Button variant="secondary" onClick={logout}>
          Sair
        </Button>
      </div>
    </header>
  );
}

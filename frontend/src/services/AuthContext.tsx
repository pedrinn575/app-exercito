/**
 * Contexto de autenticação global.
 * Responsabilidade: manter estado do usuário logado e expor login/logout.
 */

import {
  createContext,
  useContext,
  useState,
  useEffect,
  type ReactNode,
} from 'react';
import * as authService from '../services/authService';
import type { Usuario, LoginRequest } from '../types';

interface AuthContextData {
  usuario: Usuario | null;
  carregando: boolean;
  login: (credenciais: LoginRequest) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextData>({} as AuthContextData);

/**
 * Provider que envolve a aplicação com estado de autenticação.
 */
export function AuthProvider({ children }: { children: ReactNode }) {
  const [usuario, setUsuario] = useState<Usuario | null>(null);
  const [carregando, setCarregando] = useState(true);

  // Ao montar, tenta recuperar perfil se houver token
  useEffect(() => {
    if (authService.isAuthenticated()) {
      authService
        .obterPerfil()
        .then(setUsuario)
        .catch(() => authService.logout())
        .finally(() => setCarregando(false));
    } else {
      setCarregando(false);
    }
  }, []);

  const login = async (credenciais: LoginRequest) => {
    const response = await authService.login(credenciais);
    setUsuario(response.usuario);
  };

  const logout = () => {
    authService.logout();
    setUsuario(null);
  };

  return (
    <AuthContext.Provider value={{ usuario, carregando, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

/** Hook para acessar o contexto de autenticação */
export function useAuth() {
  return useContext(AuthContext);
}

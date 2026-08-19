/**
 * Service de autenticação.
 * Responsabilidade: login, logout e consulta de perfil.
 * Fluxo: LoginPage → authService.login() → apiClient → backend.
 */

import { apiClient, setToken, removeToken, getToken } from './apiClient';
import type { LoginRequest, TokenResponse, Usuario } from '../types';

/**
 * Realiza login e armazena token JWT.
 *
 * @param credenciais email e senha
 * @returns resposta com token e dados do usuário
 */
export async function login(credenciais: LoginRequest): Promise<TokenResponse> {
  const response = await apiClient<TokenResponse>('/auth/login', {
    method: 'POST',
    body: JSON.stringify(credenciais),
    autenticado: false,
  });

  setToken(response.token);
  return response;
}

/**
 * Remove token e encerra sessão.
 */
export function logout(): void {
  removeToken();
}

/**
 * Verifica se há token armazenado.
 */
export function isAuthenticated(): boolean {
  return getToken() !== null;
}

/**
 * Obtém perfil do usuário autenticado.
 *
 * @returns dados do usuário logado
 */
export async function obterPerfil(): Promise<Usuario> {
  return apiClient<Usuario>('/auth/me');
}

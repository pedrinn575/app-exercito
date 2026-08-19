/**
 * Cliente HTTP centralizado para comunicação com a API.
 * Responsabilidade: executar requisições, injetar token JWT e tratar erros.
 * Regra: toda comunicação HTTP do frontend passa por este módulo.
 */

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';

/** Chave do token no localStorage */
const TOKEN_KEY = 'escalas_token';

/**
 * Obtém o token JWT armazenado.
 */
export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

/**
 * Armazena o token JWT.
 */
export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

/**
 * Remove o token JWT (logout).
 */
export function removeToken(): void {
  localStorage.removeItem(TOKEN_KEY);
}

/**
 * Opções estendidas para requisições.
 */
interface RequestOptions extends RequestInit {
  /** Se false, não envia token (ex: login) */
  autenticado?: boolean;
}

/**
 * Executa requisição HTTP à API.
 *
 * @param endpoint caminho relativo (ex: /auth/login)
 * @param options opções fetch estendidas
 * @returns corpo da resposta parseado como JSON
 */
export async function apiClient<T>(
  endpoint: string,
  options: RequestOptions = {}
): Promise<T> {
  const { autenticado = true, headers, ...rest } = options;

  const requestHeaders: Record<string, string> = {
    'Content-Type': 'application/json',
    ...(headers as Record<string, string>),
  };

  // Injeta token Bearer se autenticado
  if (autenticado) {
    const token = getToken();
    if (token) {
      requestHeaders['Authorization'] = `Bearer ${token}`;
    }
  }

  const response = await fetch(`${API_URL}${endpoint}`, {
    ...rest,
    headers: requestHeaders,
  });

  // Resposta sem corpo (204)
  if (response.status === 204) {
    return {} as T;
  }

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.mensagem || data.erro || 'Erro na requisição');
  }

  return data as T;
}

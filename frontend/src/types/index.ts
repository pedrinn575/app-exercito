/**
 * Papéis de acesso do sistema.
 */
export type Papel = 'ADMINISTRADOR' | 'MILITAR';

/**
 * Dados públicos do usuário autenticado.
 */
export interface Usuario {
  id: string;
  email: string;
  papel: Papel;
  nome: string;
}

/**
 * Payload de login.
 */
export interface LoginRequest {
  email: string;
  senha: string;
}

/**
 * Resposta do endpoint de login.
 */
export interface TokenResponse {
  token: string;
  tipo: string;
  usuario: Usuario;
}

/**
 * Tipo de militar.
 */
export type TipoMilitar = 'ATIRADOR' | 'MONITOR';

/**
 * Dados de militar.
 */
export interface Militar {
  id: string;
  numero: number;
  nome: string;
  tipo: TipoMilitar;
  posto: string;
  reserva: boolean;
  ativo: boolean;
}

/**
 * Tipo de escala.
 */
export type TipoEscala = 'PRETA' | 'VERMELHA';

/**
 * Resposta de consulta de escala.
 */
export interface EscalaResponse {
  tipo: TipoEscala;
  dias: EscalaDia[];
  mensagem: string;
}

/**
 * Dia da escala.
 */
export interface EscalaDia {
  id: string;
  data: string;
  diaNumero: number;
  atribuicoes: Atribuicao[];
}

/**
 * Atribuição de militar a função.
 */
export interface Atribuicao {
  militarId: string;
  militarNome: string;
  militarNumero: number;
  funcao: string;
}

/**
 * Resposta padronizada de erro da API.
 */
export interface ErroResponse {
  timestamp: string;
  status: number;
  erro: string;
  mensagem: string;
  caminho: string;
}

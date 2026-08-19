/**
 * Service de escalas (preta e vermelha).
 */

import { apiClient } from './apiClient';
import type { EscalaResponse } from '../types';

/** Consulta escala preta ativa */
export async function consultarEscalaPreta(): Promise<EscalaResponse> {
  return apiClient<EscalaResponse>('/escalas/preta');
}

/** Consulta escala vermelha ativa */
export async function consultarEscalaVermelha(): Promise<EscalaResponse> {
  return apiClient<EscalaResponse>('/escalas/vermelha');
}

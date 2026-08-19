/**
 * Service de militares.
 * Responsabilidade: listagem e cadastro de militares via API.
 */

import { apiClient } from './apiClient';
import type { Militar } from '../types';

/** Lista todos os militares ativos */
export async function listarMilitares(): Promise<Militar[]> {
  return apiClient<Militar[]>('/militares');
}

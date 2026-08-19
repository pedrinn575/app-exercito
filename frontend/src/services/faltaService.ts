/**
 * Service de faltas.
 */

import { apiClient } from './apiClient';

/** Lista faltas registradas */
export async function listarFaltas(): Promise<unknown[]> {
  return apiClient<unknown[]>('/faltas');
}

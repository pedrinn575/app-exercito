/**
 * Service de trocas de serviço.
 */

import { apiClient } from './apiClient';

/** Lista solicitações de troca */
export async function listarTrocas(): Promise<unknown[]> {
  return apiClient<unknown[]>('/trocas');
}

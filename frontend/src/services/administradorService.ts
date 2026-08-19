/**
 * Service de administradores.
 */

import { apiClient } from './apiClient';
import type { Usuario } from '../types';

/** Lista administradores cadastrados */
export async function listarAdministradores(): Promise<Usuario[]> {
  return apiClient<Usuario[]>('/administradores');
}

/**
 * Rota protegida — redireciona para login se não autenticado.
 */

import { Navigate } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
  const { usuario, carregando } = useAuth();

  if (carregando) {
    return <div className="loading">Carregando...</div>;
  }

  if (!usuario) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
}

/**
 * Componente raiz da aplicação.
 * Responsabilidade: definir rotas e layout geral.
 */

import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './services/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Layout } from './components/Layout';
import { LoginPage } from './pages/LoginPage';
import { DashboardPage } from './pages/DashboardPage';
import { MilitaresPage } from './pages/MilitaresPage';
import { AdministradoresPage } from './pages/AdministradoresPage';
import { EscalaPretaPage } from './pages/EscalaPretaPage';
import { EscalaVermelhaPage } from './pages/EscalaVermelhaPage';
import { TrocaServicoPage } from './pages/TrocaServicoPage';
import { FaltasPage } from './pages/FaltasPage';
import { RelatoriosPage } from './pages/RelatoriosPage';

/** Wrapper que aplica Layout às páginas autenticadas */
function AuthenticatedLayout({ children }: { children: React.ReactNode }) {
  return (
    <ProtectedRoute>
      <Layout>{children}</Layout>
    </ProtectedRoute>
  );
}

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/dashboard" element={<AuthenticatedLayout><DashboardPage /></AuthenticatedLayout>} />
          <Route path="/militares" element={<AuthenticatedLayout><MilitaresPage /></AuthenticatedLayout>} />
          <Route path="/administradores" element={<AuthenticatedLayout><AdministradoresPage /></AuthenticatedLayout>} />
          <Route path="/escala-preta" element={<AuthenticatedLayout><EscalaPretaPage /></AuthenticatedLayout>} />
          <Route path="/escala-vermelha" element={<AuthenticatedLayout><EscalaVermelhaPage /></AuthenticatedLayout>} />
          <Route path="/trocas" element={<AuthenticatedLayout><TrocaServicoPage /></AuthenticatedLayout>} />
          <Route path="/faltas" element={<AuthenticatedLayout><FaltasPage /></AuthenticatedLayout>} />
          <Route path="/relatorios" element={<AuthenticatedLayout><RelatoriosPage /></AuthenticatedLayout>} />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;

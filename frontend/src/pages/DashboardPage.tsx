/**
 * Página inicial — Dashboard.
 */

import { Link } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';

export function DashboardPage() {
  const { usuario } = useAuth();

  const modulos = [
    { nome: 'Militares', desc: 'Cadastro e gestão de atiradores e monitores', path: '/militares' },
    { nome: 'Escala Preta', desc: 'Escala de dias úteis (1-150, 11 pessoas/dia)', path: '/escala-preta' },
    { nome: 'Escala Vermelha', desc: 'Escala de fins de semana e feriados (24h)', path: '/escala-vermelha' },
    { nome: 'Trocas', desc: 'Solicitação e aprovação de trocas de serviço', path: '/trocas' },
    { nome: 'Faltas', desc: 'Controle de faltas justificadas e não justificadas', path: '/faltas' },
    { nome: 'Relatórios', desc: 'Relatórios de faltas e serviços', path: '/relatorios' },
  ];

  return (
    <div className="dashboard">
      <h2>Bem-vindo, {usuario?.nome}</h2>
      <p className="dashboard-subtitulo">
        Painel de controle do sistema de escalas militares
      </p>

      <div className="dashboard-grid">
        {modulos.map((mod) => (
          <Link key={mod.path} to={mod.path} className="dashboard-card">
            <h3>{mod.nome}</h3>
            <p>{mod.desc}</p>
          </Link>
        ))}
      </div>
    </div>
  );
}

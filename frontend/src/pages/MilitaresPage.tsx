/**
 * Página de listagem de militares.
 * Comunicação via militarService (nunca HTTP direto).
 */

import { useEffect, useState } from 'react';
import { listarMilitares } from '../services/militarService';
import type { Militar } from '../types';
import { StubPage } from '../components/StubPage';

export function MilitaresPage() {
  const [militares, setMilitares] = useState<Militar[]>([]);
  const [erro, setErro] = useState('');

  useEffect(() => {
    listarMilitares()
      .then(setMilitares)
      .catch((err) => setErro(err.message));
  }, []);

  if (erro) {
    return <StubPage titulo="Militares" descricao={`Erro ao carregar: ${erro}`} />;
  }

  return (
    <div>
      <h2>Militares</h2>
      {militares.length === 0 ? (
        <StubPage
          titulo="Nenhum militar encontrado"
          descricao="Cadastre militares ou execute o seed do banco de dados."
        />
      ) : (
        <table className="data-table">
          <thead>
            <tr>
              <th>Nº</th>
              <th>Nome</th>
              <th>Tipo</th>
              <th>Posto</th>
              <th>Reserva</th>
            </tr>
          </thead>
          <tbody>
            {militares.map((m) => (
              <tr key={m.id}>
                <td>{m.numero}</td>
                <td>{m.nome}</td>
                <td>{m.tipo}</td>
                <td>{m.posto}</td>
                <td>{m.reserva ? 'Sim' : 'Não'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}

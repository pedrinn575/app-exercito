/**
 * Página da escala vermelha.
 */

import { useEffect, useState } from 'react';
import { consultarEscalaVermelha } from '../services/escalaService';
import type { EscalaResponse } from '../types';
import { StubPage } from '../components/StubPage';

export function EscalaVermelhaPage() {
  const [escala, setEscala] = useState<EscalaResponse | null>(null);

  useEffect(() => {
    consultarEscalaVermelha().then(setEscala).catch(console.error);
  }, []);

  return (
    <div>
      <h2>Escala Vermelha</h2>
      {escala && (
        <StubPage
          titulo="Escala Vermelha — Fins de Semana e Feriados"
          descricao={`${escala.mensagem}. Serviço de 24h com rotação independente.`}
        />
      )}
    </div>
  );
}

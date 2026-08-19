/**
 * Página da escala preta.
 */

import { useEffect, useState } from 'react';
import { consultarEscalaPreta } from '../services/escalaService';
import type { EscalaResponse } from '../types';
import { StubPage } from '../components/StubPage';

export function EscalaPretaPage() {
  const [escala, setEscala] = useState<EscalaResponse | null>(null);

  useEffect(() => {
    consultarEscalaPreta().then(setEscala).catch(console.error);
  }, []);

  return (
    <div>
      <h2>Escala Preta</h2>
      {escala && (
        <StubPage
          titulo="Escala Preta — Dias Úteis"
          descricao={`${escala.mensagem}. Rotação 1-150, 11 pessoas por dia, intervalo de 48h.`}
        />
      )}
    </div>
  );
}

/**
 * Componente genérico para páginas em desenvolvimento (stub).
 */

interface StubPageProps {
  titulo: string;
  descricao: string;
}

export function StubPage({ titulo, descricao }: StubPageProps) {
  return (
    <div className="stub-page">
      <h2>{titulo}</h2>
      <p className="stub-descricao">{descricao}</p>
      <div className="stub-badge">Em desenvolvimento</div>
    </div>
  );
}

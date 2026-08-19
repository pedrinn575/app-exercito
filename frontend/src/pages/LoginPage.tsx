/**
 * Página de login.
 * Responsabilidade: autenticar usuário via authService (nunca HTTP direto).
 */

import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';
import { Input } from '../components/Input';
import { Button } from '../components/Button';

export function LoginPage() {
  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [erro, setErro] = useState('');
  const [carregando, setCarregando] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setErro('');
    setCarregando(true);

    try {
      await login({ email, senha });
      navigate('/dashboard');
    } catch (err) {
      setErro(err instanceof Error ? err.message : 'Erro ao fazer login');
    } finally {
      setCarregando(false);
    }
  };

  return (
    <div className="login-page">
      <div className="login-card">
        <div className="login-header">
          <span className="login-icon">⚔</span>
          <h1>Escalas Militares</h1>
          <p>Sistema de Gerenciamento de Escalas</p>
        </div>

        <form onSubmit={handleSubmit} className="login-form">
          <Input
            label="E-mail"
            type="email"
            value={email}
            onChange={setEmail}
            placeholder="admin@escala.local"
            required
          />
          <Input
            label="Senha"
            type="password"
            value={senha}
            onChange={setSenha}
            placeholder="••••••••"
            required
          />

          {erro && <div className="login-erro">{erro}</div>}

          <Button type="submit" fullWidth disabled={carregando}>
            {carregando ? 'Entrando...' : 'Entrar'}
          </Button>
        </form>

        <div className="login-hint">
          <small>Dev: admin@escala.local / admin123</small>
        </div>
      </div>
    </div>
  );
}

# Setup — Sistema de Escalas Militares

Guia passo a passo para configurar o ambiente de desenvolvimento com Supabase.

## 1. Criar projeto no Supabase

1. Acesse [https://supabase.com](https://supabase.com) e crie uma conta.
2. Crie um novo projeto (região próxima ao Brasil recomendada).
3. Anote a senha do banco definida na criação.
4. Instale **Java 21** e configure `JAVA_HOME` no sistema.

## 2. Obter credenciais de conexão

No Supabase Dashboard:

1. Vá em **Project Settings** → **Database**.
2. Em **Connection string**, selecione **URI** ou **JDBC**.
3. Use o host, porta, database (`postgres`) e usuário (`postgres`).

Formato JDBC para o `.env`:

```text
SUPABASE_DB_URL=jdbc:postgresql://db.SEU_PROJETO.supabase.co:5432/postgres?sslmode=require
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=sua_senha
```

## 3. Executar migrations

No **SQL Editor** do Supabase, execute **em ordem**:

### V001 — Schema inicial

Arquivo: [`database/migrations/V001__schema_inicial.sql`](../database/migrations/V001__schema_inicial.sql)

Cria todas as tabelas, índices, constraints e enums.

### V002 — Dados de desenvolvimento

Arquivo: [`database/migrations/V002__dados_seed_dev.sql`](../database/migrations/V002__dados_seed_dev.sql)

Insere feriados de exemplo. **Usuários e militares** são criados automaticamente pelo `DevDataSeeder` do backend na primeira execução (com senhas BCrypt corretas).

## 4. Configurar variáveis de ambiente

Na raiz do projeto:

```bash
cp .env.example .env
```

Preencha todas as variáveis. O backend lê `SUPABASE_DB_*` e `JWT_*`.

Para o frontend, `VITE_API_URL` deve apontar para o backend:

```text
VITE_API_URL=http://localhost:8080/api
```

## 5. Rodar o backend

```bash
cd backend
./mvnw spring-boot:run
# Windows: mvnw.cmd spring-boot:run
```

Verifique: `GET http://localhost:8080/api/auth/me` (sem token retorna 401).

## 6. Rodar o frontend

```bash
cd frontend
npm install
npm run dev
```

Acesse `http://localhost:5173` e faça login.

## Credenciais seed (desenvolvimento)

| Email                  | Senha      | Papel         | Número militar |
|------------------------|------------|---------------|----------------|
| admin@escala.local     | admin123   | ADMINISTRADOR | —              |
| militar1@escala.local  | militar123 | MILITAR       | 1              |
| militar2@escala.local  | militar123 | MILITAR       | 2              |
| militar3@escala.local  | militar123 | MILITAR       | 3              |

> **Atenção:** Não use essas credenciais em produção.

## Testar login via curl

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@escala.local","senha":"admin123"}'
```

Resposta esperada: JSON com `token`, `tipo` e `usuario`.

## Troubleshooting

| Problema | Solução |
|----------|---------|
| Erro SSL na conexão | Confirme `?sslmode=require` na URL JDBC |
| 401 no login | Verifique se V002 foi executado e senhas estão corretas |
| CORS no frontend | Backend já permite `localhost:5173`; confira `VITE_API_URL` |
| Porta 8080 em uso | Altere `SERVER_PORT` no `.env` |

## Nota sobre autenticação

Este projeto usa **JWT próprio** (não Supabase Auth). O Supabase é usado apenas como hospedeiro PostgreSQL. A autenticação é gerenciada pelo Spring Security com BCrypt.

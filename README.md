# Sistema de Escalas Militares

Sistema completo de gerenciamento de escalas militares (escala preta, escala vermelha, trocas, faltas e relatórios).

## Stack

| Camada    | Tecnologia                          |
|-----------|-------------------------------------|
| Frontend  | React 18, TypeScript, Vite          |
| Backend   | Java 21, Spring Boot 3              |
| Banco     | PostgreSQL (Supabase)               |
| Auth      | JWT + BCrypt                        |

## Estrutura do repositório

```text
app exercito/
├── backend/          # API REST Spring Boot
├── frontend/         # Interface React
├── database/         # Migrations SQL
├── docs/             # Documentação técnica e de negócio
├── .env.example      # Template de variáveis de ambiente
└── README.md
```

## Pré-requisitos

- **Java 21+** (com `JAVA_HOME` configurado)
- **Maven** (incluso via `mvnw` no backend)
- **Node.js 18+**
- Conta no [Supabase](https://supabase.com)

## Início rápido

### 1. Configurar ambiente

```bash
cp .env.example .env
# Edite .env com suas credenciais Supabase
```

### 2. Banco de dados

Execute as migrations em ordem no SQL Editor do Supabase:

1. `database/migrations/V001__schema_inicial.sql`
2. `database/migrations/V002__dados_seed_dev.sql`

Detalhes em [docs/SETUP.md](docs/SETUP.md).

### 3. Backend

```bash
cd backend
./mvnw spring-boot:run
# Windows: mvnw.cmd spring-boot:run
```

API disponível em `http://localhost:8080/api`.

### 4. Frontend

```bash
cd frontend
npm install
npm run dev
```

Interface em `http://localhost:5173`.

## Credenciais de desenvolvimento (seed)

| Usuário              | Senha      | Papel          |
|----------------------|------------|----------------|
| admin@escala.local   | admin123   | ADMINISTRADOR  |
| militar1@escala.local| militar123 | MILITAR        |

## Documentação

- [Setup e Supabase](docs/SETUP.md)
- [Arquitetura](docs/ARQUITETURA.md)
- [Domínio e regras de negócio](docs/DOMINIO_ESCALAS.md)
- [API REST](docs/API.md)
- [Prompt master do projeto](docs/PROMPT_MASTER_CURSOR.md)

## Funcionalidades

| Módulo              | Status scaffold |
|---------------------|-----------------|
| Login / Auth JWT    | Implementado    |
| Dashboard           | Stub (UI)       |
| Cadastro militares  | Stub (API + UI) |
| Cadastro admins     | Stub (API + UI) |
| Escala preta        | Stub            |
| Escala vermelha     | Stub            |
| Troca de serviço    | Stub            |
| Aprovação de troca  | Stub            |
| Controle de faltas  | Stub            |
| Relatórios          | Stub            |
| Pedido de marmitas  | Futuro          |

## Convenções

- Commits: Conventional Commits (`feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`)
- Regra de negócio: sempre na camada `service` do backend
- HTTP no frontend: sempre via `services/`, nunca direto nas páginas

## Próximos passos sugeridos

1. Implementar algoritmo de rotação da escala preta (1–150, 11 pessoas/dia, 48h)
2. Implementar escala vermelha (fins de semana/feriados, 24h)
3. Fluxo completo de troca com aceites em cascata
4. UI de faltas com destaque vermelho e substituição
5. Testes automatizados (JUnit + Vitest)

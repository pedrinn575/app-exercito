# API REST — Sistema de Escalas Militares

Base URL: `http://localhost:8080/api`

Autenticação: header `Authorization: Bearer <token>` (exceto login).

## Códigos de erro padrão

| HTTP | Significado |
|------|-------------|
| 400 | Validação falhou (campos inválidos) |
| 401 | Não autenticado ou token inválido |
| 403 | Sem permissão para o recurso |
| 404 | Recurso não encontrado |
| 409 | Conflito de regra de negócio |
| 501 | Funcionalidade ainda não implementada |
| 500 | Erro interno |

Formato de erro:

```json
{
  "timestamp": "2026-07-08T14:00:00",
  "status": 400,
  "erro": "Erro de validação",
  "mensagem": "email: deve ser um e-mail válido",
  "caminho": "/api/auth/login"
}
```

---

## Autenticação

### POST /auth/login

Autentica usuário e retorna JWT.

**Request:**

```json
{
  "email": "admin@escala.local",
  "senha": "admin123"
}
```

**Response 200:**

```json
{
  "token": "eyJhbGciOiJIUzI1NiJ9...",
  "tipo": "Bearer",
  "usuario": {
    "id": "uuid",
    "email": "admin@escala.local",
    "papel": "ADMINISTRADOR",
    "nome": "Subtenente Admin"
  }
}
```

**Exemplo curl:**

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@escala.local","senha":"admin123"}'
```

### GET /auth/me

Retorna dados do usuário autenticado.

**Response 200:**

```json
{
  "id": "uuid",
  "email": "admin@escala.local",
  "papel": "ADMINISTRADOR",
  "nome": "Subtenente Admin"
}
```

---

## Militares

### GET /militares

Lista militares cadastrados. **Stub** — retorna lista vazia.

**Response 200:**

```json
[]
```

### POST /militares

Cadastra militar. **Stub** — retorna 501.

### GET /militares/{id}

Busca militar por ID. **Stub** — retorna 501.

---

## Administradores

### GET /administradores

Lista administradores. **Stub** — retorna lista vazia.

### POST /administradores

Cadastra administrador. **Stub** — retorna 501.

---

## Escalas

### GET /escalas/preta

Retorna escala preta ativa. **Stub** — retorna estrutura vazia.

**Response 200:**

```json
{
  "tipo": "PRETA",
  "dias": [],
  "mensagem": "Funcionalidade em desenvolvimento"
}
```

### GET /escalas/vermelha

Retorna escala vermelha ativa. **Stub** — estrutura análoga.

### POST /escalas/preta/gerar

Gera nova escala preta. **Stub** — retorna 501.

### POST /escalas/vermelha/gerar

Gera nova escala vermelha. **Stub** — retorna 501.

---

## Trocas de serviço

### GET /trocas

Lista solicitações de troca. **Stub** — retorna lista vazia.

### POST /trocas

Cria solicitação de troca.

**Request (futuro):**

```json
{
  "numeroSolicitante": 1,
  "numeroAlvo": 5,
  "escalaDiaId": "uuid"
}
```

**Status atual:** Stub — retorna 501.

### PATCH /trocas/{id}/aceitar

Militar alvo aceita troca. **Stub** — 501.

### PATCH /trocas/{id}/aprovar

Admin aprova troca. **Stub** — 501.

### PATCH /trocas/{id}/rejeitar

Rejeita troca. **Stub** — 501.

---

## Faltas

### GET /faltas

Lista faltas registradas. **Stub** — lista vazia.

### POST /faltas

Registra falta.

**Request (futuro):**

```json
{
  "escalaDiaId": "uuid",
  "militarId": "uuid",
  "tipo": "JUSTIFICADA",
  "substitutoId": null
}
```

**Status atual:** Stub — 501.

---

## Relatórios

### GET /relatorios/faltas

Relatório de faltas por período. **Stub** — 501.

### GET /relatorios/servicos

Relatório de serviços por militar. **Stub** — 501.

---

## DTOs principais

| DTO | Campos | Uso |
|-----|--------|-----|
| `LoginRequestDTO` | email, senha | Login |
| `TokenResponseDTO` | token, tipo, usuario | Resposta login |
| `UsuarioResponseDTO` | id, email, papel, nome | Perfil |
| `MilitarRequestDTO` | numero, nome, tipo, posto, reserva | Cadastro |
| `MilitarResponseDTO` | id, numero, nome, tipo, posto, reserva | Listagem |
| `EscalaResponseDTO` | tipo, dias, mensagem | Consulta escala |
| `TrocaRequestDTO` | numeroSolicitante, numeroAlvo, escalaDiaId | Troca |
| `FaltaRequestDTO` | escalaDiaId, militarId, tipo, substitutoId | Falta |

## Papéis (roles)

| Papel | Permissões |
|-------|------------|
| `ADMINISTRADOR` | Todas as operações, aprovar trocas, gerenciar faltas |
| `MILITAR` | Visualizar escalas, solicitar/aceitar trocas |

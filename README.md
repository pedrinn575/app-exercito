# Sistema de Escalas Militares

Aplicação PHP (MVC) para gerenciamento de **escala preta**, **escala vermelha**, trocas de serviço e controle de faltas.

> Stack do documento original (Java / Spring Boot / React / TypeScript) foi substituída por **PHP + Tailwind CSS**, mantendo a mesma arquitetura em camadas e as regras de negócio.

## Stack

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.2+ (MVC) |
| Frontend | PHP Views + Tailwind CSS (CDN) |
| Banco | SQLite (PDO) — scripts em `database/` |
| Auth | Sessão + BCrypt |

## Estrutura

```
app/
  Controllers/   # Entrada HTTP (sem regra de negócio)
  Services/      # Regras de negócio
  Repositories/  # Acesso a dados
  Entities/      # Modelos de domínio
  DTOs/          # Validação de entrada
  Config/        # App, DB, Router, rotas
  Exceptions/    # Handler global
  Utils/         # Auth, View, Calendar
  Views/         # Interface Tailwind
database/        # Migrations SQL + seed
docs/
public/          # Front controller
storage/         # SQLite local
```

**Fluxo:** `Controller → Service → Repository → Database`

## Regras de negócio

- **Escala preta:** dias úteis · padrão de 11 pessoas/dia (2 monitores e 9 atiradores), ajustável
- **Escala vermelha:** fins de semana e feriados · serviço 24h · rotação própria
- **Intervalo:** mínimo de **48h** entre serviços
- **Troca:** solicitante abre pedido → destino aceita → **Subtenente (admin)** aprova → troca só no dia informado
- **Falta injustificada:** nome em vermelho + opção de puxar reserva/substituto
- **Falta justificada:** sem substituto; atirador não é prejudicado
- **Marmitas:** o militar pede; o Subtenente aprova, nega ou pede alteração
- **Calendário:** gera o mês (preta nos úteis, vermelha no fim de semana e feriado)
- **Escalas editáveis:** efetivo por dia configurável, com inclusão, troca de função e remoção

## Como rodar

Requisitos: PHP 8.2+ com extensões `pdo_sqlite` e `openssl`.

```bash
# 1) Criar banco e usuários demo
php database/migrate.php

# 2) Subir servidor
php -S localhost:8080 -t public public/router.php
```

Abra: http://localhost:8080

**Login demo:** `ADM001` / `123456`

## Funcionalidades

- [x] Login
- [x] Dashboard
- [x] Cadastro de militares / administradores
- [x] Escala preta
- [x] Escala vermelha
- [x] Troca de serviço + aprovação
- [x] Controle de faltas
- [x] Relatórios
- [x] Calendário mensal e feriados da unidade
- [x] Meus serviços
- [x] Pedido de marmitas

## Commits sugeridos (Git Flow)

`feat` · `fix` · `docs` · `style` · `refactor` · `test` · `chore`

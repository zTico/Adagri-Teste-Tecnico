# Sistema de Gestao Agropecuaria

Teste tecnico full stack para um sistema de gestao agropecuaria construido com Laravel 12, Vue 3, TypeScript, Vite, PostgreSQL, Sanctum e Docker.

## Destaques

- CRUD completo de produtores rurais, fazendas e rebanhos
- Autenticacao por token com Laravel Sanctum
- Autorizacao por perfis com `admin` e `viewer`
- API RESTful com filtros e paginacao
- Exportacao de fazendas para `.xlsx`
- Exportacao de rebanhos por produtor para `.pdf`
- Painel de relatorios com:
  - total de fazendas por cidade
  - total de animais por especie
- Consulta opcional de CEP via ViaCEP
- Dados iniciais com seed
- Testes de feature para autenticacao, autorizacao, relatorios, exportacoes e consulta de CEP

## Stack

- Backend: Laravel 12
- Frontend: Vue 3 + TypeScript + Vite
- Banco de dados: PostgreSQL
- Autenticacao: Laravel Sanctum
- Ambiente local: Docker Compose

## Decisoes de Arquitetura

- O backend foi organizado em camadas mais pragmáticas:
  - `domain` para regras e contratos simples do fluxo
  - `infra/db` para queries e persistencia com Eloquent
- Os controllers foram mantidos enxutos e fazem apenas orquestracao de request, autorizacao e resposta.
- As validacoes ficam em form requests dedicados.
- A aplicacao opera com fila em modo `sync` enquanto nao existir caso de uso assincrono real, evitando worker e tabelas de jobs sem necessidade.
- A autorizacao usa policies do Laravel com uma regra direta:
  - `admin`: acesso total
  - `viewer`: somente leitura
- A exportacao em Excel usa um gerador Open XML nativo para evitar atrito de dependencia e ainda produzir um `.xlsx` valido.
- A exportacao em PDF usa Dompdf a partir de uma view Blade dedicada.

## Modelo de Dominio

### Produtor Rural

- `name`
- `cpf_cnpj`
- `phone`
- `email`
- `postal_code`
- `street`
- `number`
- `complement`
- `district`
- `city`
- `state`

### Fazenda

- `name`
- `city`
- `state`
- `state_registration`
- `total_area`
- `rural_producer_id`

### Rebanho

- `species`
- `quantity`
- `purpose`
- `farm_id`

### Especies obrigatorias

- `swine`
- `goats`
- `cattle`

## Como rodar com Docker

1. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

2. Suba o ambiente:

```bash
docker compose up --build
```

3. Acesse a aplicacao principal em:

- `http://localhost:8081`

Importante:

- A tela principal abre em `http://localhost:8081`
- A porta `5173` pertence ao Vite e nao deve ser usada como URL principal da aplicacao
- Na primeira subida, o build do container PHP pode demorar alguns minutos

O container `app` executa automaticamente:

- instalacao das dependencias Composer quando necessario
- espera pelo PostgreSQL
- migrations
- seed quando o banco estiver vazio

## Acesso ao PostgreSQL no host

O PostgreSQL do projeto fica publicado localmente em:

- `localhost:5432`

Credenciais para ferramentas desktop como `pgAdmin4`:

- host: `localhost`
- port: `5432`
- maintenance database: `postgres`
- username: `postgres`
- password: `postgres`

Importante:

- `DB_HOST=db` funciona apenas entre os containers do Docker
- em ferramentas desktop no host, use `localhost`
- a porta `8081` pertence a aplicacao web e nao deve ser usada para acessar o banco

### Configuracao sugerida no pgAdmin Desktop

Ao criar um novo servidor no `pgAdmin`:

**Aba `General`**

- `Name`: `Agro Local`

**Aba `Connection`**

- `Host name/address`: `localhost`
- `Port`: `5432`
- `Maintenance database`: `postgres`
- `Username`: `postgres`
- `Password`: `postgres`
- `Save password`: marcado

Depois de conectar:

- abrir `Databases`
- acessar o banco `agro_management`

Se o `pgAdmin` falhar:

- manter `Host name/address` como `localhost`
- tentar `SSL mode` em `Prefer` ou `Disable`
- confirmar que o Docker ainda esta com a porta publicada usando `docker compose ps`

Se a tela nao abrir localmente:

```bash
docker compose ps
```

Verifique se os servicos `app`, `web`, `db` e `node` estao em execucao e tente novamente em `http://localhost:8081`.

## Usuarios de Demonstracao

- Administrador
  - email: `admin@agro.test`
  - senha: `password`
- Visualizador
  - email: `viewer@agro.test`
  - senha: `password`

## Comandos uteis

Rodar os testes do Laravel:

```bash
docker compose exec app php artisan test
```

Gerar build do frontend:

```bash
docker compose exec node npm run build
```

Recriar banco e dados iniciais:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Principais rotas da API

### Autenticacao

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

### Produtores Rurais

- `GET /api/rural-producers`
- `POST /api/rural-producers`
- `GET /api/rural-producers/{id}`
- `PUT /api/rural-producers/{id}`
- `DELETE /api/rural-producers/{id}`

### Fazendas

- `GET /api/farms`
- `POST /api/farms`
- `GET /api/farms/{id}`
- `PUT /api/farms/{id}`
- `DELETE /api/farms/{id}`

### Rebanhos

- `GET /api/herds`
- `POST /api/herds`
- `GET /api/herds/{id}`
- `PUT /api/herds/{id}`
- `DELETE /api/herds/{id}`

### Relatorios e Exportacoes

- `GET /api/reports`
- `GET /api/exports/farms`
- `GET /api/exports/rural-producers/{id}/herds-pdf`

### Consultas auxiliares

- `GET /api/lookups/options`
- `GET /api/lookups/postal-code/{postalCode}`

## Filtros

### Produtores rurais

- `search`
- `city`
- `state`
- `per_page`

### Fazendas

- `search`
- `city`
- `state`
- `rural_producer_id`
- `per_page`

### Rebanhos

- `search`
- `species`
- `purpose`
- `farm_id`
- `rural_producer_id`
- `per_page`

## Testes

A suite de testes cobre:

- fluxo de login
- autorizacao entre admin e viewer
- agregacao de relatorios
- exportacao de planilha de fazendas
- exportacao em PDF de rebanhos
- consulta de CEP com HTTP fake

## Observacoes

- O codigo da aplicacao continua em ingles, conforme o requisito original.
- Os textos visiveis de interface e documentacao foram adaptados para portugues.
- A consulta de CEP foi isolada entre um fluxo de dominio e um gateway de infraestrutura.
- O frontend consome a API REST diretamente com tokens do Sanctum.

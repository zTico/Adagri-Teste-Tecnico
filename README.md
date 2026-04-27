# Sistema de Gestao Agropecuaria

Teste tecnico full stack para um sistema de gestao agropecuaria construido com Laravel 12, Vue 3, TypeScript, Vite, PostgreSQL, Sanctum e Docker.

## Como instalar o sistema localmente:
- https://youtu.be/sg492n-UZUc

## Video Mostrando o Funcionamento do Sistema:
- https://youtu.be/5_NQyBkfkQI

## Video Mostrando o Funcionamento das rotas:
- https://youtu.be/jyQQkn1jtKo

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
- Consulta via ViaCEP no cadastro do 
- Consulta IBGE para estados e cidades
- Dados iniciais com seed
- Testes de feature para autenticacao, autorizacao, relatorios, exportacoes e consulta de CEP

## Stack

- Backend (`servidor/API`): Laravel 12
- Frontend (`interface web`): Vue 3 + TypeScript + Vite
- Banco de dados: PostgreSQL
- Autenticacao (`login por token`): Laravel Sanctum
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

- `name` (`nome`)
- `cpf_cnpj` (`CPF/CNPJ`)
- `phone` (`telefone`)
- `email` (`email`)
- `postal_code` (`CEP`)
- `street` (`logradouro`)
- `number` (`numero`)
- `complement` (`complemento`)
- `district` (`bairro`)
- `city` (`cidade`)
- `state` (`estado`)

### Fazenda

- `name` (`nome`)
- `city` (`cidade`)
- `state` (`estado`)
- `state_registration` (`inscricao estadual`)
- `total_area` (`area total`)
- `rural_producer_id` (`id do produtor rural`)

### Rebanho

- `species` (`especie`)
- `quantity` (`quantidade`)
- `purpose` (`finalidade`)
- `farm_id` (`id da fazenda`)

### Especies obrigatorias

- `swine` (`suinos`)
- `goats` (`caprinos`)
- `cattle` (`bovinos`)

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
- criacao do link publico do storage para arquivos enviados
- seed quando o banco estiver vazio

## Acesso ao PostgreSQL no host

O PostgreSQL do projeto fica publicado localmente em:

- `localhost:5432`

Credenciais para ferramentas desktop como `pgAdmin4`:

- host (`servidor`): `localhost`
- port (`porta`): `5432`
- maintenance database (`banco de manutencao`): `postgres`
- username (`usuario`): `postgres`
- password (`senha`): `postgres`

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

### Acessar o PostgreSQL pelo terminal

Para abrir o banco diretamente pelo terminal usando o container Docker:

```bash
docker compose exec db psql -U postgres -d agro_management
```

Comandos uteis dentro do `psql`:

```sql
\dt
```

Lista as tabelas do banco.

```sql
SELECT * FROM users;
```

Consulta os usuarios cadastrados.

```sql
SELECT * FROM rural_producers;
```

Consulta os produtores rurais cadastrados.

```sql
SELECT * FROM farms;
```

Consulta as fazendas cadastradas.

```sql
SELECT * FROM herds;
```

Consulta os rebanhos cadastrados.

```sql
\q
```

Sai do terminal do PostgreSQL.

Tambem e possivel executar uma consulta direta, sem entrar no modo interativo:

```bash
docker compose exec db psql -U postgres -d agro_management -c "SELECT * FROM users;"
```

Se o `psql` estiver instalado no seu computador, tambem pode conectar pela porta publicada no host:

```bash
PGPASSWORD=postgres psql -h 127.0.0.1 -p 5432 -U postgres -d agro_management
```

## Usuarios de Demonstracao

- Administrador
  - email: `admin@agro.test`
  - senha: `password`
- Usuário
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

## Colecoes para API

Foram adicionados arquivos prontos para testar as rotas da API em clientes HTTP:

- Postman collection: `docs/api-clients/agro-management.postman_collection.json`
- Postman environment: `docs/api-clients/agro-management.postman_environment.json`
- Insomnia export: `docs/api-clients/agro-management.insomnia.json`

No Postman, importe a collection e o environment. Depois selecione o environment `Agro Management Local`, execute `Login - Admin` e o token sera salvo automaticamente na variavel `token`.

No Insomnia, importe o arquivo `agro-management.insomnia.json`, execute `Login - Admin`, copie o valor de `token` retornado na resposta e cole na variavel de ambiente `token`.

## Principais rotas da API

### Autenticacao

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

### Perfil do Usuario

- `GET /api/profile`
- `PUT /api/profile`
- `PUT /api/profile/password`
- `POST /api/profile/photo`
- `DELETE /api/profile/photo`

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

- `search` (`busca`)
- `city` (`cidade`)
- `state` (`estado`)
- `per_page` (`itens por pagina`)

### Fazendas

- `search` (`busca`)
- `city` (`cidade`)
- `state` (`estado`)
- `rural_producer_id` (`id do produtor rural`)
- `per_page` (`itens por pagina`)

### Rebanhos

- `search` (`busca`)
- `species` (`especie`)
- `purpose` (`finalidade`)
- `farm_id` (`id da fazenda`)
- `rural_producer_id` (`id do produtor rural`)
- `per_page` (`itens por pagina`)

## Testes

A suite de testes cobre:

- fluxo de login
- autorizacao entre admin e viewer
- agregacao de relatorios
- exportacao de planilha de fazendas
- exportacao em PDF de rebanhos
- consulta de CEP com HTTP fake

## Observacoes
- A consulta de CEP foi isolada entre um fluxo de dominio e um gateway de infraestrutura.
- O frontend consome a API REST diretamente com tokens do Sanctum.
- Fotos de perfil sao salvas no disco publico em `storage/app/public/profile-photos` e expostas via `public/storage`.

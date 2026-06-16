# Sistema de Laboratório

Aplicação web para cadastro de pacientes, exames e atendimentos.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (com Docker Compose)
- [Composer](https://getcomposer.org/)

O arquivo `.env` já vem configurado no repositório. Não é necessário criá-lo ou editá-lo.

## Como rodar

Clone o repositório, entre na pasta do projeto e execute os comandos abaixo:

```bash
composer install
docker compose up -d
docker compose exec laravel.test php artisan migrate --seed
docker compose exec laravel.test npm install
docker compose exec laravel.test npm run build
```

Acesse a aplicação em:

```text
http://localhost:8006
```

A porta é definida pela variável `APP_PORT` no `.env`.

## Desenvolvimento (opcional)

Para hot-reload do frontend com Vite, rode em outro terminal:

```bash
docker compose exec laravel.test npm run dev
```

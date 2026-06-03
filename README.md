# Teste técnico - Integração com Laboratório de Apoio

Olá, candidato(a).

Esta API faz parte do teste técnico. O objetivo é avaliar sua capacidade de criar uma aplicação web, modelar um fluxo simples de atendimento e integrar com um serviço externo que possui regras próprias e instabilidade proposital.

## Sobre este repositório

Esta API simula um Laboratório de Apoio. Em sistemas de saúde, laboratórios de apoio recebem exames que serão processados fora da empresa principal e disponibilizam os resultados por meio de web services.

Neste teste, a API:

- recebe solicitações de exames externos;
- aceita somente alguns códigos de exames;
- retorna resultados fictícios;
- pode falhar aleatoriamente para simular instabilidade de integração.

## Requisitos do projeto do candidato

Crie um projeto utilizando:

- Vue.js 3 ou superior;
- Laravel 9 ou superior;
- MySQL 5.6 ou superior.

As bibliotecas de frontend ficam a seu critério.

No seu projeto, implemente as funcionalidades abaixo.

## Cadastro de pacientes

Crie um cadastro de pacientes com os seguintes campos obrigatórios:

- nome;
- sexo;
- data de nascimento.

## Cadastro de exames

Crie um cadastro de exames com os seguintes campos obrigatórios:

- nome;
- código usado para identificar o exame;
- flag para indicar se o exame será processado internamente ou externamente.

A API deste repositório aceita somente estes códigos de exames externos:

- `HEMO`;
- `TESTO`;
- `T4L`.

## Tela de atendimento

Crie uma tela de atendimento onde o usuário consiga:

- selecionar ou buscar o paciente pelo ID ou pelo nome;
- adicionar uma lista de exames a serem realizados;
- visualizar o status de cada exame.

Os status esperados são:

- `Pendente`;
- `Enviado ao Apoio`;
- `Exame Pronto`.

Para exames internos, você pode deixar o status como `Pendente` ou `Exame Pronto`.

Para exames externos:

- envie os exames para esta API;
- salve o protocolo retornado pela API;
- consulte o resultado usando o protocolo;
- salve o resultado retornado;
- altere o status do exame para `Exame Pronto`;
- exiba o resultado do exame de forma simples na tela.

É obrigatório, para avaliação, ter pelo menos um atendimento externo integrado com esta API.

O uso de agentes de IA para auxiliar na construção do projeto fica a critério do candidato.

## Como executar esta API

O arquivo `.env` já está incluído no repositório com as configurações padrão. Não é necessário criar ou editar esse arquivo para executar a API.

Instale as dependências, crie o banco SQLite, execute as migrations e suba o servidor local:

```bash
composer install
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Por padrão, a API ficará disponível em:

```text
http://127.0.0.1:8000
```

## Configuração da API

As principais configurações já estão definidas no `.env` versionado neste repositório:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
EXAM_API_PRIVATE_KEY=c19a0ae13f3de4a65ed2f0bdb840ed28
EXAM_API_HASH_HEADER=X-Exam-Hash
EXAM_API_RANDOM_FAILURE_PERCENT=80
```

`EXAM_API_RANDOM_FAILURE_PERCENT` controla a instabilidade proposital da API.

- `0`: desativa falhas aleatórias;
- `80`: aproximadamente 80% das requisições válidas falham;
- `100`: todas as requisições válidas falham.

Quando a API falhar propositalmente, ela retornará HTTP `503`. O projeto do candidato deve tratar esse cenário.

## Autenticação

Todas as rotas da API exigem autenticação pelo header `X-Exam-Hash`.

O valor do header deve ser o hash SHA-256 gerado a partir da chave privada configurada em `EXAM_API_PRIVATE_KEY`.

Exemplo em PHP:

```php
$privateKey = 'c19a0ae13f3de4a65ed2f0bdb840ed28';
$hash = hash('sha256', $privateKey);
```

Com a chave padrão deste repositório, o valor do header será:

```text
X-Exam-Hash: 14e22da9c6a9c6cca82e12052f5b7cc88e72148c5faeabde9856b09e27bf3efc
```

Exemplo usando Laravel HTTP Client no projeto do candidato:

```php
use Illuminate\Support\Facades\Http;

$privateKey = config('services.exam_api.private_key');
$hash = hash('sha256', $privateKey);

$response = Http::withHeaders([
    'X-Exam-Hash' => $hash,
])->post('http://127.0.0.1:8000/api/exams', $payload);
```

Uma sugestão de configuração no projeto do candidato:

```dotenv
EXAM_API_URL=http://127.0.0.1:8000
EXAM_API_PRIVATE_KEY=c19a0ae13f3de4a65ed2f0bdb840ed28
```

## Rotas da API

### Enviar exames externos

```http
POST /api/exams
```

Esta rota recebe um atendimento externo. O mesmo paciente pode enviar um ou mais exames na mesma requisição.

Exemplo de payload:

```json
{
  "external_service_id": 1001,
  "requested_at": "2026-06-03T10:30:00-03:00",
  "patient": {
    "name": "Jane Doe",
    "sex": "f",
    "birth_date": "1990-03-10"
  },
  "exams": [
    {
      "code": "TESTO"
    },
    {
      "code": "HEMO"
    }
  ],
  "requester": {
    "name": "Dr. House"
  }
}
```

Campos importantes:

- `external_service_id`: ID numérico do atendimento no sistema do candidato;
- `requested_at`: data e hora da solicitação;
- `patient.name`: nome do paciente;
- `patient.sex`: `m` ou `f`;
- `patient.birth_date`: data de nascimento do paciente;
- `exams`: lista de exames externos;
- `exams.*.code`: código do exame. Valores aceitos: `HEMO`, `TESTO`, `T4L`;
- `requester.name`: nome do solicitante.

Exemplo com `curl`:

```bash
hash=$(php -r 'echo hash("sha256", "c19a0ae13f3de4a65ed2f0bdb840ed28");')

curl -X POST http://127.0.0.1:8000/api/exams \
  -H "Content-Type: application/json" \
  -H "X-Exam-Hash: $hash" \
  -d '{
    "external_service_id": 1001,
    "requested_at": "2026-06-03T10:30:00-03:00",
    "patient": {
      "name": "Jane Doe",
      "sex": "f",
      "birth_date": "1990-03-10"
    },
    "exams": [
      { "code": "TESTO" },
      { "code": "HEMO" }
    ],
    "requester": {
      "name": "Dr. House"
    }
  }'
```

Exemplo de resposta:

```json
{
  "message": "Exam request received.",
  "data": {
    "external_service_id": 1001,
    "protocol": "PROTO-20260603-123456",
    "status": "completed",
    "exams": [
      {
        "id": 1,
        "exam_code": "TESTO",
        "status": "completed",
        "created_at": "2026-06-03T13:30:00.000000Z"
      },
      {
        "id": 2,
        "exam_code": "HEMO",
        "status": "completed",
        "created_at": "2026-06-03T13:30:00.000000Z"
      }
    ]
  }
}
```

O campo `protocol` identifica a solicitação enviada ao laboratório de apoio. Todos os exames enviados na mesma requisição compartilham o mesmo protocolo.

### Consultar todos os resultados por protocolo

```http
GET /api/exams/{protocol}
```

Use esta rota para consultar todos os resultados vinculados ao protocolo.

Exemplo:

```bash
curl http://127.0.0.1:8000/api/exams/PROTO-20260603-123456 \
  -H "X-Exam-Hash: $hash"
```

Exemplo de resposta:

```json
{
  "data": {
    "external_service_id": 1001,
    "protocol": "PROTO-20260603-123456",
    "patient_name": "Jane Doe",
    "exams": [
      {
        "exam_code": "TESTO",
        "result": "Testosterona total: 560 ng/dL"
      },
      {
        "exam_code": "HEMO",
        "result": "Hemograma completo: sem alteracoes relevantes"
      }
    ]
  }
}
```

### Consultar um resultado específico

```http
GET /api/exams/{protocol}/{examCode}
```

Use esta rota quando quiser consultar apenas um exame dentro do protocolo.

Exemplo:

```bash
curl http://127.0.0.1:8000/api/exams/PROTO-20260603-123456/HEMO \
  -H "X-Exam-Hash: $hash"
```

Exemplo de resposta:

```json
{
  "data": {
    "external_service_id": 1001,
    "protocol": "PROTO-20260603-123456",
    "patient_name": "Jane Doe",
    "exams": [
      {
        "exam_code": "HEMO",
        "result": "Hemograma completo: sem alteracoes relevantes"
      }
    ]
  }
}
```

## Resultados fictícios

Os resultados retornados pela API são fixos:

- `TESTO`: `Testosterona total: 560 ng/dL`;
- `HEMO`: `Hemograma completo: sem alteracoes relevantes`;
- `T4L`: `T4 livre: 1.20 ng/dL`.

## O que será observado na avaliação

Serão observados principalmente:

- organização do código;
- modelagem do fluxo de atendimento;
- integração com API externa;
- tratamento de falhas da API;
- persistência correta dos protocolos e resultados;
- clareza da interface para acompanhar o status dos exames.

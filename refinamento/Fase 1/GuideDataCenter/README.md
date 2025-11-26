# GuideDataCenter

Módulo completo para gerenciamento de guias automotivos com MongoDB.

## 📋 Índice

- [Características](#características)
- [Arquitetura](#arquitetura)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
- [Modelos](#modelos)
- [Repositórios](#repositórios)
- [Serviços](#serviços)
- [Migrations](#migrations)
- [Seeders](#seeders)

## ✨ Características

- **100% MongoDB** - Todas as collections e operações em MongoDB
- **Arquitetura Limpa** - Separação clara entre Domain, Infrastructure
- **Repositories Pattern** - Interfaces e implementações MongoDB
- **Services Layer** - Lógica de negócio encapsulada
- **SEO Automático** - Geração automática de meta tags, schema.org
- **Clusters Inteligentes** - Sistema de links internos automáticos
- **Validação Completa** - Validação de dados em múltiplas camadas
- **Import/Export** - Sistema de importação de guias

## 🏗️ Arquitetura

```
Src/GuideDataCenter/
├── Domain/
│   ├── Mongo/                    # Models MongoDB
│   ├── Repositories/
│   │   ├── Contracts/           # Interfaces
│   │   └── Mongo/               # Implementações
│   └── Services/                # Lógica de negócio
├── Infrastructure/
│   └── Providers/               # Service Providers
├── Migrations/mongo/            # Migrations MongoDB
├── Seeders/                     # Seeders
└── config/                      # Configurações
```

## 📦 Instalação

### 1. Copiar arquivos

Copie toda a pasta `GuideDataCenter` para `Src/`:

```bash
cp -r GuideDataCenter/ /caminho/do/projeto/Src/
```

### 2. Registrar Service Provider

Adicione ao `config/app.php`:

```php
'providers' => [
    // ...
    Src\GuideDataCenter\Infrastructure\Providers\GuideDataCenterServiceProvider::class,
],
```

### 3. Publicar configurações

```bash
php artisan vendor:publish --tag=guide-datacenter-config
```

### 4. Executar migrations

```bash
php artisan migrate --path=Src/GuideDataCenter/Migrations/mongo
```

### 5. Executar seeders

```bash
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideCategorySeeder
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideSampleSeeder
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideClusterSeeder
```

## ⚙️ Configuração

### Configurar MongoDB

No arquivo `.env`:

```env
MONGODB_HOST=localhost
MONGODB_PORT=27017
MONGODB_DATABASE=guidedatacenter
MONGODB_USERNAME=
MONGODB_PASSWORD=
```

### Configurações do Módulo

Edite `config/guide-datacenter.php`:

```php
return [
    'base_url' => env('APP_URL') . '/guias',
    'seo' => [
        'title_max_length' => 60,
        'meta_description_max_length' => 160,
    ],
    'clusters' => [
        'enable_auto_generation' => true,
    ],
];
```

## 🚀 Uso

### Criar um Guia

```php
use Src\GuideDataCenter\Domain\Services\GuideCreationService;

$guideService = app(GuideCreationService::class);

$guide = $guideService->createGuide([
    'make' => 'Fiat',
    'model' => 'Uno',
    'version' => '1.0 Fire',
    'motor' => '1.0',
    'fuel' => 'Gasolina',
    'year_start' => 2010,
    'year_end' => 2020,
    'guide_category_id' => $categoryId,
    'template' => 'oleo-motor',
    'payload' => [
        'title' => 'Óleo do Motor Fiat Uno',
        'tipo_oleo' => '10W-30',
        'capacidade' => '3.5 litros',
    ],
]);
```

### Buscar Guias

```php
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

$repository = app(GuideRepositoryInterface::class);

// Por slug
$guide = $repository->findBySlug('fiat-uno-oleo-2010-2020');

// Por veículo
$guide = $repository->findByVehicle('fiat', 'uno', 2015);

// Por categoria
$guides = $repository->findByCategory('oleo', 50);

// Por marca
$guides = $repository->listByMake('fiat');

// Busca
$guides = $repository->search('fiat uno');
```

### Gerar Clusters

```php
use Src\GuideDataCenter\Domain\Services\GuideClusterService;

$clusterService = app(GuideClusterService::class);

// Gerar super cluster
$cluster = $clusterService->generateSuperCluster('fiat', 'uno');

// Atualizar clusters de um guia
$clusterService->updateGuideClusters($guide);

// Sincronizar todos os clusters de um veículo
$count = $clusterService->syncVehicleClusters('fiat', 'uno');
```

### Gerenciar SEO

```php
use Src\GuideDataCenter\Domain\Services\GuideSeoService;

$seoService = app(GuideSeoService::class);

// Criar SEO inicial
$seo = $seoService->createInitialSeo($guide);

// Atualizar schema.org
$seoService->updateSchemaOrg($guide);

// Calcular score
$score = $seoService->calculateSeoScore($guide->_id);
```

### Importar Guias

```php
use Src\GuideDataCenter\Domain\Services\GuideImportService;

$importService = app(GuideImportService::class);

// De array
$results = $importService->importFromArray($guidesArray);

// De JSON
$results = $importService->importFromJson($jsonString);

// Corrigir guias existentes
$results = $importService->fixExistingGuides();
```

## 📊 Modelos

### Guide

Principal modelo que representa um guia completo.

**Campos:**
- `guide_category_id` - ID da categoria
- `make`, `make_slug` - Marca do veículo
- `model`, `model_slug` - Modelo do veículo
- `version` - Versão
- `motor` - Motorização
- `fuel` - Combustível
- `year_start`, `year_end` - Range de anos
- `template` - Template usado
- `slug` - Slug único
- `url` - URL completa
- `payload` - Conteúdo do guia (array)
- `seo` - Dados de SEO (array)
- `links_internal` - Links internos (array)
- `links_related` - Links relacionados (array)

### GuideCategory

Categorias dos guias (Óleo, Pneus, Calibragem, etc).

**Campos:**
- `name` - Nome da categoria
- `slug` - Slug único
- `description` - Descrição
- `icon` - Ícone
- `order` - Ordem de exibição
- `active` - Ativo/Inativo

### GuideCluster

Clusters de links internos entre guias.

**Campos:**
- `guide_id` - ID do guia
- `make_slug`, `model_slug` - Veículo
- `year_range` - Range de anos
- `cluster_type` - Tipo (super, category, related, etc)
- `links` - Array de links

### GuideSeo

Dados de SEO dos guias.

**Campos:**
- `guide_id` - ID do guia
- `slug` - Slug
- `title` - Title tag
- `h1` - Título H1
- `meta_description` - Meta description
- `primary_keyword` - Palavra-chave primária
- `secondary_keywords` - Palavras-chave secundárias
- `schema_org` - Schema.org JSON-LD
- `canonical_url` - URL canônica

## 🔄 Repositórios

Todos os repositórios seguem o padrão Interface → Implementação:

- `GuideRepositoryInterface` → `GuideRepository`
- `GuideCategoryRepositoryInterface` → `GuideCategoryRepository`
- `GuideClusterRepositoryInterface` → `GuideClusterRepository`
- `GuideSeoRepositoryInterface` → `GuideSeoRepository`

## 🛠️ Serviços

### GuideCreationService

Criação completa de guias com validação, normalização, SEO e clusters.

**Métodos:**
- `createGuide(array $data)` - Cria guia completo
- `createFromTemplate()` - Cria a partir de template
- `duplicateGuide()` - Duplica guia existente
- `batchCreate()` - Criação em lote

### GuideClusterService

Gerenciamento de clusters e links internos.

**Métodos:**
- `generateSuperCluster()` - Gera super cluster
- `createYearCluster()` - Cluster por ano
- `createGenerationCluster()` - Cluster por geração
- `updateGuideClusters()` - Atualiza clusters de um guia
- `syncVehicleClusters()` - Sincroniza todos os clusters

### GuideSeoService

Gerenciamento de SEO automático.

**Métodos:**
- `createInitialSeo()` - Cria SEO inicial
- `updateSchemaOrg()` - Atualiza schema.org
- `calculateSeoScore()` - Calcula pontuação de SEO

### GuideValidatorService

Validação de dados.

**Métodos:**
- `validateGuideData()` - Valida dados do guia
- `validateVehicleData()` - Valida dados do veículo
- `validateYearRange()` - Valida range de anos

### GuideImportService

Importação de guias.

**Métodos:**
- `importFromArray()` - Importa de array
- `importFromJson()` - Importa de JSON
- `fixExistingGuides()` - Corrige guias existentes

## 🗄️ Collections e Índices

### guides

**Índices:**
- `slug` (unique)
- `guide_category_id`
- `make_slug + model_slug`
- `year_start + year_end`
- `template`
- `seo.primary_keyword`
- Full-text search em make, model, version, title

### guide_categories

**Índices:**
- `slug` (unique)
- `name`
- `order`
- `active + order`

### guide_clusters

**Índices:**
- `guide_id`
- `make_slug + model_slug`
- `cluster_type`
- `make_slug + model_slug + cluster_type`

### guide_seo

**Índices:**
- `guide_id` (unique)
- `slug` (unique)
- `primary_keyword`
- `secondary_keywords`
- Full-text em title, h1, meta_description

## 📝 Licença

Módulo proprietário para uso interno.

## 👥 Suporte

Para dúvidas e suporte, entre em contato com a equipe de desenvolvimento.

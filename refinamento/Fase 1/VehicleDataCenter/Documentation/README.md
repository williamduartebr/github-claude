# VehicleDataCenter - Documentação Completa

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Instalação](#instalação)
3. [Configuração](#configuração)
4. [Uso Básico](#uso-básico)
5. [Services](#services)
6. [API Reference](#api-reference)
7. [Ingestão de Dados](#ingestão-de-dados)
8. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

O **VehicleDataCenter** é um módulo completo para gerenciamento de dados veiculares, oferecendo:

-   ✅ Armazenamento dual (MySQL + MongoDB)
-   ✅ Sistema de ingestão de dados avançado
-   ✅ Busca otimizada e categorização
-   ✅ Geração automática de SEO
-   ✅ API RESTful completa
-   ✅ Sincronização automática entre bancos

### Arquitetura

```
VehicleDataCenter/
├── Domain/              # Lógica de negócio
├── Infrastructure/      # Implementações concretas
├── Presentation/        # Controllers e Views
└── Providers/          # Service Provider Laravel
```

---

## 🚀 Instalação

### 1. Copiar arquivos

Copie todo o diretório `VehicleDataCenter` para `src/`:

```bash
cp -r VehicleDataCenter/ /path/to/project/src/
```

### 2. Registrar Service Provider

Em `config/app.php`, adicione:

```php
'providers' => [
    // ...
    VehicleDataCenter\Providers\VehicleDataCenterServiceProvider::class,
],
```

### 3. Publicar assets

```bash
php artisan vendor:publish --tag=vehicle-data-center-config
php artisan vendor:publish --tag=vehicle-data-center-migrations
```

### 4. Configurar MongoDB

Em `.env`:

```env
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=vehicle_data
MONGODB_USERNAME=
MONGODB_PASSWORD=
```

Em `config/database.php`:

```php
'connections' => [
    'mongodb' => [
        'driver' => 'mongodb',
        'host' => env('MONGODB_HOST', '127.0.0.1'),
        'port' => env('MONGODB_PORT', 27017),
        'database' => env('MONGODB_DATABASE', 'vehicle_data'),
        'username' => env('MONGODB_USERNAME', ''),
        'password' => env('MONGODB_PASSWORD', ''),
        'options' => [
            'database' => env('MONGODB_AUTHENTICATION_DATABASE', 'admin'),
        ],
    ],
],
```

### 5. Executar migrations

```bash
php artisan migrate
```

### 6. Popular dados iniciais

```bash
php artisan db:seed --class="VehicleDataCenter\Database\Seeders\VehicleMakesAndModelsSeeder"
php artisan db:seed --class="VehicleDataCenter\Database\Seeders\VehicleSpecsSeeder"
```

---

## ⚙️ Configuração

### Arquivo de Configuração

O arquivo `config/vehicle-data-center.php` contém todas as opções:

```php
return [
    'middleware' => ['web'],
    'mysql_connection' => 'mysql',
    'mongodb_connection' => 'mongodb',
    'pagination' => [
        'per_page' => 20,
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
```

---

## 💡 Uso Básico

### Acessar via Web

```
http://seusite.com/veiculos
http://seusite.com/veiculos/toyota
http://seusite.com/veiculos/toyota/corolla
http://seusite.com/veiculos/toyota/corolla/2024
http://seusite.com/veiculos/toyota/corolla/2024/xei-20-flex
```

### Usar nos Controllers

```php
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;

class YourController extends Controller
{
    public function __construct(
        private VehicleSearchService $searchService
    ) {}

    public function search(Request $request)
    {
        $results = $this->searchService->search([
            'make' => 'toyota',
            'model' => 'corolla',
            'year' => 2024
        ]);

        return view('results', ['vehicles' => $results]);
    }
}
```

---

## 🔧 Services

### VehicleIngestionService

Responsável por ingerir dados de veículos:

```php
use Src\VehicleDataCenter\Domain\Services\VehicleIngestionService;

$ingestionService = app(VehicleIngestionService::class);

$result = $ingestionService->ingestVehicleData([
    'make' => [
        'name' => 'Toyota',
        'country_origin' => 'JP',
        'type' => 'car'
    ],
    'model' => [
        'name' => 'Corolla',
        'category' => 'sedan'
    ],
    'version' => [
        'name' => 'XEi 2.0 Flex',
        'year' => 2024,
        'fuel_type' => 'flex',
        'transmission' => 'automatic'
    ],
    'specs' => [
        'general' => [
            'power_hp' => 177,
            'torque_nm' => 210,
            'fuel_consumption_city' => 9.8,
            'fuel_consumption_highway' => 13.2
        ],
        'engine' => [
            'displacement_cc' => 2000,
            'cylinders' => 4,
            'aspiration' => 'naturally_aspirated'
        ],
        'tires' => [
            'front_tire_size' => '205/55 R16',
            'rear_tire_size' => '205/55 R16'
        ],
        'fluids' => [
            'engine_oil_type' => '0W-20',
            'engine_oil_capacity' => 4.2
        ]
    ]
]);
```

### VehicleSearchService

Busca otimizada de veículos:

```php
use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;

$searchService = app(VehicleSearchService::class);

// Busca simples
$results = $searchService->search([
    'make' => 'toyota',
    'year' => 2024
]);

// Busca rápida
$results = $searchService->quickSearch('corolla xei');

// Busca por specs
$results = $searchService->searchBySpecs([
    'fuel_type' => 'flex',
    'min_power_hp' => 150,
    'category' => 'sedan'
]);

// Por categoria
$results = $searchService->getByCategory('suv');

// Populares
$results = $searchService->getPopular(10);
```

### VehicleSyncService

Sincroniza dados entre MySQL e MongoDB:

```php
use Src\VehicleDataCenter\Domain\Services\VehicleSyncService;

$syncService = app(VehicleSyncService::class);

// Sincronizar uma versão específica
$result = $syncService->syncVersionToMongo(123);

// Sincronizar todas as versões
$result = $syncService->syncAllVersions();

// Detectar inconsistências
$inconsistencies = $syncService->detectInconsistencies();
```

### VehicleSeoBuilderService

Gera dados de SEO completos:

```php
use Src\VehicleDataCenter\Domain\Services\VehicleSeoBuilderService;

$seoBuilder = app(VehicleSeoBuilderService::class);

// Gerar SEO para uma versão
$seoData = $seoBuilder->buildSeoForVersion(123);

// Gerar para todas as versões
$result = $seoBuilder->buildSeoForAllVersions();
```

---

## 🌐 API Reference

### Endpoints Disponíveis

#### GET `/api/vehicles/health`

Health check do serviço

**Response:**

```json
{
    "status": "ok",
    "service": "VehicleDataCenter",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

#### GET `/api/vehicles/{versionId}`

Buscar veículo por ID

**Response:**

```json
{
    "version": {
        "id": 123,
        "name": "XEi 2.0 Flex",
        "year": 2024,
        "fuel_type": "flex"
    },
    "specs": {
        "general": {...},
        "engine": {...},
        "tires": {...}
    }
}
```

#### GET `/api/vehicles/search?make=toyota&year=2024`

Buscar veículos com filtros

**Parameters:**

-   `make` - Slug da marca
-   `model` - Slug do modelo
-   `year` - Ano
-   `keyword` - Palavra-chave

#### GET `/api/vehicles/{versionId}/seo`

Obter dados de SEO

**Response:**

```json
{
    "title": "Toyota Corolla XEi 2.0 2024 - Ficha Técnica",
    "meta_description": "...",
    "canonical_url": "...",
    "og_data": {...},
    "json_ld": {...}
}
```

---

## 📥 Ingestão de Dados

### Estrutura do Payload

```php
$payload = [
    'source' => 'api', // api, manual, csv, json, ai

    'make' => [
        'name' => 'Toyota',
        'logo_url' => 'https://...',
        'country_origin' => 'JP',
        'type' => 'car', // car, motorcycle, truck, bus
    ],

    'model' => [
        'name' => 'Corolla',
        'category' => 'sedan', // sedan, hatch, suv, pickup, etc
        'year_start' => 1990,
        'year_end' => null
    ],

    'version' => [
        'name' => 'XEi 2.0 Flex',
        'year' => 2024,
        'engine_code' => 'M20A-FKS',
        'fuel_type' => 'flex', // gasoline, diesel, ethanol, flex, electric, hybrid
        'transmission' => 'automatic', // manual, automatic, cvt, dct, amt
        'price_msrp' => 145990.00
    ],

    'specs' => [
        'general' => [
            'power_hp' => 177,
            'power_kw' => 130,
            'torque_nm' => 210,
            'top_speed_kmh' => 200,
            'acceleration_0_100' => 9.1,
            'fuel_consumption_city' => 9.8,
            'fuel_consumption_highway' => 13.2,
            'fuel_consumption_mixed' => 11.2,
            'fuel_tank_capacity' => 50,
            'weight_kg' => 1340,
            'trunk_capacity_liters' => 470,
            'seating_capacity' => 5,
            'doors' => 4,
            'drive_type' => 'fwd' // fwd, rwd, awd, 4wd
        ],

        'engine' => [
            'engine_type' => 'Inline 4-cylinder',
            'engine_code' => 'M20A-FKS',
            'displacement_cc' => 2000,
            'cylinders' => 4,
            'cylinder_arrangement' => 'inline',
            'valves_per_cylinder' => 4,
            'aspiration' => 'naturally_aspirated',
            'compression_ratio' => 13.0,
            'max_rpm' => 6600
        ],

        'tires' => [
            'front_tire_size' => '205/55 R16',
            'rear_tire_size' => '205/55 R16',
            'front_rim_size' => '16',
            'rear_rim_size' => '16',
            'front_pressure_psi' => 32.0,
            'rear_pressure_psi' => 32.0,
            'spare_tire_type' => 'full_size'
        ],

        'fluids' => [
            'engine_oil_type' => '0W-20',
            'engine_oil_capacity' => 4.2,
            'engine_oil_standard' => 'API SN',
            'coolant_type' => 'Etileno Glicol',
            'coolant_capacity' => 6.0,
            'transmission_fluid_type' => 'ATF WS',
            'brake_fluid_type' => 'DOT 4'
        ],

        'battery' => [
            'battery_type' => 'Lead-acid',
            'voltage' => 12,
            'capacity_ah' => 60,
            'cca' => 550,
            'group_size' => '60'
        ],

        'dimensions' => [
            'length_mm' => 4630,
            'width_mm' => 1780,
            'height_mm' => 1435,
            'wheelbase_mm' => 2700,
            'ground_clearance_mm' => 135
        ]
    ]
];

$ingestionService->ingestVehicleData($payload);
```

---

## 📚 Exemplos Práticos

### Exemplo 1: Buscar Veículos por Marca

```php
$results = $searchService->search(['make' => 'toyota']);

foreach ($results['results'] as $vehicle) {
    echo $vehicle['full_name'] . "\n";
}
```

### Exemplo 2: Comparar Veículos

```php
// Via Controller
public function compare(Request $request)
{
    $versionIds = [123, 456, 789];

    $comparison = [];
    foreach ($versionIds as $id) {
        $version = $versionRepository->findById($id);
        $specs = $specsRepository->getCompleteSpecs($id);
        $comparison[] = compact('version', 'specs');
    }

    return view('compare', compact('comparison'));
}
```

### Exemplo 3: Gerar SEO Automático

```php
// Após criar/atualizar um veículo
$seoData = $seoBuilder->buildSeoForVersion($versionId);

// Use em sua view
<title>{{ $seoData['title'] }}</title>
<meta name="description" content="{{ $seoData['meta_description'] }}">
<link rel="canonical" href="{{ $seoData['canonical_url'] }}">
```

### Exemplo 4: Sincronização Programada

```php
// Em app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $syncService = app(VehicleSyncService::class);
        $syncService->syncAllVersions();
    })->daily();
}
```

---

## 🎨 Customização

### Criar Helpers Personalizados

```php
use Src\VehicleDataCenter\Helpers\VehicleHelpers;

$fullName = VehicleHelpers::buildFullName('Toyota', 'Corolla', 'XEi', 2024);
$power = VehicleHelpers::formatPower(177, 130);
$consumption = VehicleHelpers::formatConsumption(11.2);
```

### Estender Services

```php
namespace App\Services;

use Src\VehicleDataCenter\Domain\Services\VehicleSearchService;

class CustomVehicleSearchService extends VehicleSearchService
{
    public function searchWithCustomLogic(array $filters)
    {
        // Sua lógica customizada
        $results = parent::search($filters);

        // Adicionar processamento

        return $results;
    }
}
```

---

## 🔒 Segurança

-   Todas as queries usam Eloquent ORM (proteção contra SQL Injection)
-   Validação de dados na ingestão
-   Rate limiting na API
-   Sanitização de inputs

---

## 📊 Performance

-   Índices otimizados em MySQL e MongoDB
-   Cache configurável
-   Busca híbrida (MongoDB para velocidade, MySQL para consistência)
-   Lazy loading de relacionamentos

---

## 🐛 Troubleshooting

### Problema: MongoDB não conecta

**Solução:**

```bash
# Verificar se MongoDB está rodando
sudo systemctl status mongodb

# Verificar credenciais no .env
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
```

### Problema: Rotas não encontradas

**Solução:**

```bash
# Limpar cache de rotas
php artisan route:clear
php artisan route:cache
```

### Problema: Views não carregam

**Solução:**

```bash
# Publicar views
php artisan vendor:publish --tag=vehicle-data-center-views --force
```

---

## 📞 Suporte

Para dúvidas ou problemas:

1. Verifique os logs em `storage/logs/laravel.log`
2. Rode os comandos de diagnóstico
3. Consulte esta documentação

---

**Desenvolvido com ❤️ para gerenciamento profissional de dados veiculares**

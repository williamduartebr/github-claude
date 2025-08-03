# README_VEHICLE_DATA.md

## 📋 Módulo de Dados de Veículos

Sistema completo para extração, validação e busca de dados técnicos de veículos extraídos dos artigos de pressão de pneus.

---

## 🚀 Instalação e Configuração

### 1. Registrar o Provider

Adicione o provider no arquivo `bootstrap/providers.php`:

```php
return [
    // ... outros providers
    Src\VehicleData\Infrastructure\Providers\VehicleDataServiceProvider::class,
];
```

### 2. Executar Migration

```bash
# Criar e executar migration
php artisan migrate
```

### 3. Primeira Extração

```bash
# Extrair dados de todos os artigos existentes
php artisan vehicle-data:extract --validate

# Ver estatísticas
php artisan vehicle-data:stats
```

---

## 📊 Estrutura de Dados

### Campos Principais

```php
// Dados básicos do veículo
'make'          => 'Toyota',           // Marca
'model'         => 'Corolla',          // Modelo  
'year'          => 2024,               // Ano
'tire_size'     => '205/55 R16',       // Tamanho do pneu

// Categorização
'main_category'     => 'sedan',        // hatch, sedan, suv, pickup, motorcycle, car_electric
'vehicle_segment'   => 'C',            // A, B, C, D, E, F, MOTO
'vehicle_type'      => 'sedan',        // Tipo específico

// Especificações de pressão
'pressure_specifications' => [
    'pressure_empty_front'  => 32,     // PSI vazio dianteiro
    'pressure_empty_rear'   => 30,     // PSI vazio traseiro
    'pressure_light_front'  => 32,     // PSI carga leve dianteiro
    'pressure_light_rear'   => 30,     // PSI carga leve traseiro
    'pressure_max_front'    => 36,     // PSI carga máxima dianteiro
    'pressure_max_rear'     => 34,     // PSI carga máxima traseiro
    'pressure_spare'        => 60,     // PSI pneu estepe
],

// Características
'is_premium'    => true,               // Veículo premium
'has_tpms'      => true,               // Sensor de pressão
'is_motorcycle' => false,              // É motocicleta
'is_electric'   => false,              // É elétrico
'is_hybrid'     => false,              // É híbrido

// Controle de qualidade
'validation_status'   => 'validated',  // pending, validated, needs_review
'data_quality_score'  => 8.5,         // Score 0-10
'source_articles'     => [...],        // IDs dos artigos fonte
```

---

## 🔧 Commands Disponíveis

### Extração de Dados

```bash
# Extração básica
php artisan vehicle-data:extract

# Com validação automática
php artisan vehicle-data:extract --validate

# Por marca específica
php artisan vehicle-data:extract --make="Toyota"

# Por categoria
php artisan vehicle-data:extract --category="car_electric"

# Forçar atualização
php artisan vehicle-data:extract --force

# Dry-run (testar sem salvar)
php artisan vehicle-data:extract --dry-run
```

### Validação

```bash
# Validação básica
php artisan vehicle-data:validate

# Com correção automática
php artisan vehicle-data:validate --fix

# Relatório detalhado
php artisan vehicle-data:validate --detailed

# Por marca específica
php artisan vehicle-data:validate --make="Honda"
```

### Limpeza

```bash
# Limpeza completa
php artisan vehicle-data:clean --all

# Remover duplicatas
php artisan vehicle-data:clean --remove-duplicates

# Normalizar dados
php artisan vehicle-data:clean --normalize-data

# Dry-run
php artisan vehicle-data:clean --all --dry-run
```

### Estatísticas

```bash
# Estatísticas gerais
php artisan vehicle-data:stats

# Análise detalhada
php artisan vehicle-data:stats --detailed --trends

# Por marca
php artisan vehicle-data:stats --make="Toyota"

# Exportar para JSON
php artisan vehicle-data:stats --export=json --output=stats.json
```

### Busca

```bash
# Busca específica
php artisan vehicle-data:search --make="BMW" --model="R 1250 RT"
php artisan vehicle-data:search --make="BMW" --model="R 1250 RT" --year=2021

# Por marca
php artisan vehicle-data:search --make="Toyota"

# Busca livre
php artisan vehicle-data:search --term="BMW R1250"

# Sugestões
php artisan vehicle-data:search --suggest="BMW"
```

---

## 💻 Usando o Model em Outros Domínios

### Importar o Model

```php
use Src\VehicleData\Domain\Entities\VehicleData;
```

### Exemplos de Uso

#### 1. Busca Específica

```php
// Buscar veículo exato (make + model + year)
$vehicle = VehicleData::findVehicle('BMW', 'R 1250 RT', 2021);

if ($vehicle) {
    echo "Encontrado: {$vehicle->vehicle_full_name}";
    echo "Pressão dianteira: {$vehicle->pressure_specifications['pressure_light_front']} PSI";
}

// Buscar sem ano (pega o mais recente)
$vehicle = VehicleData::findVehicle('BMW', 'R 1250 RT');

// Buscar todos os anos disponíveis
$vehicles = VehicleData::findAllYears('BMW', 'R 1250 RT');
foreach ($vehicles as $vehicle) {
    echo "{$vehicle->make} {$vehicle->model} {$vehicle->year}\n";
}
```

#### 2. Busca com Critérios

```php
$vehicles = VehicleData::search([
    'make' => 'BMW',
    'model' => 'R 1250 RT',
    'year' => 2021,
    'category' => 'motorcycle'
]);

foreach ($vehicles as $vehicle) {
    echo "- {$vehicle->vehicle_full_name} ({$vehicle->main_category})\n";
}
```

#### 3. Busca por Scopes

```php
// Veículos elétricos premium
$eletricos = VehicleData::electric()->premium()->get();

// SUVs com TPMS
$suvs = VehicleData::byCategory('suv')->withTpms()->get();

// Por marca e categoria
$toyotas = VehicleData::byMake('Toyota')->byCategory('sedan')->get();

// Veículos validados com qualidade alta
$qualidade = VehicleData::validated()
    ->where('data_quality_score', '>=', 8.0)
    ->get();
```

#### 4. Busca Fuzzy

```php
// Busca inteligente por termo livre
$resultados = VehicleData::fuzzySearch('BMW R1250', 10);

foreach ($resultados as $vehicle) {
    echo "- {$vehicle->make} {$vehicle->model} {$vehicle->year}\n";
}
```

#### 5. Sugestões de Busca

```php
$sugestoes = VehicleData::suggest('BMW');

// Marcas sugeridas
foreach ($sugestoes['makes'] as $marca) {
    echo "Marca: {$marca}\n";
}

// Modelos sugeridos
foreach ($sugestoes['models'] as $modelo) {
    echo "Modelo: {$modelo}\n";
}

// Veículos completos
foreach ($sugestoes['vehicles'] as $veiculo) {
    echo "Veículo: {$veiculo}\n";
}
```

#### 6. Busca com Filtros Avançados

```php
$bmws = VehicleData::findByMakeWithFilters('BMW', [
    'category' => 'motorcycle',    // Apenas motos
    'year_min' => 2020,           // A partir de 2020
    'year_max' => 2024,           // Até 2024
    'is_premium' => true,         // Apenas premium
    'has_tpms' => true           // Com sensor TPMS
]);
```

#### 7. Busca por Pressão

```php
// Veículos com pressão entre 30-40 PSI
$vehicles = VehicleData::findByPressureRange(30, 40);

foreach ($vehicles as $vehicle) {
    $specs = $vehicle->pressure_specifications;
    echo "{$vehicle->vehicle_full_name}: {$specs['pressure_light_front']} PSI\n";
}
```

#### 8. Estatísticas Rápidas

```php
$stats = VehicleData::getStatistics();

echo "Total de veículos: {$stats['total_vehicles']}\n";
echo "Score médio: {$stats['quality_scores']['average']}/10\n";

// Por categoria
foreach ($stats['by_category'] as $categoria => $count) {
    echo "{$categoria}: {$count} veículos\n";
}

// Características especiais
echo "Premium: {$stats['features']['premium']}\n";
echo "Elétricos: {$stats['features']['electric']}\n";
echo "Com TPMS: {$stats['features']['with_tpms']}\n";
```

---

## 🎯 Casos de Uso Práticos

### 1. API de Consulta de Veículos

```php
Route::get('/api/vehicles/search', function (Request $request) {
    if ($request->has(['make', 'model'])) {
        return VehicleData::findVehicle(
            $request->make,
            $request->model,
            $request->year
        );
    }
    
    return VehicleData::fuzzySearch($request->term ?? '', 10);
});

Route::get('/api/vehicles/suggest/{term}', function ($term) {
    return VehicleData::suggest($term);
});
```

### 2. Dashboard de Veículos

```php
class VehicleDashboardController extends Controller
{
    public function index()
    {
        $stats = VehicleData::getStatistics();
        $recentVehicles = VehicleData::orderBy('created_at', 'desc')->limit(10)->get();
        $topMakes = VehicleData::select('make')
            ->groupBy('make')
            ->orderByRaw('count(*) desc')
            ->limit(5)
            ->get();
            
        return view('dashboard.vehicles', compact('stats', 'recentVehicles', 'topMakes'));
    }
}
```

### 3. Sistema de Recomendação

```php
class VehicleRecommendationService
{
    public function getSimilarVehicles($make, $model, $limit = 5)
    {
        return VehicleData::findSimilarModels($make, $model, $limit);
    }
    
    public function getAlternativesByCategory($category, $limit = 10)
    {
        return VehicleData::byCategory($category)
            ->orderByDesc('data_quality_score')
            ->limit($limit)
            ->get();
    }
}
```

### 4. Validação de Formulários

```php
class VehicleFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:1950|max:' . (date('Y') + 2),
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $vehicle = VehicleData::findVehicle(
                $this->make,
                $this->model,
                $this->year
            );
            
            if (!$vehicle) {
                $validator->errors()->add('vehicle', 'Veículo não encontrado na base de dados.');
            }
        });
    }
}
```

---

## 📈 Performance e Otimização

### Índices Criados

- **Busca básica**: make, model, year, main_category
- **Características**: is_premium, has_tpms, is_electric, is_hybrid
- **Qualidade**: validation_status, data_quality_score
- **Compostos**: [make, model, year], [main_category, vehicle_segment]

### Dicas de Performance

```php
// ✅ Eficiente - usa índices
$vehicles = VehicleData::byMake('Toyota')->byCategory('sedan')->get();

// ✅ Eficiente - busca exata
$vehicle = VehicleData::findVehicle('BMW', 'R 1250 RT', 2021);

// ⚠️ Cuidado - pode ser lento com muitos resultados
$all = VehicleData::fuzzySearch('BMW');

// ✅ Melhor - com limite
$limited = VehicleData::fuzzySearch('BMW', 10);
```

---

## 🔍 Troubleshooting

### Problemas Comuns

1. **Migration falha**: Verifique conexão MongoDB
2. **Extração com erro**: Execute com `--dry-run` primeiro
3. **Busca lenta**: Use filtros específicos e limite resultados
4. **Dados duplicados**: Execute `vehicle-data:clean --remove-duplicates`

### Logs

```bash
# Ver logs de extração
tail -f storage/logs/laravel.log | grep VehicleData

# Ver estatísticas em tempo real
php artisan vehicle-data:stats --detailed
```

### Manutenção

```bash
# Limpeza semanal recomendada
php artisan vehicle-data:validate --fix
php artisan vehicle-data:clean --normalize-data

# Relatório mensal
php artisan vehicle-data:stats --detailed --trends --export=json --output=monthly_report.json
```

---

## 📝 Resumo dos Comandos

| Command | Descrição | Exemplo |
|---------|-----------|---------|
| `extract` | Extrai dados dos artigos | `--make="Toyota" --validate` |
| `search` | Busca veículos | `--make="BMW" --model="X1"` |
| `validate` | Valida dados | `--fix --detailed` |
| `clean` | Limpa e normaliza | `--all --dry-run` |
| `stats` | Estatísticas | `--detailed --trends` |

---

**Versão**: 1.0  
**Última atualização**: Agosto 2025  
**Dados processados**: 1926 artigos → 963 veículos únicos
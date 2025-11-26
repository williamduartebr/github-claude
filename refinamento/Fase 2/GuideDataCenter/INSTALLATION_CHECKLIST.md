# Checklist de Instalação - GuideDataCenter

## ✅ Pré-requisitos

- [ ] Laravel 8+ instalado
- [ ] MongoDB 4.4+ instalado e rodando
- [ ] Pacote `mongodb/laravel-mongodb` instalado via Composer
- [ ] Extensão MongoDB PHP habilitada

## 📋 Passos de Instalação

### 1. Copiar Arquivos
- [ ] Copiar pasta `GuideDataCenter` para `Src/` do projeto
- [ ] Verificar se todas as pastas foram copiadas corretamente

### 2. Configurar MongoDB
- [ ] Adicionar configuração MongoDB no `config/database.php`:
```php
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('MONGODB_HOST', 'localhost'),
    'port' => env('MONGODB_PORT', 27017),
    'database' => env('MONGODB_DATABASE'),
    'username' => env('MONGODB_USERNAME'),
    'password' => env('MONGODB_PASSWORD'),
    'options' => [
        'database' => env('MONGODB_AUTH_DATABASE', 'admin'),
    ],
],
```

- [ ] Adicionar variáveis no `.env`:
```env
MONGODB_HOST=localhost
MONGODB_PORT=27017
MONGODB_DATABASE=guidedatacenter
MONGODB_USERNAME=
MONGODB_PASSWORD=
MONGODB_AUTH_DATABASE=admin
```

### 3. Registrar Service Provider
- [ ] Adicionar em `config/app.php` na seção `providers`:
```php
Src\GuideDataCenter\Infrastructure\Providers\GuideDataCenterServiceProvider::class,
```

### 4. Publicar Configurações
- [ ] Executar:
```bash
php artisan vendor:publish --tag=guide-datacenter-config
```

### 5. Executar Migrations
- [ ] Executar migrations do MongoDB:
```bash
php artisan migrate --path=Src/GuideDataCenter/Migrations/mongo
```

- [ ] Verificar se as 4 collections foram criadas:
  - [ ] `guides`
  - [ ] `guide_categories`
  - [ ] `guide_clusters`
  - [ ] `guide_seo`

### 6. Executar Seeders
- [ ] Seeder de Categorias:
```bash
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideCategorySeeder
```

- [ ] Seeder de Guias de Exemplo:
```bash
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideSampleSeeder
```

- [ ] Seeder de Clusters:
```bash
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideClusterSeeder
```

### 7. Verificar Instalação
- [ ] Testar no Tinker:
```bash
php artisan tinker
```

```php
// Verificar categorias
\Src\GuideDataCenter\Domain\Mongo\GuideCategory::count();

// Verificar guias
\Src\GuideDataCenter\Domain\Mongo\Guide::count();

// Buscar um guia
$guide = \Src\GuideDataCenter\Domain\Mongo\Guide::first();
echo $guide->full_title;

// Testar repository
$repo = app(\Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface::class);
$guide = $repo->findBySlug('fiat-uno-oleo-2010-2020');
dd($guide);
```

### 8. Configurações Adicionais (Opcional)
- [ ] Ajustar configurações em `config/guide-datacenter.php`
- [ ] Configurar cache se necessário
- [ ] Configurar queues para jobs de atualização de clusters
- [ ] Adicionar observers (se desejado)

### 9. Rotas (Opcional)
- [ ] Adicionar rotas em `routes/web.php` ou `routes/api.php`
- [ ] Ver exemplos em `USAGE_EXAMPLES.md`

### 10. Controllers (Opcional)
- [ ] Criar controllers conforme exemplos em `USAGE_EXAMPLES.md`
- [ ] Criar views Blade

## 🧪 Testes de Funcionalidade

### Teste 1: Criar um Guia
```php
$service = app(\Src\GuideDataCenter\Domain\Services\GuideCreationService::class);
$category = \Src\GuideDataCenter\Domain\Mongo\GuideCategory::where('slug', 'oleo')->first();

$guide = $service->createGuide([
    'make' => 'Toyota',
    'model' => 'Corolla',
    'version' => '2.0 XEI',
    'motor' => '2.0',
    'fuel' => 'Gasolina',
    'year_start' => 2020,
    'year_end' => 2023,
    'guide_category_id' => $category->_id,
    'template' => 'oleo-motor',
    'payload' => [
        'title' => 'Óleo do Motor Toyota Corolla',
        'tipo_oleo' => '5W-30',
        'capacidade' => '4.2 litros',
    ],
]);

echo "Guia criado: " . $guide->slug;
```

### Teste 2: Buscar Guias
```php
$repo = app(\Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface::class);

// Por slug
$guide = $repo->findBySlug('toyota-corolla-oleo-2020-2023');

// Por veículo
$guide = $repo->findByVehicle('toyota', 'corolla', 2021);

// Por categoria
$guides = $repo->findByCategory('oleo');
```

### Teste 3: Clusters
```php
$service = app(\Src\GuideDataCenter\Domain\Services\GuideClusterService::class);

// Gerar super cluster
$cluster = $service->generateSuperCluster('toyota', 'corolla');

// Verificar
dd($cluster->links);
```

### Teste 4: SEO
```php
$guide = \Src\GuideDataCenter\Domain\Mongo\Guide::first();
$seoService = app(\Src\GuideDataCenter\Domain\Services\GuideSeoService::class);

// Calcular score
$score = $seoService->calculateSeoScore($guide->_id);
echo "SEO Score: {$score}%";
```

## 🐛 Troubleshooting

### Erro: "Class not found"
- Verificar se o Service Provider foi registrado
- Executar `composer dump-autoload`

### Erro: "Connection refused" MongoDB
- Verificar se MongoDB está rodando: `sudo systemctl status mongod`
- Verificar credenciais no `.env`

### Erro nas Migrations
- Verificar conexão MongoDB
- Verificar permissões do usuário MongoDB

### Repositories retornam null
- Verificar se seeders foram executados
- Verificar conexão com MongoDB
- Verificar se collections existem

## 📊 Estrutura de Arquivos

```
Src/GuideDataCenter/
├── Domain/
│   ├── Mongo/                          # 4 Models
│   ├── Repositories/
│   │   ├── Contracts/                  # 4 Interfaces
│   │   └── Mongo/                      # 4 Repositories
│   └── Services/                       # 5 Services
├── Infrastructure/
│   └── Providers/                      # 1 Service Provider
├── Migrations/mongo/                   # 4 Migrations
├── Seeders/                            # 3 Seeders
├── config/                             # 1 Config
├── README.md
├── USAGE_EXAMPLES.md
└── INSTALLATION_CHECKLIST.md (este arquivo)
```

## ✨ Arquivos Criados

Total: **28 arquivos**

- Models: 4
- Interfaces: 4
- Repositories: 4
- Services: 5
- Migrations: 4
- Seeders: 3
- Provider: 1
- Config: 1
- Docs: 3

## 🎉 Conclusão

Após completar todos os itens deste checklist, o módulo GuideDataCenter estará 100% funcional e pronto para uso!

Para dúvidas, consulte:
- `README.md` - Documentação completa
- `USAGE_EXAMPLES.md` - Exemplos práticos

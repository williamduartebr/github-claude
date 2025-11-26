# Exemplos de Uso - GuideDataCenter

## Exemplos Práticos de Implementação

### 1. Criando um Controller para Guias

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\GuideDataCenter\Domain\Services\GuideCreationService;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

class GuideController extends Controller
{
    protected $guideRepository;
    protected $categoryRepository;
    protected $creationService;

    public function __construct(
        GuideRepositoryInterface $guideRepository,
        GuideCategoryRepositoryInterface $categoryRepository,
        GuideCreationService $creationService
    ) {
        $this->guideRepository = $guideRepository;
        $this->categoryRepository = $categoryRepository;
        $this->creationService = $creationService;
    }

    /**
     * Exibe lista de guias
     */
    public function index(Request $request)
    {
        $guides = $this->guideRepository->paginate(20);
        return view('guides.index', compact('guides'));
    }

    /**
     * Exibe um guia específico
     */
    public function show(string $slug)
    {
        $guide = $this->guideRepository->findBySlug($slug);
        
        if (!$guide) {
            abort(404);
        }

        // Busca guias relacionados
        $related = $this->guideRepository->findRelated($guide, 5);

        return view('guides.show', compact('guide', 'related'));
    }

    /**
     * Busca guias por veículo
     */
    public function byVehicle(string $make, string $model, ?int $year = null)
    {
        $guides = $this->guideRepository->findByFilters([
            'make_slug' => $make,
            'model_slug' => $model,
            'year' => $year,
            'limit' => 50,
        ]);

        return view('guides.vehicle', compact('guides', 'make', 'model', 'year'));
    }

    /**
     * Guias por categoria
     */
    public function byCategory(string $categorySlug)
    {
        $category = $this->categoryRepository->findBySlug($categorySlug);
        
        if (!$category) {
            abort(404);
        }

        $guides = $this->guideRepository->findByCategory($categorySlug, 50);

        return view('guides.category', compact('category', 'guides'));
    }

    /**
     * Criar novo guia (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'make' => 'required|string|min:2',
            'model' => 'required|string|min:2',
            'guide_category_id' => 'required|string',
            'version' => 'nullable|string',
            'motor' => 'nullable|string',
            'fuel' => 'nullable|string',
            'year_start' => 'nullable|integer|min:1900',
            'year_end' => 'nullable|integer|min:1900',
            'template' => 'required|string',
            'payload' => 'required|array',
        ]);

        $guide = $this->creationService->createGuide($validated);

        return redirect()
            ->route('guides.show', $guide->slug)
            ->with('success', 'Guia criado com sucesso!');
    }
}
```

### 2. API REST para Guias

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Services\GuideCreationService;

class GuideApiController extends Controller
{
    protected $repository;
    protected $creationService;

    public function __construct(
        GuideRepositoryInterface $repository,
        GuideCreationService $creationService
    ) {
        $this->repository = $repository;
        $this->creationService = $creationService;
    }

    /**
     * GET /api/guides
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'make_slug',
            'model_slug',
            'category_id',
            'year',
            'template',
            'search',
        ]);

        $guides = $this->repository->findByFilters($filters);

        return response()->json([
            'success' => true,
            'data' => $guides,
            'count' => $guides->count(),
        ]);
    }

    /**
     * GET /api/guides/{slug}
     */
    public function show(string $slug)
    {
        $guide = $this->repository->findBySlug($slug);

        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $guide->load(['category', 'guideSeo', 'clusters']),
        ]);
    }

    /**
     * POST /api/guides
     */
    public function store(Request $request)
    {
        try {
            $guide = $this->creationService->createGuide($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Guide created successfully',
                'data' => $guide,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/guides/vehicle/{make}/{model}
     */
    public function byVehicle(string $make, string $model, Request $request)
    {
        $year = $request->input('year');
        
        $guides = $this->repository->findByFilters([
            'make_slug' => $make,
            'model_slug' => $model,
            'year' => $year,
        ]);

        return response()->json([
            'success' => true,
            'vehicle' => compact('make', 'model', 'year'),
            'data' => $guides,
        ]);
    }
}
```

### 3. Command para Importação em Lote

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\GuideDataCenter\Domain\Services\GuideImportService;

class ImportGuidesCommand extends Command
{
    protected $signature = 'guides:import {file}';
    protected $description = 'Import guides from JSON file';

    protected $importService;

    public function __construct(GuideImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info('Starting import...');
        
        $json = file_get_contents($file);
        $results = $this->importService->importFromJson($json);

        $this->info("Imported: {$results['imported']}");
        $this->error("Failed: {$results['failed']}");

        if (!empty($results['errors'])) {
            $this->error('Errors:');
            foreach ($results['errors'] as $error) {
                $this->line("Index {$error['index']}: {$error['error']}");
            }
        }

        return 0;
    }
}
```

### 4. Job para Atualização de Clusters

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;

class UpdateVehicleClustersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $makeSlug;
    protected $modelSlug;

    public function __construct(string $makeSlug, string $modelSlug)
    {
        $this->makeSlug = $makeSlug;
        $this->modelSlug = $modelSlug;
    }

    public function handle(GuideClusterService $clusterService)
    {
        $count = $clusterService->syncVehicleClusters(
            $this->makeSlug,
            $this->modelSlug
        );

        \Log::info("Updated {$count} clusters for {$this->makeSlug}/{$this->modelSlug}");
    }
}
```

### 5. Observer para SEO Automático

```php
<?php

namespace App\Observers;

use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Services\GuideSeoService;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;

class GuideObserver
{
    protected $seoService;
    protected $clusterService;

    public function __construct(
        GuideSeoService $seoService,
        GuideClusterService $clusterService
    ) {
        $this->seoService = $seoService;
        $this->clusterService = $clusterService;
    }

    /**
     * Handle the Guide "created" event.
     */
    public function created(Guide $guide)
    {
        // Atualiza schema.org
        $this->seoService->updateSchemaOrg($guide);

        // Atualiza clusters
        $this->clusterService->updateGuideClusters($guide);
    }

    /**
     * Handle the Guide "updated" event.
     */
    public function updated(Guide $guide)
    {
        // Atualiza schema.org
        $this->seoService->updateSchemaOrg($guide);

        // Atualiza clusters se dados do veículo mudaram
        if ($guide->wasChanged(['make_slug', 'model_slug', 'year_start', 'year_end'])) {
            $this->clusterService->updateGuideClusters($guide);
        }
    }
}
```

### 6. Blade Template Example

```blade
{{-- resources/views/guides/show.blade.php --}}
@extends('layouts.app')

@section('title', $guide->guideSeo->title ?? $guide->full_title)
@section('meta_description', $guide->guideSeo->meta_description ?? '')

@push('head')
    {{-- Schema.org --}}
    @if($guide->guideSeo && !empty($guide->guideSeo->schema_org))
        <script type="application/ld+json">
            {!! json_encode($guide->guideSeo->schema_org) !!}
        </script>
    @endif

    {{-- Open Graph --}}
    @if($guide->guideSeo && !empty($guide->guideSeo->open_graph))
        @foreach($guide->guideSeo->open_graph as $property => $content)
            <meta property="{{ $property }}" content="{{ $content }}">
        @endforeach
    @endif
@endpush

@section('content')
<article class="guide">
    <h1>{{ $guide->guideSeo->h1 ?? $guide->full_title }}</h1>
    
    <div class="guide-meta">
        <span class="category">{{ $guide->category->name }}</span>
        <span class="vehicle">{{ $guide->make }} {{ $guide->model }}</span>
        @if($guide->year_range_text)
            <span class="year">{{ $guide->year_range_text }}</span>
        @endif
    </div>

    <div class="guide-content">
        @foreach($guide->payload as $key => $value)
            @if(is_array($value))
                <h3>{{ ucfirst(str_replace('_', ' ', $key)) }}</h3>
                <ul>
                    @foreach($value as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
            @endif
        @endforeach
    </div>

    {{-- Links Internos --}}
    @if(!empty($guide->links_internal))
        <div class="internal-links">
            <h3>Outros Guias do {{ $guide->make }} {{ $guide->model }}</h3>
            <ul>
                @foreach($guide->links_internal as $type => $link)
                    <li>
                        <a href="{{ $link['url'] }}">{{ $link['title'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Guias Relacionados --}}
    @if($related->count() > 0)
        <div class="related-guides">
            <h3>Guias Relacionados</h3>
            <ul>
                @foreach($related as $relatedGuide)
                    <li>
                        <a href="{{ route('guides.show', $relatedGuide->slug) }}">
                            {{ $relatedGuide->full_title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</article>
@endsection
```

### 7. Rotas

```php
// routes/web.php

use App\Http\Controllers\GuideController;

Route::prefix('guias')->name('guides.')->group(function () {
    Route::get('/', [GuideController::class, 'index'])->name('index');
    Route::get('/{slug}', [GuideController::class, 'show'])->name('show');
    Route::get('/categoria/{category}', [GuideController::class, 'byCategory'])->name('category');
    Route::get('/veiculo/{make}/{model}/{year?}', [GuideController::class, 'byVehicle'])->name('vehicle');
});

// routes/api.php

use App\Http\Controllers\Api\GuideApiController;

Route::prefix('api/guides')->group(function () {
    Route::get('/', [GuideApiController::class, 'index']);
    Route::get('/{slug}', [GuideApiController::class, 'show']);
    Route::post('/', [GuideApiController::class, 'store']);
    Route::get('/vehicle/{make}/{model}', [GuideApiController::class, 'byVehicle']);
});
```

## Executando Tarefas Comuns

### Criar Categorias

```bash
php artisan db:seed --class=Src\\GuideDataCenter\\Seeders\\GuideCategorySeeder
```

### Importar Guias

```bash
php artisan guides:import storage/guides.json
```

### Atualizar Clusters

```php
// No Tinker
$service = app(\Src\GuideDataCenter\Domain\Services\GuideClusterService::class);
$service->syncVehicleClusters('fiat', 'uno');
```

### Verificar SEO Score

```php
$seoService = app(\Src\GuideDataCenter\Domain\Services\GuideSeoService::class);
$score = $seoService->calculateSeoScore($guideId);
echo "SEO Score: {$score}%";
```

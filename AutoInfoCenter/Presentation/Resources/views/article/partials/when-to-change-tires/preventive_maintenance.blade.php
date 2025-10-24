     @if(!empty($article->preventive_maintenance) && is_array($article->preventive_maintenance))
     <section class="mb-12">
         <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
             🔧 Manutenção Preventiva dos Pneus
         </h2>

         <!-- Grid de Cards de Manutenção -->
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

             <!-- Verificação de Pressão -->
             @if(!empty($article->preventive_maintenance['verificacao_pressao']))
             @php $pressao = $article->preventive_maintenance['verificacao_pressao']; @endphp
             <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                 <div class="flex items-center mb-4">
                     <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                         </svg>
                     </div>
                     <h3 class="text-lg font-medium text-gray-900">Verificação de Pressão</h3>
                 </div>

                 @if(!empty($pressao['frequencia']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Frequência:</strong> {{ $pressao['frequencia'] }}</p>
                 @endif

                 @if(!empty($pressao['momento']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Quando:</strong> {{ $pressao['momento'] }}</p>
                 @endif

                 @if(!empty($pressao['tolerancia']))
                 <p class="text-sm text-gray-600 mb-3"><strong>Tolerância:</strong> {{ $pressao['tolerancia'] }}</p>
                 @endif

                 <div class="bg-blue-50 p-3 rounded-lg">
                     <p class="text-sm text-blue-800"><strong>Importância:</strong> A pressão correta garante segurança, economia e durabilidade dos pneus.</p>
                 </div>
             </div>
             @endif

             <!-- Rodízio -->
             @if(!empty($article->preventive_maintenance['rodizio']))
             @php $rodizio = $article->preventive_maintenance['rodizio']; @endphp
             <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                 <div class="flex items-center mb-4">
                     <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                         </svg>
                     </div>
                     <h3 class="text-lg font-medium text-gray-900">Rodízio de Pneus</h3>
                 </div>

                 @if(!empty($rodizio['frequencia']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Frequência:</strong> {{ $rodizio['frequencia'] }}</p>
                 @endif

                 @if(!empty($rodizio['padrao']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Padrão:</strong> {{ $rodizio['padrao'] }}</p>
                 @endif

                 @if(!empty($rodizio['beneficio']))
                 <div class="bg-green-50 p-3 rounded-lg">
                     <p class="text-sm text-green-800"><strong>Benefício:</strong> {{ $rodizio['beneficio'] }}</p>
                 </div>
                 @endif
             </div>
             @endif

             <!-- Alinhamento e Balanceamento -->
             @if(!empty($article->preventive_maintenance['alinhamento_balanceamento']))
             @php $alinhamento = $article->preventive_maintenance['alinhamento_balanceamento']; @endphp
             <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                 <div class="flex items-center mb-4">
                     <div class="flex-shrink-0 h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center mr-4">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                         </svg>
                     </div>
                     <h3 class="text-lg font-medium text-gray-900">Alinhamento e Balanceamento</h3>
                 </div>

                 @if(!empty($alinhamento['frequencia']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Frequência:</strong> {{ $alinhamento['frequencia'] }}</p>
                 @endif

                 @if(!empty($alinhamento['sinais']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Sinais:</strong> {{ $alinhamento['sinais'] }}</p>
                 @endif

                 @if(!empty($alinhamento['importancia']))
                 <div class="bg-yellow-50 p-3 rounded-lg">
                     <p class="text-sm text-yellow-800"><strong>Importância:</strong> {{ $alinhamento['importancia'] }}</p>
                 </div>
                 @endif
             </div>
             @endif

             <!-- Tasks antigas (compatibilidade) -->
             @if(!empty($article->preventive_maintenance['tasks']) && is_array($article->preventive_maintenance['tasks']))
             @foreach($article->preventive_maintenance['tasks'] as $task)
             <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                 <div class="flex items-center mb-4">
                     <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                         </svg>
                     </div>
                     <h3 class="text-lg font-medium text-gray-900">{{ $task['frequency'] }}</h3>
                 </div>

                 @if(!empty($task['moment']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Quando:</strong> {{ $task['moment'] }}</p>
                 @endif

                 @if(!empty($task['pattern']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Padrão:</strong> {{ $task['pattern'] }}</p>
                 @endif

                 @if(!empty($task['tolerance']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Tolerância:</strong> {{ $task['tolerance'] }}</p>
                 @endif

                 @if(!empty($task['signs']))
                 <p class="text-sm text-gray-600 mb-2"><strong>Sinais:</strong> {{ $task['signs'] }}</p>
                 @endif

                 @if(!empty($task['benefit']))
                 <div class="bg-green-50 p-3 rounded-lg mt-3">
                     <p class="text-sm text-green-800"><strong>Benefício:</strong> {{ $task['benefit'] }}</p>
                 </div>
                 @endif

                 @if(!empty($task['importance']))
                 <div class="bg-yellow-50 p-3 rounded-lg mt-3">
                     <p class="text-sm text-yellow-800"><strong>Importância:</strong> {{ $task['importance'] }}</p>
                 </div>
                 @endif
             </div>
             @endforeach
             @endif
         </div>

         <!-- Cuidados Gerais -->
         @if(!empty($article->preventive_maintenance['cuidados_gerais']) && is_array($article->preventive_maintenance['cuidados_gerais']))
         <div class="bg-blue-100/50 border border-blue-200 rounded-lg p-6">
             <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                 </svg>
                 Cuidados Gerais
             </h3>
             <ul class="space-y-2">
                 @foreach($article->preventive_maintenance['cuidados_gerais'] as $care)
                 @if(!empty($care))
                 <li class="flex items-start">
                     <span class="text-[#0E368A] mr-2 mt-1">•</span>
                     <span class="text-gray-700">{{ $care }}</span>
                 </li>
                 @endif
                 @endforeach
             </ul>
         </div>
         @endif

         <!-- Manter compatibilidade com general_care antiga -->
         @if(!empty($article->preventive_maintenance['general_care']) && is_array($article->preventive_maintenance['general_care']))
         <div class="bg-blue-100/50 border border-blue-200 rounded-lg p-6">
             <h3 class="text-lg font-semibold text-gray-900 mb-4">Cuidados Gerais</h3>
             <ul class="space-y-2">
                 @foreach($article->preventive_maintenance['general_care'] as $care)
                 @if(!empty($care))
                 <li class="flex items-start">
                     <span class="text-[#0E368A] mr-2 mt-1">•</span>
                     <span class="text-gray-700">{{ $care }}</span>
                 </li>
                 @endif
                 @endforeach
             </ul>
         </div>
         @endif
     </section>
     @endif
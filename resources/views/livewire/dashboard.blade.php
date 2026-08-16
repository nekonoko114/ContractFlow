<div class="space-y-8 w-full">
    <!-- Toast Message -->
    @if ($toastMessage)
        <div class="fixed top-5 right-5 z-50 flex items-center gap-3 bg-emerald-600 text-white px-6 py-3.5 rounded-2xl shadow-2xl transition-all duration-300 animate-in fade-in slide-in-from-top-4">
            <span class="material-symbols-outlined text-2xl">check_circle</span>
            <span class="text-sm font-semibold">{{ $toastMessage }}</span>
            <button wire:click="clearToast" class="hover:opacity-75 transition-opacity ml-3 cursor-pointer">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-2">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#3525cd] to-[#4f46e5] flex items-center justify-center text-white shadow-md">
                    <span class="material-symbols-outlined text-2xl">dashboard</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#131b2e] tracking-tight">ダッシュボード</h1>
            </div>
            <p class="text-sm text-[#505f76] mt-2 font-medium">ご契約中の携帯電話回線の状況・料金・安全維持期間・乗り換え実績の総合概要</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <!-- CSV Actions -->
            <div class="inline-flex items-center gap-2">
                <button wire:click="exportCsv" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-white border border-[#c7c4d8]/60 text-xs font-bold text-[#131b2e] hover:bg-[#f2f3ff] transition-all shadow-xs cursor-pointer"
                        title="回線一覧をCSVファイルとしてダウンロード">
                    <span class="material-symbols-outlined text-lg text-emerald-600">download</span>
                    <span>CSV出力</span>
                </button>
                <button wire:click="openImportModal" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-white border border-[#c7c4d8]/60 text-xs font-bold text-[#131b2e] hover:bg-[#f2f3ff] transition-all shadow-xs cursor-pointer"
                        title="CSVファイルから回線情報を一括登録">
                    <span class="material-symbols-outlined text-lg text-indigo-600">upload_file</span>
                    <span>一括インポート</span>
                </button>
            </div>

            <!-- View Mode Switcher -->
            <div class="inline-flex items-center bg-[#f2f3ff] p-1.5 rounded-2xl border border-[#dae2fd]">
                <button wire:click="$set('viewMode', 'table')" 
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $viewMode === 'table' ? 'bg-white text-[#3525cd] shadow-xs' : 'text-[#505f76] hover:text-[#131b2e]' }}">
                    <span class="material-symbols-outlined text-lg">table_rows</span>
                    <span>テーブル</span>
                </button>
                <button wire:click="$set('viewMode', 'grid')" 
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $viewMode === 'grid' ? 'bg-white text-[#3525cd] shadow-xs' : 'text-[#505f76] hover:text-[#131b2e]' }}">
                    <span class="material-symbols-outlined text-lg">grid_view</span>
                    <span>カード</span>
                </button>
            </div>

            <!-- New Contract Button -->
            <a href="{{ route('lines.create') }}" 
               class="inline-flex items-center justify-center gap-2 bg-[#3525cd] hover:bg-[#291cb0] text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-md shadow-[#3525cd]/20 active:scale-95 transition-all duration-150">
                <span class="material-symbols-outlined text-xl">add_circle</span>
                <span>回線追加・乗り換え比較</span>
            </a>
        </div>
    </div>

    <!-- Bento Grid Summary Cards (Spacious & Clean) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Monthly Total -->
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs flex flex-col justify-between relative overflow-hidden group hover:border-[#c3c0ff] transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-[#4f46e5]/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#505f76]">月額料金 合計</span>
                <span class="material-symbols-outlined text-2xl text-[#3525cd] bg-[#f2f3ff] p-2 rounded-xl">payments</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black text-[#131b2e] tracking-tight">¥{{ number_format($totalMonthlyFee) }}</span>
                    <span class="text-sm font-bold text-[#505f76]">/月</span>
                </div>
                <div class="flex items-center gap-2 mt-2 pt-2 border-t border-[#f2f3ff] text-xs text-[#505f76]">
                    <span>年間換算:</span>
                    <span class="font-bold text-[#131b2e]">¥{{ number_format($totalMonthlyFee * 12) }}</span>
                </div>
            </div>
        </div>

        <!-- Total Data Capacity -->
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs flex flex-col justify-between relative overflow-hidden group hover:border-[#c3c0ff] transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-[#d0e1fb]/40 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#505f76]">合計 通信容量</span>
                <span class="material-symbols-outlined text-2xl text-sky-600 bg-sky-50 p-2 rounded-xl">data_usage</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black text-[#131b2e] tracking-tight">{{ number_format($totalDataCapacity, 1) }}</span>
                    <span class="text-lg font-black text-[#505f76]">GB</span>
                </div>
                <div class="flex items-center gap-2 mt-2 pt-2 border-t border-[#f2f3ff] text-xs text-[#505f76]">
                    <span>全契約回線の合算ギガ数</span>
                </div>
            </div>
        </div>

        <!-- Active Contracts Count -->
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs flex flex-col justify-between relative overflow-hidden group hover:border-[#c3c0ff] transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-emerald-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#505f76]">稼働中の回線数</span>
                <span class="material-symbols-outlined text-2xl text-emerald-600 bg-emerald-50 p-2 rounded-xl">phonelink_ring</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black text-[#131b2e] tracking-tight">{{ $activeLinesCount }}</span>
                    <span class="text-base font-bold text-[#505f76]">回線</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-[#f2f3ff] text-xs text-emerald-700 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>すべて正常に契約中</span>
                </div>
            </div>
        </div>

        <!-- Annual Savings achieved -->
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs flex flex-col justify-between relative overflow-hidden group hover:border-[#c3c0ff] transition-all">
            <div class="absolute top-0 right-0 w-28 h-28 bg-amber-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#505f76]">乗り換え累計節約効果</span>
                <span class="material-symbols-outlined text-2xl text-amber-600 bg-amber-50 p-2 rounded-xl">savings</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl sm:text-4xl font-black text-amber-600 tracking-tight">¥{{ number_format($totalSavings) }}</span>
                    <span class="text-sm font-bold text-[#505f76]">/年</span>
                </div>
                <div class="flex items-center gap-2 mt-2 pt-2 border-t border-[#f2f3ff] text-xs text-[#505f76]">
                    <span>過去のMNPによる削減累計</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrier Switching Breakdown (キャリア別 乗り換え・契約実績カウント) -->
    @if (!empty($carrierStats))
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#3525cd] text-xl">analytics</span>
                    <h3 class="text-sm font-bold text-[#131b2e]">キャリア別 契約・乗り換え実績カウント</h3>
                </div>
                <span class="text-xs text-[#505f76]">クリックして該当キャリアで絞り込み</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach ($carrierStats as $stat)
                    <button wire:click="$set('carrierFilter', '{{ $carrierFilter === $stat['name'] ? '' : $stat['name'] }}')"
                            class="p-3.5 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between gap-2 {{ $carrierFilter === $stat['name'] ? 'bg-[#eaedff] border-[#3525cd] ring-2 ring-[#3525cd]/20 shadow-xs' : 'bg-[#faf8ff]/70 border-[#c7c4d8]/50 hover:bg-[#f2f3ff]' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-[#131b2e] truncate">{{ $stat['name'] }}</span>
                            @if ($stat['active_count'] > 0)
                                <span class="w-2 h-2 rounded-full bg-emerald-500" title="現在利用中"></span>
                            @endif
                        </div>
                        <div class="space-y-1 text-[11px]">
                            <div class="flex justify-between items-center text-[#505f76]">
                                <span>現在利用:</span>
                                <strong class="{{ $stat['active_count'] > 0 ? 'text-emerald-700 font-bold' : 'text-[#777587]' }}">{{ $stat['active_count'] }}回線</strong>
                            </div>
                            <div class="flex justify-between items-center text-[#505f76]">
                                <span>乗り換え先:</span>
                                <strong class="{{ $stat['to_count'] > 0 ? 'text-[#3525cd] font-bold' : 'text-[#777587]' }}">{{ $stat['to_count'] }}回</strong>
                            </div>
                            <div class="flex justify-between items-center text-[#505f76]">
                                <span>乗り換え元:</span>
                                <strong class="{{ $stat['from_count'] > 0 ? 'text-amber-700 font-bold' : 'text-[#777587]' }}">{{ $stat['from_count'] }}回</strong>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Phone Number Transfer History Breakdown (電話番号別 乗り換え実績カウント) -->
    @if (!empty($phoneStats))
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#3525cd] text-xl">contact_phone</span>
                    <h3 class="text-sm font-bold text-[#131b2e]">電話番号別 乗り換え回数・キャリア変遷</h3>
                </div>
                <span class="text-xs text-[#505f76]">電話番号ごとの累計乗り換え回数とこれまでのキャリア履歴</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($phoneStats as $pStat)
                    <div class="bg-[#faf8ff] p-4 rounded-2xl border border-[#dae2fd] hover:border-[#c3c0ff] transition-all flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 font-mono font-bold text-sm text-[#131b2e]">
                                    <span class="material-symbols-outlined text-[#3525cd] text-base">call</span>
                                    <span>{{ $pStat['phone_number'] }}</span>
                                </div>
                                <div class="text-xs text-[#505f76] mt-0.5 font-medium">{{ $pStat['line_name'] }}</div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $pStat['transfer_count'] > 0 ? 'bg-[#eaedff] text-[#3525cd] border border-[#c3c0ff]' : 'bg-slate-100 text-slate-600' }}">
                                <span>{{ $pStat['transfer_count'] > 0 ? '乗り換え ' . $pStat['transfer_count'] . '回' : '新規回線' }}</span>
                            </span>
                        </div>

                        <!-- Carrier Flow Track -->
                        <div class="bg-white p-2.5 rounded-xl border border-[#c7c4d8]/30 space-y-1">
                            <span class="text-[10px] font-bold text-[#777587] uppercase tracking-wider">キャリア変遷</span>
                            <div class="flex items-center flex-wrap gap-1.5 pt-0.5">
                                @foreach ($pStat['carrier_flow'] as $idx => $cName)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $idx === count($pStat['carrier_flow']) - 1 ? 'bg-[#eaedff] text-[#3525cd] border border-[#dae2fd]' : 'bg-[#f2f3ff] text-[#505f76]' }}">
                                        {{ $cName }}
                                    </span>
                                    @if ($idx < count($pStat['carrier_flow']) - 1)
                                        <span class="material-symbols-outlined text-[13px] text-[#777587]">chevron_right</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1 border-t border-[#dae2fd]/60">
                            <span class="text-[#505f76]">年間削減累計:</span>
                            <span class="font-bold {{ $pStat['annual_savings'] > 0 ? 'text-emerald-700' : 'text-[#777587]' }}">
                                {{ $pStat['annual_savings'] > 0 ? '-¥' . number_format($pStat['annual_savings']) . '/年' : '¥0' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Lines Section -->
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[#3525cd] text-2xl">smartphone</span>
                <h2 class="text-xl font-bold text-[#131b2e]">契約回線一覧</h2>
                <span class="text-xs font-bold bg-[#eaedff] text-[#3525cd] px-3 py-1 rounded-full">
                    {{ $lines->count() }} @if ($totalLinesCount !== $lines->count()) / {{ $totalLinesCount }} @endif 回線
                </span>
            </div>
        </div>

        <!-- Search & Filter Toolbar -->
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-5 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#777587]">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                </span>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="回線名、電話番号、キャリア、プラン、名義人で検索..."
                       class="w-full pl-10 pr-10 py-2.5 bg-[#faf8ff] border border-[#c7c4d8]/60 rounded-2xl text-sm text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd] transition-all">
                @if ($search !== '')
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#777587] hover:text-[#131b2e] cursor-pointer">
                        <span class="material-symbols-outlined text-lg">cancel</span>
                    </button>
                @endif
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Carrier Filter -->
                <select wire:model.live="carrierFilter" 
                        class="px-3.5 py-2.5 bg-[#faf8ff] border border-[#c7c4d8]/60 rounded-2xl text-xs font-semibold text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                    <option value="">すべてのキャリア</option>
                    @foreach ($carriers as $c)
                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                    @endforeach
                </select>

                <!-- Safe Period Filter (短期解約防止フィルター) -->
                <select wire:model.live="safeFilter" 
                        class="px-3.5 py-2.5 bg-[#faf8ff] border border-[#c7c4d8]/60 rounded-2xl text-xs font-semibold text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                    <option value="">安全状態: すべて</option>
                    <option value="safe">安全達成 (転出OK)</option>
                    <option value="caution">まもなく安全達成</option>
                    <option value="danger">短期利用中 (BL警戒)</option>
                </select>

                <!-- Status Filter -->
                <select wire:model.live="statusFilter" 
                        class="px-3.5 py-2.5 bg-[#faf8ff] border border-[#c7c4d8]/60 rounded-2xl text-xs font-semibold text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                    <option value="">すべてのステータス</option>
                    <option value="active">契約中 (利用中)</option>
                    <option value="reserved">MNP予約中</option>
                    <option value="transferred">乗り換え済み</option>
                    <option value="cancelled">解約済み</option>
                </select>

                @if ($search !== '' || $carrierFilter !== '' || $statusFilter !== '' || $safeFilter !== '')
                    <button wire:click="resetFilters" 
                            class="px-3.5 py-2.5 text-xs font-bold text-[#ba1a1a] hover:bg-[#ffdad6]/40 rounded-2xl transition-colors flex items-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                        <span>リセット</span>
                    </button>
                @endif
            </div>
        </div>

        @if ($lines->isEmpty())
            @if ($search !== '' || $carrierFilter !== '' || $statusFilter !== '' || $safeFilter !== '')
                <!-- No Search Results Empty State -->
                <div class="bg-white border border-[#c7c4d8]/50 rounded-3xl p-12 text-center shadow-xs">
                    <div class="w-16 h-16 bg-[#f2f3ff] text-[#505f76] rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-outlined text-3xl">search_off</span>
                    </div>
                    <h3 class="text-base font-bold text-[#131b2e] mb-1">条件に一致する回線が見つかりませんでした</h3>
                    <p class="text-xs text-[#505f76] max-w-sm mx-auto mb-4">検索キーワードやフィルター条件を変更してお試しください。</p>
                    <button wire:click="resetFilters" 
                            class="inline-flex items-center gap-1.5 bg-[#eaedff] text-[#3525cd] hover:bg-[#d0e1fb] text-xs font-bold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                        <span>検索条件をクリア</span>
                    </button>
                </div>
            @else
                <div class="bg-white border border-[#c7c4d8]/50 rounded-3xl p-16 text-center shadow-xs">
                    <div class="w-20 h-20 bg-[#eaedff] text-[#3525cd] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-4xl">add_to_queue</span>
                    </div>
                    <h3 class="text-xl font-bold text-[#131b2e] mb-2">契約回線がまだ登録されていません</h3>
                    <p class="text-sm text-[#505f76] max-w-md mx-auto mb-6 leading-relaxed">
                        現在お使いの携帯電話会社の回線情報を登録して、月額料金や利用期間の自動計算、乗り換え比較を始めましょう。
                    </p>
                    <a href="{{ route('lines.create') }}" 
                       class="inline-flex items-center gap-2 bg-[#3525cd] hover:bg-[#291cb0] text-white text-sm font-bold px-7 py-3.5 rounded-2xl shadow-lg shadow-[#3525cd]/20 transition-all">
                        <span class="material-symbols-outlined text-2xl">add</span>
                        <span>最初の回線を登録する</span>
                    </a>
                </div>
            @endif
        @else
            <!-- VIEW MODE 1: Spacious Table View (Desktop & Tablet) -->
            @if ($viewMode === 'table')
                <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1200px]">
                            <thead>
                                <tr class="border-b border-[#c7c4d8]/30 bg-[#faf8ff] text-[11px] font-bold text-[#505f76] uppercase tracking-wider">
                                    <th class="py-4 px-6">回線名・電話番号</th>
                                    <th class="py-4 px-6">携帯会社 / プラン</th>
                                    <th class="py-4 px-6">月額料金 / 容量</th>
                                    <th class="py-4 px-6">契約名義人 / 使用者</th>
                                    <th class="py-4 px-6">利用期間 & 安全維持メーター</th>
                                    <th class="py-4 px-6 text-center">乗り換え歴</th>
                                    <th class="py-4 px-6">暗証番号 (PIN)</th>
                                    <th class="py-4 px-6 text-center">状態</th>
                                    <th class="py-4 px-6 text-right">操作</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#c7c4d8]/20 text-sm">
                                @foreach ($lines as $line)
                                    <tr class="hover:bg-[#f8faff] transition-colors group">
                                        <!-- Line Name & Phone -->
                                        <td class="py-5 px-6 whitespace-nowrap">
                                            <div class="font-bold text-sm text-[#131b2e] group-hover:text-[#3525cd] transition-colors">
                                                {{ $line->line_name }}
                                            </div>
                                            <div class="text-xs text-[#505f76] font-mono tracking-wider mt-1 flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[15px] text-[#777587]">call</span>
                                                <span>{{ $line->phone_number ?? '番号未登録' }}</span>
                                            </div>
                                            <!-- MNP Expiration Alert if reserved -->
                                            @if ($line->status === 'reserved' && $line->mnp_reservation_number)
                                                <div class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $line->mnp_days_remaining <= 3 ? 'bg-red-50 text-red-700 border border-red-200 animate-pulse' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                                    <span class="material-symbols-outlined text-[12px]">timer</span>
                                                    <span>MNP残 {{ $line->mnp_days_remaining }}日 ({{ $line->mnp_reservation_number }})</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Carrier & Plan (With Official Brand Colors) -->
                                        <td class="py-5 px-6 whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl font-bold text-xs border shadow-2xs {{ $line->brand_badge_class }}">
                                                <span class="material-symbols-outlined text-[16px]">signal_cellular_alt</span>
                                                <span>{{ $line->carrier_name }}</span>
                                            </div>
                                            @if ($line->plan_name)
                                                <div class="text-xs text-[#505f76] mt-1.5 max-w-[200px] truncate" title="{{ $line->plan_name }}">
                                                    {{ $line->plan_name }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Monthly Fee & Data -->
                                        <td class="py-5 px-6 whitespace-nowrap">
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-black text-[#131b2e]">¥{{ number_format($line->monthly_fee) }}</span>
                                                <span class="text-xs font-medium text-[#505f76]">/月</span>
                                            </div>
                                            <div class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md mt-1 border border-indigo-100">
                                                <span class="material-symbols-outlined text-[14px]">data_usage</span>
                                                <span>{{ number_format($line->data_capacity, 1) }} GB</span>
                                            </div>
                                        </td>

                                        <!-- Contract Holder & Actual User -->
                                        <td class="py-5 px-6 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-[#505f76] bg-[#f2f3ff] px-2 py-0.5 rounded">名義</span>
                                                <span class="text-xs font-semibold text-[#131b2e]">{{ $line->contract_holder }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <span class="text-[10px] font-bold text-[#505f76] bg-[#f8fafc] px-2 py-0.5 rounded border border-[#e2e8f0]">使用</span>
                                                <span class="text-xs font-medium text-[#505f76]">{{ $line->actual_user }}</span>
                                            </div>
                                        </td>

                                        <!-- Usage Period & Safe Period Meter (短期解約防止メーター) -->
                                        <td class="py-5 px-6 whitespace-nowrap">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs font-bold text-[#131b2e] flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm text-[#3525cd]">schedule</span>
                                                    <span>{{ $line->usage_period_human }}</span>
                                                </span>
                                                <span class="text-[11px] font-mono text-[#505f76]">
                                                    {{ $line->usage_days }}日 / {{ $line->target_safe_period_days }}日
                                                </span>
                                            </div>
                                            <!-- Progress Bar -->
                                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mt-1.5 border border-slate-200/60">
                                                <div class="h-full transition-all duration-500 rounded-full {{ $line->safe_status === 'safe' ? 'bg-emerald-500' : ($line->safe_status === 'caution' ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                     style="width: {{ $line->safe_period_progress }}%;"></div>
                                            </div>
                                            <!-- Safe Status Badge -->
                                            <div class="mt-1.5 flex items-center justify-between">
                                                @if ($line->safe_status === 'safe')
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                        <span class="material-symbols-outlined text-[12px]">verified</span>
                                                        <span>安全達成 (転出OK)</span>
                                                    </span>
                                                @elseif ($line->safe_status === 'caution')
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                                        <span class="material-symbols-outlined text-[12px]">warning</span>
                                                        <span>まもなく安全 (残 {{ $line->safe_period_remaining_days }}日)</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                                                        <span class="material-symbols-outlined text-[12px]">report</span>
                                                        <span>短期利用中 (残 {{ $line->safe_period_remaining_days }}日)</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Transfer Count Badge (乗り換え回数カウント) -->
                                        <td class="py-5 px-6 text-center whitespace-nowrap">
                                            @if ($line->transfer_count > 0)
                                                <button wire:click="openLineHistoryModal({{ $line->id }})" 
                                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-[#eaedff] text-[#3525cd] hover:bg-[#d0e1fb] border border-[#c3c0ff] transition-colors cursor-pointer"
                                                        title="過去の乗り換え履歴を確認">
                                                    <span class="material-symbols-outlined text-[15px]">sync_saved_locally</span>
                                                    <span>乗り換え {{ $line->transfer_count }}回</span>
                                                </button>
                                            @else
                                                <span class="text-xs text-[#777587] font-medium">新規回線</span>
                                            @endif
                                        </td>

                                        <!-- Network PIN (Masking & Toggle) -->
                                        <td class="py-5 px-6 whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2.5 bg-[#f8fafc] px-3.5 py-1.5 rounded-xl border border-[#e2e8f0]">
                                                <span class="material-symbols-outlined text-base text-[#777587]">lock</span>
                                                @if (!empty($line->network_pin))
                                                    <span class="font-mono text-xs font-black tracking-widest {{ ($showPins[$line->id] ?? false) ? 'text-[#3525cd]' : 'text-[#505f76]' }}">
                                                        {{ ($showPins[$line->id] ?? false) ? $line->network_pin : '••••' }}
                                                    </span>
                                                    <button wire:click="togglePin({{ $line->id }})" 
                                                            class="text-[#777587] hover:text-[#3525cd] transition-colors p-0.5 rounded-lg cursor-pointer"
                                                            title="{{ ($showPins[$line->id] ?? false) ? '暗証番号を隠す' : '暗証番号を表示' }}">
                                                        <span class="material-symbols-outlined text-[17px]">
                                                            {{ ($showPins[$line->id] ?? false) ? 'visibility_off' : 'visibility' }}
                                                        </span>
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#777587] italic">未設定</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Status -->
                                        <td class="py-5 px-6 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold border whitespace-nowrap {{ $line->status_badge_class }}">
                                                {{ $line->status_label }}
                                            </span>
                                        </td>

                                        <!-- Actions (編集・削除・乗換) -->
                                        <td class="py-5 px-6 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('lines.create', ['from_line_id' => $line->id]) }}" 
                                                   class="p-2 text-[#3525cd] hover:bg-[#eaedff] rounded-xl transition-all font-semibold text-xs flex items-center gap-1"
                                                   title="この回線を他社に乗り換え比較する">
                                                    <span class="material-symbols-outlined text-[18px]">swap_horiz</span>
                                                    <span>乗換</span>
                                                </a>
                                                <button wire:click="openEditModal({{ $line->id }})" 
                                                        class="p-2 text-[#505f76] hover:bg-[#f2f3ff] hover:text-[#131b2e] rounded-xl transition-colors cursor-pointer"
                                                        title="回線情報を編集">
                                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                                </button>
                                                <button wire:click="confirmDelete({{ $line->id }})" 
                                                        class="p-2 text-[#ba1a1a] hover:bg-[#ffdad6]/50 rounded-xl transition-colors cursor-pointer"
                                                        title="回線を削除">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- VIEW MODE 2: Spacious Grid / Card View -->
            @if ($viewMode === 'grid')
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ($lines as $line)
                        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs relative overflow-hidden flex flex-col justify-between gap-5 hover:border-[#c3c0ff] hover:shadow-md transition-all">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-bold text-xs border shadow-2xs {{ $line->brand_badge_class }}">
                                            <span class="material-symbols-outlined text-[15px]">signal_cellular_alt</span>
                                            <span>{{ $line->carrier_name }}</span>
                                        </div>
                                        @if ($line->transfer_count > 0)
                                            <button wire:click="openLineHistoryModal({{ $line->id }})" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#eaedff] text-[#3525cd] hover:bg-[#d0e1fb] border border-[#c3c0ff] transition-colors cursor-pointer">
                                                <span>乗り換え {{ $line->transfer_count }}回</span>
                                            </button>
                                        @endif
                                    </div>
                                    <h3 class="font-bold text-base text-[#131b2e] mt-1">{{ $line->line_name }}</h3>
                                    <p class="text-xs text-[#505f76] font-mono tracking-wide">{{ $line->phone_number ?? '番号未登録' }}</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border whitespace-nowrap {{ $line->status_badge_class }}">
                                    {{ $line->status_label }}
                                </span>
                            </div>

                            @if ($line->plan_name)
                                <div class="bg-[#faf8ff] px-3.5 py-2 rounded-xl text-xs text-[#505f76] border border-[#dae2fd]/50">
                                    プラン: <span class="font-semibold text-[#131b2e]">{{ $line->plan_name }}</span>
                                </div>
                            @endif

                            <!-- Metrics Grid -->
                            <div class="grid grid-cols-2 gap-4 py-3 border-y border-[#c7c4d8]/20 text-xs">
                                <div>
                                    <p class="text-[10px] text-[#777587] font-bold uppercase tracking-wider">月額料金</p>
                                    <div class="flex items-baseline gap-1 mt-0.5">
                                        <span class="text-xl font-black text-[#131b2e]">¥{{ number_format($line->monthly_fee) }}</span>
                                        <span class="text-[10px] text-[#505f76]">/月</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] text-[#777587] font-bold uppercase tracking-wider">データ容量</p>
                                    <div class="flex items-baseline gap-1 mt-0.5">
                                        <span class="text-xl font-black text-indigo-600">{{ number_format($line->data_capacity, 1) }}</span>
                                        <span class="text-xs font-bold text-[#505f76]">GB</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] text-[#777587] font-bold uppercase tracking-wider">利用期間</p>
                                    <p class="font-bold text-xs text-[#131b2e] mt-1">{{ $line->usage_period_human }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-[#777587] font-bold uppercase tracking-wider">名義 / 使用者</p>
                                    <p class="font-medium text-xs text-[#131b2e] mt-1 truncate" title="{{ $line->contract_holder }} / {{ $line->actual_user }}">
                                        {{ $line->contract_holder }} / {{ $line->actual_user }}
                                    </p>
                                </div>
                            </div>

                            <!-- Safe Period Meter on Card -->
                            <div class="bg-[#faf8ff] p-3 rounded-2xl border border-[#dae2fd]/60 space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-[11px] font-bold text-[#505f76]">短期解約防止安全期間</span>
                                    <span class="font-mono text-[11px] font-bold text-[#131b2e]">{{ $line->usage_days }}日 / {{ $line->target_safe_period_days }}日</span>
                                </div>
                                <div class="w-full h-2 bg-slate-200/70 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $line->safe_status === 'safe' ? 'bg-emerald-500' : ($line->safe_status === 'caution' ? 'bg-amber-500' : 'bg-rose-500') }}"
                                         style="width: {{ $line->safe_period_progress }}%;"></div>
                                </div>
                                <div class="flex justify-between items-center pt-0.5">
                                    @if ($line->safe_status === 'safe')
                                        <span class="text-[10px] font-bold text-emerald-700">安全達成 (解約・転出OK)</span>
                                    @elseif ($line->safe_status === 'caution')
                                        <span class="text-[10px] font-bold text-amber-800">まもなく安全 (残り {{ $line->safe_period_remaining_days }}日)</span>
                                    @else
                                        <span class="text-[10px] font-bold text-rose-700">短期利用中 (残り {{ $line->safe_period_remaining_days }}日)</span>
                                    @endif
                                    <span class="text-[10px] text-[#777587] font-bold">{{ $line->safe_period_progress }}%</span>
                                </div>
                            </div>

                            <!-- PIN & Actions (編集・削除・乗換) -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-2 bg-[#f8fafc] px-3 py-1.5 rounded-xl border border-[#e2e8f0]">
                                    <span class="text-[10px] text-[#777587] font-bold">PIN:</span>
                                    @if (!empty($line->network_pin))
                                        <span class="font-mono text-xs font-black tracking-widest text-[#3525cd]">
                                            {{ ($showPins[$line->id] ?? false) ? $line->network_pin : '••••' }}
                                        </span>
                                        <button wire:click="togglePin({{ $line->id }})" class="text-[#777587] hover:text-[#3525cd] p-0.5 cursor-pointer">
                                            <span class="material-symbols-outlined text-[16px]">
                                                {{ ($showPins[$line->id] ?? false) ? 'visibility_off' : 'visibility' }}
                                            </span>
                                        </button>
                                    @else
                                        <span class="text-xs text-[#777587] italic">未設定</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('lines.create', ['from_line_id' => $line->id]) }}" 
                                       class="px-3 py-1.5 text-[#3525cd] bg-[#eaedff] hover:bg-[#d0e1fb] rounded-xl font-bold text-xs flex items-center gap-1 transition-colors"
                                       title="乗り換え比較">
                                        <span class="material-symbols-outlined text-[16px]">swap_horiz</span>
                                        <span>乗換</span>
                                    </a>
                                    <button wire:click="openEditModal({{ $line->id }})" 
                                            class="p-2 text-[#505f76] hover:bg-[#f2f3ff] rounded-xl transition-colors cursor-pointer"
                                            title="回線情報を編集">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button wire:click="confirmDelete({{ $line->id }})" 
                                            class="p-2 text-[#ba1a1a] hover:bg-[#ffdad6]/50 rounded-xl transition-colors cursor-pointer"
                                            title="回線を削除">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <!-- Line Specific Transfer History Modal (回線別 乗り換え履歴モーダル) -->
    @if ($viewingLineHistory && $selectedLine)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl border border-[#c7c4d8]/50 shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto space-y-6">
                <div class="flex justify-between items-center pb-4 border-b border-[#c7c4d8]/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#eaedff] text-[#3525cd] flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">sync_saved_locally</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#131b2e]">{{ $selectedLine->line_name }} の乗り換え履歴</h3>
                            <p class="text-xs text-[#505f76]">電話番号: {{ $selectedLine->phone_number ?? '未登録' }} | 現在: {{ $selectedLine->carrier_name }}</p>
                        </div>
                    </div>
                    <button wire:click="closeLineHistoryModal" class="p-1.5 rounded-full text-[#777587] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </button>
                </div>

                @if ($selectedLineHistories->isEmpty())
                    <div class="py-8 text-center text-xs text-[#505f76]">
                        この回線に関する乗り換え履歴はまだ記録されていません。
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($selectedLineHistories as $hist)
                            <div class="bg-[#faf8ff] p-4 rounded-2xl border border-[#dae2fd] space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-[#131b2e]">乗り換え日: {{ $hist->transfer_date ? $hist->transfer_date->format('Y年m月d日') : '-' }}</span>
                                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                        月額 {{ $hist->monthly_saving >= 0 ? '-' : '+' }}¥{{ number_format(abs($hist->monthly_saving)) }} 節約
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <div class="font-semibold text-[#505f76]">{{ $hist->from_carrier_name }} (¥{{ number_format($hist->from_monthly_fee) }})</div>
                                    <span class="material-symbols-outlined text-[#3525cd] text-base">arrow_forward</span>
                                    <div class="font-bold text-[#3525cd]">{{ $hist->to_carrier_name }} (¥{{ number_format($hist->to_monthly_fee) }})</div>
                                </div>
                                @if ($hist->usage_period_text)
                                    <p class="text-[11px] text-[#777587]">旧回線の利用期間: {{ $hist->usage_period_text }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end pt-2">
                    <button wire:click="closeLineHistoryModal" class="px-5 py-2.5 rounded-xl bg-[#f2f3ff] text-xs font-bold text-[#505f76] hover:bg-[#eaedff] cursor-pointer">
                        閉じる
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- CSV Import Modal (一括インポートモーダル) -->
    @if ($isImporting)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl border border-[#c7c4d8]/50 shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-5">
                <div class="flex justify-between items-center pb-3 border-b border-[#c7c4d8]/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#eaedff] text-[#3525cd] flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">upload_file</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#131b2e]">CSV 一括インポート</h3>
                    </div>
                    <button wire:click="closeImportModal" class="p-1.5 rounded-full text-[#777587] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </button>
                </div>

                <div class="bg-[#faf8ff] p-4 rounded-2xl border border-[#dae2fd] text-xs text-[#505f76] space-y-2">
                    <p class="font-bold text-[#131b2e]">フォーマットについて</p>
                    <p>エクスポートしたCSVと同様の形式で、複数の回線情報を一括登録できます。</p>
                    <p class="text-[11px] text-[#777587]">列順: [ID, 回線名, 電話番号, 携帯会社名, プラン名, 月額料金, 通信容量, 名義人, 使用者, 契約開始日(YYYY-MM-DD), ...]</p>
                </div>

                <form wire:submit="importCsv" class="space-y-4">
                    <div class="border-2 border-dashed border-[#c7c4d8] hover:border-[#3525cd] rounded-2xl p-6 text-center transition-colors">
                        <input type="file" wire:model="csvFile" accept=".csv,text/csv,text/plain" required class="block w-full text-xs text-[#505f76] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#eaedff] file:text-[#3525cd] hover:file:bg-[#d0e1fb] cursor-pointer">
                        @error('csvFile') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="csvFile" class="text-xs text-[#3525cd] font-bold mt-2">
                            ファイルを読み込み中...
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" wire:click="closeImportModal" class="px-5 py-2.5 rounded-xl border border-[#c7c4d8] text-xs font-semibold text-[#505f76] hover:bg-[#f2f3ff] cursor-pointer">
                            キャンセル
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-6 py-2.5 rounded-xl bg-[#3525cd] hover:bg-[#291cb0] text-xs font-bold text-white shadow-md cursor-pointer">
                            インポートを実行
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit Line Modal (編集モーダル) -->
    @if ($isEditing)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl border border-[#c7c4d8]/50 shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-4 border-b border-[#c7c4d8]/30 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#eaedff] text-[#3525cd] flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">edit_document</span>
                        </div>
                        <h3 class="text-xl font-bold text-[#131b2e]">契約回線情報の編集</h3>
                    </div>
                    <button wire:click="closeEditModal" class="p-1.5 rounded-full text-[#777587] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </button>
                </div>

                <form wire:submit="updateLine" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Line Name -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">回線識別名 *</label>
                            <input wire:model="edit_line_name" type="text" required class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Phone Number -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">電話番号</label>
                            <input wire:model="edit_phone_number" type="text" placeholder="090-1234-5678" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Carrier Selection -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">携帯会社名 *</label>
                            <select wire:model.live="edit_carrier_name" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-white focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                                @foreach ($carriers as $c)
                                    <option value="{{ $c->name }}">{{ $c->name }} ({{ $c->type }})</option>
                                @endforeach
                                <option value="その他">その他（自由入力）</option>
                            </select>
                        </div>

                        <!-- Plan Name -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">料金プラン名</label>
                            <input wire:model="edit_plan_name" type="text" placeholder="例: ahamo 30GB" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Monthly Fee -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">月額料金 (円) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#777587] text-xs font-bold">¥</span>
                                <input wire:model="edit_monthly_fee" type="number" min="0" required class="w-full pl-8 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd] font-bold">
                            </div>
                        </div>

                        <!-- Data Capacity -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">通信容量 (GB) *</label>
                            <div class="relative">
                                <input wire:model="edit_data_capacity" type="number" step="0.1" min="0" required class="w-full pr-10 pl-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd] font-bold">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#777587] text-xs font-bold">GB</span>
                            </div>
                        </div>

                        <!-- Contract Holder -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">契約名義人 *</label>
                            <input wire:model="edit_contract_holder" type="text" required class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Actual User -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">使用者 (利用者) *</label>
                            <input wire:model="edit_actual_user" type="text" required class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Network PIN -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">ネットワーク暗証番号 (PIN)</label>
                            <input wire:model="edit_network_pin" type="text" placeholder="4桁の数字など" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm font-mono tracking-widest bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Contract Start Date -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">契約開始日 *</label>
                            <input wire:model="edit_contract_start_date" type="date" required class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-white focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Custom Safe Period Days (目標安全日数) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">目標安全維持日数 (日数)</label>
                            <input wire:model="edit_custom_safe_period_days" type="number" min="0" placeholder="空欄ならキャリア標準 (180日/211日)" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60 focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Status -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">利用状況ステータス</label>
                            <select wire:model="edit_status" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-white focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                                <option value="active">契約中 (利用中)</option>
                                <option value="reserved">MNP予約中</option>
                                <option value="transferred">乗り換え済み</option>
                                <option value="cancelled">解約済み</option>
                            </select>
                        </div>

                        <!-- MNP Reservation Number -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">MNP予約番号（任意）</label>
                            <input wire:model="edit_mnp_reservation_number" type="text" placeholder="例: 1234567890" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60">
                        </div>

                        <!-- MNP Expire Date -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">MNP有効期限</label>
                            <input wire:model="edit_mnp_reservation_expire_date" type="date" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-white">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#464555]">メモ・特記事項</label>
                        <textarea wire:model="edit_notes" rows="2" class="w-full px-3.5 py-2 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/60"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-[#c7c4d8]/30">
                        <button type="button" wire:click="closeEditModal" class="px-5 py-2.5 rounded-xl border border-[#c7c4d8] text-sm font-semibold text-[#505f76] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                            キャンセル
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#3525cd] hover:bg-[#291cb0] text-sm font-semibold text-white shadow-md shadow-[#3525cd]/20 transition-all cursor-pointer">
                            更新を保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal (削除確認モーダル) -->
    @if ($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl border border-[#c7c4d8]/50 shadow-2xl max-w-md w-full p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl">warning</span>
                </div>
                <h3 class="text-lg font-bold text-[#131b2e] mb-2">回線情報を削除しますか？</h3>
                <p class="text-xs text-[#505f76] mb-6 leading-relaxed">この操作は取り消せません。該当の回線データが完全に削除されます。</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="$set('confirmingDelete', false)" class="px-5 py-2.5 rounded-xl border border-[#c7c4d8] text-xs font-semibold text-[#505f76] hover:bg-[#f2f3ff] cursor-pointer">
                        キャンセル
                    </button>
                    <button wire:click="deleteLine" class="px-5 py-2.5 rounded-xl bg-[#ba1a1a] hover:bg-red-700 text-xs font-semibold text-white shadow-md cursor-pointer">
                        削除を実行する
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="space-y-8 w-full">
    <!-- Toast Message -->
    @if ($toastMessage)
        <div class="fixed top-5 right-5 z-50 flex items-center gap-3 bg-emerald-600 text-white px-5 py-3 rounded-2xl shadow-xl transition-all duration-300 animate-in fade-in slide-in-from-top-4">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            <span class="text-sm font-medium">{{ $toastMessage }}</span>
            <button wire:click="clearToast" class="hover:opacity-75 transition-opacity ml-2 cursor-pointer">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#131b2e] tracking-tight">乗り換え履歴 (MNP History)</h1>
            <p class="text-sm text-[#505f76] mt-1 font-medium">これまでの携帯キャリア乗り換え変遷とコスト削減・実質収支の記録</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="exportCsv" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white border border-[#c7c4d8]/60 text-xs font-bold text-[#131b2e] hover:bg-[#f2f3ff] transition-all shadow-xs cursor-pointer"
                    title="乗り換え履歴をCSVダウンロード">
                <span class="material-symbols-outlined text-lg text-emerald-600">download</span>
                <span>CSV出力</span>
            </button>
            <button wire:click="openCreateModal" 
                    class="inline-flex items-center gap-2 bg-[#3525cd] hover:bg-[#291cb0] text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-[#3525cd]/20 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-xl">post_add</span>
                <span>過去の履歴を手動登録</span>
            </button>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-[#c7c4d8]/40 p-5 rounded-3xl shadow-xs">
            <div class="text-xs font-bold text-[#505f76] uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-amber-600 text-lg">savings</span>
                <span>累計年間節約効果</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-[#3525cd]">
                ¥{{ number_format($totalAnnualSaving) }}<span class="text-xs font-medium text-[#505f76]">/年</span>
            </div>
        </div>

        <div class="bg-white border border-[#c7c4d8]/40 p-5 rounded-3xl shadow-xs">
            <div class="text-xs font-bold text-[#505f76] uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-emerald-600 text-lg">swap_calls</span>
                <span>乗り換え総回数</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-[#131b2e]">
                {{ $historyCount }}<span class="text-xs font-medium text-[#505f76]">回</span>
            </div>
        </div>

        <div class="bg-white border border-[#c7c4d8]/40 p-5 rounded-3xl shadow-xs">
            <div class="text-xs font-bold text-[#505f76] uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-indigo-600 text-lg">trending_down</span>
                <span>1回あたりの平均月額削減</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-700">
                -¥{{ number_format($avgMonthlySaving) }}<span class="text-xs font-medium text-[#505f76]">/月</span>
            </div>
        </div>
    </div>

    <!-- Phone Number Breakdown Badges (電話番号別 乗り換え回数) -->
    @if (!empty($phoneCounts))
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#3525cd] text-lg">contact_phone</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#505f76]">電話番号別 乗り換え実績</h3>
                </div>
                <span class="text-[11px] text-[#777587]">バッジをクリックして絞り込み</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($phoneCounts as $item)
                    <button wire:click="$set('search', '{{ $search === $item['phone_number'] ? '' : $item['phone_number'] }}')"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer {{ $search === $item['phone_number'] ? 'bg-[#3525cd] text-white border-[#3525cd] shadow-xs' : 'bg-[#faf8ff] border-[#dae2fd] text-[#131b2e] hover:bg-[#eaedff]' }}">
                        <span class="font-mono">{{ $item['phone_number'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $search === $item['phone_number'] ? 'bg-white/20 text-white' : 'bg-[#eaedff] text-[#3525cd]' }}">
                            {{ $item['count'] }}回
                        </span>
                        <span class="text-[11px] {{ $search === $item['phone_number'] ? 'text-white/90' : 'text-emerald-700 font-bold' }}">
                            -¥{{ number_format($item['annual_saving']) }}/年
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 shadow-xs flex items-center gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#777587]">
                <span class="material-symbols-outlined text-[20px]">search</span>
            </span>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="電話番号、回線名、キャリア名、名義人で履歴を絞り込み..."
                   class="w-full pl-10 pr-10 py-2 bg-[#faf8ff] border border-[#c7c4d8]/60 rounded-xl text-sm text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
            @if ($search !== '')
                <button wire:click="resetFilters" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#777587] hover:text-[#131b2e] cursor-pointer">
                    <span class="material-symbols-outlined text-lg">cancel</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Timeline of Transfer Histories -->
    @if ($histories->isEmpty())
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-12 text-center shadow-xs">
            <div class="w-16 h-16 bg-[#f2f3ff] text-[#505f76] rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-3xl">history_toggle_off</span>
            </div>
            <h3 class="text-base font-bold text-[#131b2e] mb-1">乗り換え履歴がありません</h3>
            <p class="text-xs text-[#505f76] max-w-sm mx-auto mb-4">回線の乗り換えを実行するか、過去の履歴を手動登録してください。</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($histories as $history)
                <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-6 shadow-xs hover:border-[#c3c0ff] transition-all space-y-4">
                    <!-- Top Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#c7c4d8]/20">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[#3525cd] text-xl">event</span>
                            <span class="font-bold text-sm text-[#131b2e]">
                                {{ $history->transfer_date ? $history->transfer_date->format('Y年m月d日') : '日付未登録' }}
                            </span>
                            @if ($history->phone_number)
                                <span class="text-xs font-mono bg-[#f2f3ff] text-[#3525cd] px-2.5 py-0.5 rounded-md font-bold">
                                    {{ $history->phone_number }}
                                </span>
                            @endif
                            <span class="text-xs text-[#505f76] font-medium">{{ $history->line_name }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                月額 {{ $history->monthly_saving >= 0 ? '-' : '+' }}¥{{ number_format(abs($history->monthly_saving)) }} / 年間 {{ $history->annual_saving >= 0 ? '-' : '+' }}¥{{ number_format(abs($history->annual_saving)) }}
                            </span>
                            <button wire:click="confirmDelete({{ $history->id }})" class="text-[#ba1a1a] hover:bg-[#ffdad6]/50 p-1.5 rounded-lg transition-colors cursor-pointer" title="履歴を削除">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Flow Diagram (From -> To) -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center bg-[#faf8ff] p-4 rounded-2xl border border-[#dae2fd]">
                        <!-- From Carrier -->
                        <div class="md:col-span-2 space-y-1">
                            <span class="text-[10px] font-bold text-[#ba1a1a] uppercase tracking-wider bg-rose-50 px-2 py-0.5 rounded">乗り換え元 (解約)</span>
                            <h4 class="font-bold text-sm text-[#131b2e]">{{ $history->from_carrier_name }}</h4>
                            <p class="text-xs text-[#505f76]">{{ $history->from_plan_name ?? 'プラン名未登録' }}</p>
                            <div class="flex items-baseline gap-1 text-sm font-black text-[#131b2e] pt-1">
                                <span>¥{{ number_format($history->from_monthly_fee) }}</span>
                                <span class="text-[10px] text-[#505f76]">/月</span>
                                @if ($history->from_data_capacity)
                                    <span class="text-xs font-normal text-[#505f76] ml-2">({{ $history->from_data_capacity }}GB)</span>
                                @endif
                            </div>
                        </div>

                        <!-- Arrow & Savings -->
                        <div class="text-center flex flex-col items-center justify-center">
                            <div class="w-10 h-10 rounded-full bg-[#eaedff] text-[#3525cd] flex items-center justify-center shadow-xs">
                                <span class="material-symbols-outlined text-xl">arrow_forward</span>
                            </div>
                            @if ($history->usage_period_text)
                                <span class="text-[10px] text-[#777587] mt-1">利用期間: {{ $history->usage_period_text }}</span>
                            @endif
                        </div>

                        <!-- To Carrier -->
                        <div class="md:col-span-2 space-y-1">
                            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded">乗り換え先 (新規契約)</span>
                            <h4 class="font-bold text-sm text-[#3525cd]">{{ $history->to_carrier_name }}</h4>
                            <p class="text-xs text-[#505f76]">{{ $history->to_plan_name ?? 'プラン名未登録' }}</p>
                            <div class="flex items-baseline gap-1 text-sm font-black text-[#3525cd] pt-1">
                                <span>¥{{ number_format($history->to_monthly_fee) }}</span>
                                <span class="text-[10px] text-[#505f76]">/月</span>
                                @if ($history->to_data_capacity)
                                    <span class="text-xs font-normal text-[#505f76] ml-2">({{ $history->to_data_capacity }}GB)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Financial Breakdown (端末代・CB・トータル実質収支) -->
                    @if ($history->device_cost || $history->cashback_amount || $history->admin_fee || $history->device_sale_profit || $history->total_net_saving)
                        <div class="bg-[#f8fafc] p-3.5 rounded-2xl border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex flex-wrap items-center gap-4">
                                @if ($history->device_cost)
                                    <div><span class="text-[#777587]">端末購入:</span> <strong class="text-[#131b2e]">¥{{ number_format($history->device_cost) }}</strong></div>
                                @endif
                                @if ($history->cashback_amount)
                                    <div><span class="text-[#777587]">CB還元:</span> <strong class="text-emerald-700">+¥{{ number_format($history->cashback_amount) }}</strong></div>
                                @endif
                                @if ($history->admin_fee)
                                    <div><span class="text-[#777587]">事務手数料:</span> <strong class="text-[#131b2e]">¥{{ number_format($history->admin_fee) }}</strong></div>
                                @endif
                                @if ($history->device_sale_profit)
                                    <div><span class="text-[#777587]">端末売却益:</span> <strong class="text-emerald-700">+¥{{ number_format($history->device_sale_profit) }}</strong></div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[#505f76] font-bold">初年度トータル実質収支:</span>
                                <span class="text-base font-black {{ ($history->total_net_saving ?? $history->annual_saving) >= 0 ? 'text-emerald-700' : 'text-[#ba1a1a]' }}">
                                    {{ ($history->total_net_saving ?? $history->annual_saving) >= 0 ? '+' : '-' }}¥{{ number_format(abs($history->total_net_saving ?? $history->annual_saving)) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Footer Notes -->
                    @if ($history->notes)
                        <div class="text-xs text-[#505f76] bg-[#f8fafc] p-3 rounded-xl border border-slate-200/60">
                            {{ $history->notes }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create History Modal (手動登録モーダル) -->
    @if ($isCreating)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl border border-[#c7c4d8]/50 shadow-2xl max-w-2xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-4 border-b border-[#c7c4d8]/30 mb-6">
                    <h3 class="text-xl font-bold text-[#131b2e]">過去の乗り換え履歴を手動登録</h3>
                    <button wire:click="closeCreateModal" class="p-1.5 rounded-full text-[#777587] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </button>
                </div>

                <form wire:submit="createHistory" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="font-bold text-[#464555]">電話番号</label>
                            <input wire:model="new_phone_number" type="text" placeholder="090-1234-5678" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl">
                        </div>

                        <!-- From Carrier -->
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え元キャリア名 *</label>
                            <input wire:model="new_from_carrier_name" type="text" required placeholder="例: NTTドコモ" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え前の月額料金 (円) *</label>
                            <input wire:model="new_from_monthly_fee" type="number" required placeholder="7315" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl font-bold">
                        </div>

                        <!-- To Carrier -->
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え先キャリア名 *</label>
                            <input wire:model="new_to_carrier_name" type="text" required placeholder="例: ahamo" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え後の月額料金 (円) *</label>
                            <input wire:model="new_to_monthly_fee" type="number" required placeholder="2970" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl font-bold">
                        </div>

                        <!-- Financials -->
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">新端末購入費 (円)</label>
                            <input wire:model="new_device_cost" type="number" min="0" placeholder="0" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">CB・還元ポイント (円)</label>
                            <input wire:model="new_cashback_amount" type="number" min="0" placeholder="0" class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl text-emerald-700 font-bold">
                        </div>

                        <!-- Date -->
                        <div class="space-y-1 sm:col-span-2">
                            <label class="font-bold text-[#464555]">乗り換え日 *</label>
                            <input wire:model="new_transfer_date" type="date" required class="w-full px-3.5 py-2 border border-[#c7c4d8] rounded-xl">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-[#c7c4d8]/30">
                        <button type="button" wire:click="closeCreateModal" class="px-5 py-2.5 rounded-xl border border-[#c7c4d8] text-xs font-semibold text-[#505f76] cursor-pointer">
                            キャンセル
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#3525cd] hover:bg-[#291cb0] text-xs font-bold text-white shadow-md cursor-pointer">
                            履歴を保存
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
                <h3 class="text-lg font-bold text-[#131b2e] mb-2">この履歴を削除しますか？</h3>
                <p class="text-xs text-[#505f76] mb-6">この操作は取り消せません。</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="$set('confirmingDelete', false)" class="px-5 py-2.5 rounded-xl border border-[#c7c4d8] text-xs font-semibold text-[#505f76] cursor-pointer">
                        キャンセル
                    </button>
                    <button wire:click="deleteHistory" class="px-5 py-2.5 rounded-xl bg-[#ba1a1a] hover:bg-red-700 text-xs font-semibold text-white shadow-md cursor-pointer">
                        削除を実行
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

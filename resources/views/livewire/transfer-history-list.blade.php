<div class="space-y-6 sm:space-y-8 w-full">
    <!-- Toast Message -->
    @if ($toastMessage)
        <div class="fixed top-4 right-4 sm:top-5 sm:right-5 z-50 flex items-center gap-3 bg-emerald-600 text-white px-4 sm:px-5 py-3 rounded-2xl shadow-xl transition-all duration-300 animate-in fade-in slide-in-from-top-4 max-w-sm sm:max-w-md">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            <span class="text-xs sm:text-sm font-medium">{{ $toastMessage }}</span>
            <button wire:click="clearToast" class="hover:opacity-75 transition-opacity ml-2 cursor-pointer">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    @endif

    <!-- Header (Responsive Layout) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-[#131b2e] tracking-tight">乗り換え履歴 (MNP History)</h1>
            <p class="text-xs sm:text-sm text-[#505f76] mt-1 font-medium">これまでの携帯キャリア乗り換え変遷とコスト削減・実質収支の記録</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <button wire:click="exportCsv" 
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-xl bg-white border border-[#c7c4d8]/60 text-xs font-bold text-[#131b2e] hover:bg-[#f2f3ff] transition-all shadow-xs cursor-pointer"
                    title="乗り換え履歴をCSVダウンロード">
                <span class="material-symbols-outlined text-lg text-emerald-600">download</span>
                <span>CSV出力</span>
            </button>
            <button wire:click="openCreateModal" 
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 bg-[#3525cd] hover:bg-[#291cb0] text-white text-xs sm:text-sm font-semibold px-4 py-2 sm:py-2.5 rounded-xl shadow-md shadow-[#3525cd]/20 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-lg sm:text-xl">post_add</span>
                <span>過去の履歴を手動登録</span>
            </button>
        </div>
    </div>

    <!-- Summary Stats (Responsive: 1-col on Mobile, 3-col on Tablet/PC) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-6 shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-2 text-xs font-bold text-[#505f76]">
                <span class="material-symbols-outlined text-amber-600 text-lg sm:text-xl">savings</span>
                <span>累計年間節約効果</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-[#3525cd] tracking-tight mt-2">
                ¥{{ number_format($totalAnnualSaving) }}<span class="text-xs sm:text-sm font-bold text-[#505f76]">/年</span>
            </div>
        </div>

        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-6 shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-2 text-xs font-bold text-[#505f76]">
                <span class="material-symbols-outlined text-emerald-600 text-lg sm:text-xl">swap_calls</span>
                <span>乗り換え総回数</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-[#131b2e] tracking-tight mt-2">
                {{ $historyCount }}<span class="text-xs sm:text-sm font-bold text-[#505f76]">回</span>
            </div>
        </div>

        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-6 shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-2 text-xs font-bold text-[#505f76]">
                <span class="material-symbols-outlined text-indigo-600 text-lg sm:text-xl">trending_down</span>
                <span>1回あたりの平均月額削減</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 tracking-tight mt-2">
                -¥{{ number_format($avgMonthlySaving) }}<span class="text-xs sm:text-sm font-bold text-[#505f76]">/月</span>
            </div>
        </div>
    </div>

    <!-- Phone Number Badges (Responsive Wrap / Filter) -->
    @if (!empty($phoneCounts))
        <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-5 shadow-xs space-y-2.5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                <div class="flex items-center gap-1.5 font-bold text-[#131b2e]">
                    <span class="material-symbols-outlined text-[#3525cd] text-base">badge</span>
                    <span>電話番号別 乗り換え実績</span>
                </div>
                <span class="text-[10px] sm:text-xs text-[#777587]">バッジをクリックして絞り込み</span>
            </div>

            <div class="flex items-center flex-wrap gap-2 pt-1">
                @foreach ($phoneCounts as $pItem)
                    @php
                        $isSelected = ($search === $pItem['phone_number']);
                    @endphp
                    <button wire:click="$set('search', '{{ $isSelected ? '' : $pItem['phone_number'] }}')" 
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-all border cursor-pointer {{ $isSelected ? 'bg-[#3525cd] text-white border-[#3525cd] shadow-xs' : 'bg-[#faf8ff] text-[#131b2e] border-[#dae2fd] hover:bg-[#eaedff]' }}">
                        <span>{{ $pItem['phone_number'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $isSelected ? 'bg-white/20 text-white' : 'bg-[#eaedff] text-[#3525cd]' }}">
                            {{ $pItem['count'] }}回
                        </span>
                        @if ($pItem['annual_saving'] > 0)
                            <span class="text-[10px] {{ $isSelected ? 'text-emerald-200' : 'text-emerald-700' }}">
                                -¥{{ number_format($pItem['annual_saving']) }}/年
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search / Filter -->
    <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-3.5 sm:p-4 shadow-xs">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#777587]">
                <span class="material-symbols-outlined text-xl">search</span>
            </span>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="電話番号、回線名、キャリア名、名義人で履歴を絞り込み..."
                   class="w-full pl-10 pr-10 py-2.5 bg-[#faf8ff] border border-[#c7c4d8]/60 rounded-2xl text-xs sm:text-sm text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
            @if ($search !== '')
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#777587] hover:text-[#131b2e] cursor-pointer">
                    <span class="material-symbols-outlined text-lg">cancel</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Timeline List -->
    @if ($histories->isEmpty())
        <div class="bg-white border border-[#c7c4d8]/50 rounded-3xl p-8 sm:p-16 text-center shadow-xs">
            <div class="w-16 h-16 bg-[#eaedff] text-[#3525cd] rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">history_toggle_off</span>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#131b2e] mb-1">乗り換え履歴が見つかりませんでした</h3>
            <p class="text-xs sm:text-sm text-[#505f76] max-w-sm mx-auto mb-4">回線を他社に乗り換えるか、手動で過去の履歴を登録してください。</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($histories as $h)
                <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-6 shadow-xs relative overflow-hidden hover:border-[#c3c0ff] transition-all space-y-3 sm:space-y-4">
                    
                    <!-- Card Top Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="material-symbols-outlined text-[#3525cd] text-xl">event</span>
                            <span class="font-bold text-xs sm:text-sm text-[#131b2e]">{{ $h->transfer_date ? $h->transfer_date->format('Y年m月d日') : '-' }}</span>
                            @if ($h->phone_number)
                                <span class="bg-[#eaedff] text-[#3525cd] font-mono font-bold text-[10px] sm:text-xs px-2.5 py-0.5 rounded-full">
                                    {{ $h->phone_number }}
                                </span>
                            @endif
                            <span class="text-xs text-[#505f76] font-medium">{{ $h->line_name }}</span>
                        </div>

                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span class="text-xs font-black px-3 py-1 rounded-full {{ $h->monthly_saving >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                月額 {{ $h->monthly_saving >= 0 ? '-¥' . number_format($h->monthly_saving) : '+¥' . number_format(abs($h->monthly_saving)) }} / 年間 {{ $h->annual_saving >= 0 ? '-¥' . number_format($h->annual_saving) : '+¥' . number_format(abs($h->annual_saving)) }}
                            </span>
                            <button wire:click="deleteHistory({{ $h->id }})" 
                                    wire:confirm="この履歴を削除してもよろしいですか？"
                                    class="text-[#ba1a1a] hover:bg-[#ffdad6]/40 p-1.5 rounded-xl transition-colors cursor-pointer"
                                    title="履歴を削除">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Transfer Flow Comparison Box (Responsive: Stack on Mobile, Row on Tablet/Desktop) -->
                    <div class="bg-[#faf8ff] p-3.5 sm:p-5 rounded-2xl border border-[#dae2fd] flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 sm:gap-4">
                        <!-- From Carrier -->
                        <div class="flex-1 space-y-1">
                            <span class="text-[10px] font-bold text-rose-800 bg-rose-100 px-2 py-0.5 rounded">乗り換え元 (解約)</span>
                            <div class="font-bold text-sm sm:text-base text-[#131b2e] mt-1">{{ $h->from_carrier_name }}</div>
                            <div class="text-xs text-[#505f76]">{{ $h->from_plan_name ?? 'プラン' }}</div>
                            <div class="text-xs font-black text-[#131b2e]">
                                ¥{{ number_format($h->from_monthly_fee) }} <span class="text-[10px] font-normal text-[#505f76]">/月 ({{ number_format($h->from_data_capacity, 1) }}GB)</span>
                            </div>
                        </div>

                        <!-- Arrow Indicator -->
                        <div class="flex items-center justify-center py-1 md:py-0">
                            <div class="w-8 h-8 rounded-full bg-[#eaedff] text-[#3525cd] flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-lg hidden md:block">arrow_forward</span>
                                <span class="material-symbols-outlined text-lg block md:hidden">arrow_downward</span>
                            </div>
                            @if ($h->usage_period_text)
                                <span class="text-[10px] text-[#777587] ml-2 block md:hidden">利用期間: {{ $h->usage_period_text }}</span>
                            @endif
                        </div>

                        <!-- To Carrier -->
                        <div class="flex-1 space-y-1 text-left md:text-right">
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">乗り換え先 (新規契約)</span>
                            <div class="font-bold text-sm sm:text-base text-[#3525cd] mt-1">{{ $h->to_carrier_name }}</div>
                            <div class="text-xs text-[#505f76]">{{ $h->to_plan_name ?? 'プラン' }}</div>
                            <div class="text-xs font-black text-[#3525cd]">
                                ¥{{ number_format($h->to_monthly_fee) }} <span class="text-[10px] font-normal text-[#3525cd]/80">/月 ({{ number_format($h->to_data_capacity, 1) }}GB)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Extra Financial Details if recorded -->
                    @if ((int)$h->device_cost > 0 || (int)$h->cashback_amount > 0 || (int)$h->admin_fee > 0 || (int)$h->device_sale_profit > 0)
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#f2f3ff] text-[11px] sm:text-xs">
                            <div class="flex items-center flex-wrap gap-2 text-[#505f76]">
                                @if ((int)$h->admin_fee > 0)
                                    <span>事務手数料: <strong class="text-[#131b2e]">¥{{ number_format($h->admin_fee) }}</strong></span>
                                @endif
                                @if ((int)$h->device_cost > 0)
                                    <span>端末代: <strong class="text-[#131b2e]">¥{{ number_format($h->device_cost) }}</strong></span>
                                @endif
                                @if ((int)$h->cashback_amount > 0)
                                    <span>特典・CB: <strong class="text-emerald-700">¥{{ number_format($h->cashback_amount) }}</strong></span>
                                @endif
                                @if ((int)$h->device_sale_profit > 0)
                                    <span>売却益: <strong class="text-emerald-700">¥{{ number_format($h->device_sale_profit) }}</strong></span>
                                @endif
                            </div>
                            <div class="font-bold">
                                <span>初年度トータル実質収支: </span>
                                <strong class="{{ $h->total_net_saving >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $h->total_net_saving >= 0 ? '+¥' : '-¥' }}{{ number_format(abs($h->total_net_saving)) }}
                                </strong>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create History Modal (手動登録モーダル) -->
    @if ($isCreating)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-3xl border border-[#c7c4d8]/50 shadow-2xl max-w-2xl w-full p-4 sm:p-8 max-h-[92vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-3 border-b border-[#c7c4d8]/30 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#eaedff] text-[#3525cd] flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl sm:text-2xl">post_add</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-[#131b2e]">過去の乗り換え履歴を手動登録</h3>
                    </div>
                    <button wire:click="closeCreateModal" class="p-1 rounded-full text-[#777587] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl">close</span>
                    </button>
                </div>

                <form wire:submit="createHistory" class="space-y-4 text-xs sm:text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">回線名 (識別名) *</label>
                            <input wire:model="new_line_name" type="text" required placeholder="メイン回線" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/60">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">電話番号</label>
                            <input wire:model="new_phone_number" type="text" placeholder="090-1234-5678" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/60 font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え日 *</label>
                            <input wire:model="new_transfer_date" type="date" required class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-white">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">旧回線の利用期間 (テキスト)</label>
                            <input wire:model="new_usage_period_text" type="text" placeholder="例: 1年6ヶ月" class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/60">
                        </div>
                    </div>

                    <!-- From Carrier Section -->
                    <div class="p-3.5 bg-rose-50/50 border border-rose-100 rounded-2xl space-y-2.5">
                        <span class="font-bold text-xs text-rose-800">乗り換え元（解約）の情報</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 text-xs">
                            <div>
                                <label class="text-[#505f76] font-bold">会社名 *</label>
                                <input wire:model="new_from_carrier_name" type="text" required placeholder="au" class="w-full px-3 py-2 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                            </div>
                            <div>
                                <label class="text-[#505f76] font-bold">月額料金 (円) *</label>
                                <input wire:model="new_from_monthly_fee" type="number" required placeholder="7238" class="w-full px-3 py-2 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                            </div>
                            <div>
                                <label class="text-[#505f76] font-bold">通信容量 (GB) *</label>
                                <input wire:model="new_from_data_capacity" type="number" step="0.1" required placeholder="50.0" class="w-full px-3 py-2 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                            </div>
                        </div>
                    </div>

                    <!-- To Carrier Section -->
                    <div class="p-3.5 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-2.5">
                        <span class="font-bold text-xs text-emerald-800">乗り換え先（新規契約）の情報</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 text-xs">
                            <div>
                                <label class="text-[#505f76] font-bold">会社名 *</label>
                                <input wire:model="new_to_carrier_name" type="text" required placeholder="UQ mobile" class="w-full px-3 py-2 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                            </div>
                            <div>
                                <label class="text-[#505f76] font-bold">月額料金 (円) *</label>
                                <input wire:model="new_to_monthly_fee" type="number" required placeholder="3278" class="w-full px-3 py-2 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                            </div>
                            <div>
                                <label class="text-[#505f76] font-bold">通信容量 (GB) *</label>
                                <input wire:model="new_to_data_capacity" type="number" step="0.1" required placeholder="33.0" class="w-full px-3 py-2 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                            </div>
                        </div>
                    </div>

                    <!-- Extra Financial Simulation Options -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        <div>
                            <label class="text-[#505f76] font-bold">新端末代 (円)</label>
                            <input wire:model="new_device_cost" type="number" min="0" placeholder="0" class="w-full px-2.5 py-1.5 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                        </div>
                        <div>
                            <label class="text-emerald-800 font-bold">キャッシュバック (円)</label>
                            <input wire:model="new_cashback_amount" type="number" min="0" placeholder="0" class="w-full px-2.5 py-1.5 bg-white border border-emerald-300 rounded-xl mt-1 text-emerald-700 font-bold">
                        </div>
                        <div>
                            <label class="text-[#505f76] font-bold">事務手数料 (円)</label>
                            <input wire:model="new_admin_fee" type="number" min="0" placeholder="3850" class="w-full px-2.5 py-1.5 bg-white border border-[#c7c4d8] rounded-xl mt-1">
                        </div>
                        <div>
                            <label class="text-emerald-800 font-bold">旧端末売却益 (円)</label>
                            <input wire:model="new_device_sale_profit" type="number" min="0" placeholder="0" class="w-full px-2.5 py-1.5 bg-white border border-emerald-300 rounded-xl mt-1 text-emerald-700 font-bold">
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-[#c7c4d8]/30">
                        <button type="button" wire:click="closeCreateModal" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-[#c7c4d8] text-xs font-semibold text-[#505f76] hover:bg-[#f2f3ff] cursor-pointer">
                            キャンセル
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#3525cd] hover:bg-[#291cb0] text-xs font-bold text-white shadow-md cursor-pointer">
                            履歴を保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

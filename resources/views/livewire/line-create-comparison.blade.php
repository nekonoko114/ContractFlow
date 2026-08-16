<div class="space-y-6 sm:space-y-8 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-[#505f76] hover:text-[#3525cd] transition-colors p-1 rounded-lg">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </a>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-[#131b2e] tracking-tight">
                    {{ $from_line_id ? '回線の乗り換え（MNP）手続き' : '新規回線登録・乗り換え比較' }}
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-[#505f76] mt-1 font-medium pl-7 sm:pl-8">
                新しい携帯回線の情報を入力し、乗り換え元との月額差額や端末代・CBを含めたトータル実質収支をシミュレーションできます。
            </p>
        </div>
    </div>

    <!-- MNP Line Selector banner if transfer mode -->
    @if ($from_line_id)
        <div class="bg-gradient-to-r from-[#4f46e5]/10 to-[#d0e1fb]/30 border border-[#dae2fd] rounded-2xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#3525cd] text-white flex items-center justify-center shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-xl sm:text-2xl">swap_horiz</span>
                </div>
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-[#3525cd] uppercase tracking-wider">乗り換え対象回線</span>
                    <h3 class="font-bold text-xs sm:text-sm text-[#131b2e]">{{ $line_name }} ({{ $prev_carrier_name }}: ¥{{ number_format($prev_monthly_fee) }} / {{ $prev_data_capacity }}GB)</h3>
                </div>
            </div>
            <a href="{{ route('lines.create') }}" class="self-start sm:self-auto text-xs font-semibold text-[#505f76] hover:text-[#3525cd] bg-white px-3 py-1.5 rounded-lg border border-[#c7c4d8]/60 transition-colors">
                単独の新規登録に切り替える
            </a>
        </div>
    @endif

    <!-- Main Grid: Input Form + Live Comparison Card (Responsive: 1-col on Mobile/Tablet, 3-col on Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
        
        <!-- Left 7 or 8 Cols: Input Form -->
        <div class="lg:col-span-7 xl:col-span-8 bg-white border border-[#c7c4d8]/40 rounded-3xl p-4 sm:p-6 md:p-8 shadow-xs space-y-6 sm:space-y-8">
            <form wire:submit="save" class="space-y-6 sm:space-y-8">
                
                <!-- Section 1: Carrier & Plan Selection -->
                <div class="space-y-4 sm:space-y-5">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-[#f2f3ff]">
                        <span class="material-symbols-outlined text-[#3525cd] text-xl">cell_tower</span>
                        <h2 class="text-sm sm:text-base font-bold text-[#131b2e]">1. 携帯電話会社の選択</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Carrier Dropdown Selection -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">携帯会社名 (ドロップダウン選択) *</label>
                            <div class="relative">
                                <select wire:model.live="carrier_name" required
                                        class="w-full px-3.5 py-2.5 sm:py-3 bg-white border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm text-[#131b2e] font-semibold focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd] cursor-pointer">
                                    <option value="">-- 携帯会社を選択してください --</option>
                                    
                                    <optgroup label="主要大手キャリア (MNO)">
                                        @foreach ($carriers->where('type', 'MNO') as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }} (安全目安: {{ $c->safe_period_days ?? 180 }}日)</option>
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="サブブランド・オンライン専用">
                                        @foreach ($carriers->whereIn('type', ['サブブランド', 'オンライン専用', 'Online', 'SubBrand']) as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }} (安全目安: {{ $c->safe_period_days ?? 180 }}日)</option>
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="格安SIM (MVNO)">
                                        @foreach ($carriers->where('type', 'MVNO') as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }} (安全目安: {{ $c->safe_period_days ?? 90 }}日)</option>
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="その他">
                                        <option value="その他">その他（自由入力）</option>
                                    </optgroup>
                                </select>
                            </div>
                            @error('carrier_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Plan Name -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">料金プラン名</label>
                            <input wire:model="plan_name" type="text" placeholder="例: シンプル2 M, コミコミプラン+, ahamo 30GB"
                                   class="w-full px-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('plan_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Custom carrier name text input if "その他" is selected -->
                    @if ($carrier_name === 'その他' || ($carrier_name && !$carriers->contains('name', $carrier_name)))
                        <div class="p-3 bg-[#f2f3ff] rounded-2xl border border-[#dae2fd] space-y-1.5 animate-in fade-in duration-150">
                            <label class="text-xs font-bold text-[#3525cd]">会社名を直接入力してください</label>
                            <input wire:model.live="carrier_name" type="text" placeholder="例: 楽天Turbo, ひかり電話SIMなど"
                                   class="w-full px-3.5 py-2 bg-white border border-[#c7c4d8]/70 rounded-xl text-xs sm:text-sm text-[#131b2e]">
                        </div>
                    @endif
                </div>

                <!-- Section 2: Pricing & Capacity -->
                <div class="space-y-4 sm:space-y-5">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-[#f2f3ff]">
                        <span class="material-symbols-outlined text-[#3525cd] text-xl">payments</span>
                        <h2 class="text-sm sm:text-base font-bold text-[#131b2e]">2. 料金・データ通信容量</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Monthly Fee -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">新月額料金 (円/月・税込) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#505f76] font-bold text-xs sm:text-sm">¥</span>
                                <input wire:model.live="monthly_fee" type="number" min="0" step="1" required placeholder="2970"
                                       class="w-full pl-8 pr-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-base sm:text-lg font-black text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            </div>
                            @error('monthly_fee') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Data Capacity -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">データ通信容量 (GB) *</label>
                            <div class="relative">
                                <input wire:model.live="data_capacity" type="number" step="0.1" min="0" required placeholder="30.0"
                                       class="w-full pr-12 pl-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-base sm:text-lg font-black text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#505f76] font-bold text-xs sm:text-sm">GB</span>
                            </div>
                            @error('data_capacity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Preset Capacity Badges (Responsive) -->
                    <div class="flex items-center gap-2 flex-wrap pt-1">
                        <span class="text-[11px] font-bold text-[#777587]">容量プリセット:</span>
                        @foreach ([3, 10, 20, 30, 50, 100] as $cap)
                            <button type="button" 
                                    wire:click="$set('data_capacity', {{ $cap }})"
                                    class="px-2.5 py-1 rounded-xl text-xs font-bold border transition-colors cursor-pointer {{ (float)$data_capacity === (float)$cap ? 'bg-[#3525cd] text-white border-[#3525cd]' : 'bg-[#f2f3ff] text-[#505f76] border-[#dae2fd] hover:bg-[#eaedff]' }}">
                                {{ $cap }}GB
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Financial Simulation Options Accordion (端末代・キャッシュバック・手数料) -->
                <div x-data="{ expanded: false }" class="bg-[#faf8ff] border border-[#dae2fd] rounded-2xl p-4 space-y-3">
                    <button type="button" @click="expanded = !expanded" class="w-full flex items-center justify-between text-left cursor-pointer">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#3525cd] text-lg sm:text-xl">calculate</span>
                            <span class="text-xs sm:text-sm font-bold text-[#131b2e]">端末代・キャッシュバック・手数料を含めた実質収支を計算する</span>
                        </div>
                        <span class="material-symbols-outlined text-[#505f76] transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">expand_more</span>
                    </button>

                    <div x-show="expanded" x-transition.opacity.duration.200ms class="pt-3 border-t border-[#dae2fd] grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-xs">
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">新端末購入代金 (円・一括実質)</label>
                            <input wire:model.live="device_cost" type="number" min="0" placeholder="0" class="w-full px-3 py-2 bg-white border border-[#c7c4d8]/70 rounded-xl text-xs">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-emerald-800">特典・キャッシュバック (円)</label>
                            <input wire:model.live="cashback_amount" type="number" min="0" placeholder="0" class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs font-bold text-emerald-700">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">契約事務手数料 (円)</label>
                            <input wire:model.live="admin_fee" type="number" min="0" placeholder="3850" class="w-full px-3 py-2 bg-white border border-[#c7c4d8]/70 rounded-xl text-xs">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-emerald-800">旧端末売却益・下取り (円)</label>
                            <input wire:model.live="device_sale_profit" type="number" min="0" placeholder="0" class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs font-bold text-emerald-700">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Line Details & Security -->
                <div class="space-y-4 sm:space-y-5">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-[#f2f3ff]">
                        <span class="material-symbols-outlined text-[#3525cd] text-xl">badge</span>
                        <h2 class="text-sm sm:text-base font-bold text-[#131b2e]">3. 回線・契約者情報 & セキュリティ</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-[#464555]">回線識別名 *</label>
                            <input wire:model="line_name" type="text" placeholder="例: メイン回線 (Pixel 8), 仕事用iPad" required
                                   class="w-full px-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('line_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">電話番号</label>
                            <input wire:model="phone_number" type="text" placeholder="090-1234-5678"
                                   class="w-full px-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm font-mono text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('phone_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">契約開始日 (開通日) *</label>
                            <input wire:model="contract_start_date" type="date" required
                                   class="w-full px-3.5 py-2.5 sm:py-3 bg-white border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('contract_start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">契約名義人 *</label>
                            <input wire:model="contract_holder" type="text" required placeholder="山田 太郎"
                                   class="w-full px-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('contract_holder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#464555]">実際の使用者 (利用者) *</label>
                            <input wire:model="actual_user" type="text" required placeholder="山田 太郎"
                                   class="w-full px-3.5 py-2.5 sm:py-3 bg-[#faf8ff] border border-[#c7c4d8]/70 rounded-2xl text-xs sm:text-sm text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('actual_user') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Network PIN (Hidden with Toggle) -->
                    <div x-data="{ showPin: false }" class="bg-[#faf8ff] p-3.5 sm:p-4 rounded-2xl border border-[#dae2fd] space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[#131b2e] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#3525cd]">lock</span>
                                <span>ネットワーク暗証番号 (PIN)</span>
                            </label>
                            <span class="text-[10px] text-[#777587]">暗号化・セキュリティ保護</span>
                        </div>
                        <div class="relative">
                            <input :type="showPin ? 'text' : 'password'" 
                                   wire:model="network_pin" 
                                   placeholder="ご契約時の4桁の数字など"
                                   class="w-full pl-3.5 pr-10 py-2.5 sm:py-3 bg-white border border-[#c7c4d8]/70 rounded-xl text-xs sm:text-sm font-mono tracking-widest text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            <button type="button" @click="showPin = !showPin" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#777587] hover:text-[#3525cd] transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]" x-text="showPin ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-[#f2f3ff]">
                    <a href="{{ route('dashboard') }}" class="text-center px-6 py-3 rounded-2xl border border-[#c7c4d8] text-xs sm:text-sm font-bold text-[#505f76] hover:bg-[#f2f3ff] transition-colors">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 bg-[#3525cd] hover:bg-[#291cb0] text-white text-xs sm:text-sm font-bold px-8 py-3.5 rounded-2xl shadow-lg shadow-[#3525cd]/25 active:scale-98 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl">save</span>
                        <span>{{ $from_line_id ? '乗り換えを完了して保存' : '回線情報を登録する' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right 5 or 4 Cols: Live Comparison & Simulation Card (Sticky on Desktop) -->
        <div class="lg:col-span-5 xl:col-span-4 space-y-4 sm:space-y-6 lg:sticky lg:top-24">
            <div class="bg-white border border-[#c7c4d8]/40 rounded-3xl p-5 sm:p-6 shadow-xs space-y-4 sm:space-y-6 relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-[#f2f3ff]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#3525cd] text-xl">analytics</span>
                        <h2 class="text-sm sm:text-base font-bold text-[#131b2e]">乗り換えシミュレーター</h2>
                    </div>
                    <span class="text-[10px] font-bold bg-[#eaedff] text-[#3525cd] px-2 py-0.5 rounded-full">リアルタイム計算</span>
                </div>

                <!-- Before vs After Compact Table -->
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-[#faf8ff] p-3 rounded-2xl border border-[#dae2fd]/60">
                            <span class="text-[10px] font-bold text-[#777587] uppercase tracking-wider">現在 (乗り換え元)</span>
                            <div class="font-bold text-xs sm:text-sm text-[#131b2e] mt-1 truncate">{{ $prev_carrier_name ?? '（新規）' }}</div>
                            <div class="text-sm sm:text-base font-black text-[#131b2e] mt-0.5">¥{{ number_format((int)$prev_monthly_fee) }}</div>
                            <div class="text-[10px] text-[#505f76] mt-0.5">{{ number_format((float)$prev_data_capacity, 1) }} GB</div>
                        </div>

                        <div class="bg-[#eaedff]/60 p-3 rounded-2xl border border-[#c3c0ff]">
                            <span class="text-[10px] font-bold text-[#3525cd] uppercase tracking-wider">乗り換え後 (新規契約)</span>
                            <div class="font-bold text-xs sm:text-sm text-[#3525cd] mt-1 truncate">{{ $carrier_name ?: '携帯会社' }}</div>
                            <div class="text-sm sm:text-base font-black text-[#3525cd] mt-0.5">¥{{ number_format((int)$monthly_fee) }}</div>
                            <div class="text-[10px] text-[#3525cd] mt-0.5">{{ number_format((float)$data_capacity, 1) }} GB</div>
                        </div>
                    </div>

                    <!-- Monthly Difference -->
                    <div class="bg-[#faf8ff] p-3.5 sm:p-4 rounded-2xl border border-[#dae2fd] space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-[#505f76]">月額料金の差額:</span>
                            <span class="font-black text-sm sm:text-base {{ $monthlyDiff > 0 ? 'text-emerald-600' : ($monthlyDiff < 0 ? 'text-rose-600' : 'text-[#131b2e]') }}">
                                {{ $monthlyDiff > 0 ? '-¥' . number_format($monthlyDiff) : ($monthlyDiff < 0 ? '+¥' . number_format(abs($monthlyDiff)) : '±¥0') }} /月
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-[#505f76]">年間コスト削減見込み:</span>
                            <span class="font-black text-sm sm:text-base {{ $annualDiff > 0 ? 'text-emerald-700 font-black' : 'text-[#505f76]' }}">
                                {{ $annualDiff > 0 ? '-¥' . number_format($annualDiff) : '¥0' }} /年
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-1 border-t border-[#dae2fd]">
                            <span class="font-semibold text-[#505f76]">データ容量差:</span>
                            <span class="font-bold text-xs {{ $dataDiff > 0 ? 'text-indigo-600' : ($dataDiff < 0 ? 'text-rose-600' : 'text-[#131b2e]') }}">
                                {{ $dataDiff > 0 ? '+' : '' }}{{ number_format($dataDiff, 1) }} GB
                            </span>
                        </div>
                    </div>

                    <!-- Total Financial Net Saving (初年度トータル実質収支) -->
                    @if ((int)$device_cost > 0 || (int)$cashback_amount > 0 || (int)$admin_fee > 0 || (int)$device_sale_profit > 0)
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 p-3.5 sm:p-4 rounded-2xl border border-emerald-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-900">初年度トータル実質収支</span>
                                <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">端末/CB/手数料込</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-1">
                                <span class="text-xs text-[#505f76]">実質トータル節約額:</span>
                                <span class="text-lg sm:text-xl font-black {{ $totalNetDiff >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $totalNetDiff >= 0 ? '+¥' . number_format($totalNetDiff) : '-¥' . number_format(abs($totalNetDiff)) }}
                                </span>
                            </div>
                            <p class="text-[10px] text-emerald-800/80 leading-relaxed">
                                ※年間月額差額 ({{ number_format($annualDiff) }}円) ＋ 特典 ({{ number_format((int)$cashback_amount) }}円) ＋ 売却益 ({{ number_format((int)$device_sale_profit) }}円) － 端末代 ({{ number_format((int)$device_cost) }}円) － 手数料 ({{ number_format((int)$admin_fee) }}円)
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Safe Period Guidance (BL対策) -->
                <div class="bg-[#faf8ff] p-3.5 sm:p-4 rounded-2xl border border-[#dae2fd] space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#131b2e]">
                        <span class="material-symbols-outlined text-[#3525cd] text-base">verified_user</span>
                        <span>解約・転出の推奨安全維持日数</span>
                    </div>
                    <p class="text-[11px] text-[#505f76] leading-relaxed">
                        {{ $carrier_name ?: '選択した携帯会社' }}の推奨安全維持期間は約 <strong class="text-[#3525cd]">{{ $recommendedSafeDays ?? 180 }}日間</strong> です。短期解約によるブラックリスト登録を避けるため、この期間の維持をおすすめします。
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="space-y-8 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-[#505f76] hover:text-[#3525cd] transition-colors p-1 rounded-lg">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-[#131b2e] tracking-tight">
                    {{ $from_line_id ? '回線の乗り換え（MNP）手続き' : '新規回線登録・乗り換え比較' }}
                </h1>
            </div>
            <p class="text-sm text-[#505f76] mt-1 font-medium pl-8">
                新しい携帯回線の情報を入力し、乗り換え元との月額差額や端末代・CBを含めたトータル実質収支をシミュレーションできます。
            </p>
        </div>
    </div>

    <!-- MNP Line Selector banner if transfer mode -->
    @if ($from_line_id)
        <div class="bg-gradient-to-r from-[#4f46e5]/10 to-[#d0e1fb]/30 border border-[#dae2fd] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#3525cd] text-white flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-2xl">swap_horiz</span>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#3525cd] uppercase tracking-wider">乗り換え対象回線</span>
                    <h3 class="font-bold text-sm text-[#131b2e]">{{ $line_name }} ({{ $prev_carrier_name }}: ¥{{ number_format($prev_monthly_fee) }} / {{ $prev_data_capacity }}GB)</h3>
                </div>
            </div>
            <a href="{{ route('lines.create') }}" class="text-xs font-semibold text-[#505f76] hover:text-[#3525cd] bg-white px-3 py-1.5 rounded-lg border border-[#c7c4d8]/60 transition-colors">
                新規登録に切り替え
            </a>
        </div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Columns: Contract Details & Identity -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Section 1: New Contract Details -->
                <div class="bg-white rounded-3xl border border-[#c7c4d8]/40 p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-[#c7c4d8]/20">
                        <h2 class="text-base font-bold text-[#131b2e] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#3525cd] text-2xl">sim_card</span>
                            <span>新しい契約情報 (New Contract)</span>
                        </h2>
                        <span class="text-xs font-medium text-[#3525cd] bg-[#eaedff] px-2.5 py-1 rounded-full">乗り換え先</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Line Name -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-xs font-bold text-[#464555]">回線識別名 *</label>
                            <input wire:model="line_name" type="text" required placeholder="例: メイン回線 (iPhone 15), 仕事用スマホ"
                                   class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('line_name') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#464555]">電話番号</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#777587]">
                                    <span class="material-symbols-outlined text-[18px]">phone</span>
                                </span>
                                <input wire:model="phone_number" type="text" placeholder="090-1234-5678"
                                       class="w-full pl-9 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            </div>
                        </div>

                        <!-- Carrier Selection -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#464555]">携帯会社名 (キャリア) *</label>
                            <div class="relative">
                                <select wire:model.live="carrier_name" required
                                        class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-white text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                                    <option value="">キャリアを選択</option>
                                    @foreach ($carriers as $carrier)
                                        <option value="{{ $carrier->name }}">{{ $carrier->name }} ({{ $carrier->type }})</option>
                                    @endforeach
                                    <option value="その他">その他（自由入力）</option>
                                </select>
                            </div>
                            @error('carrier_name') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Plan Name -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#464555]">料金プラン名</label>
                            <input wire:model="plan_name" type="text" placeholder="例: ahamo 30GB, コミコミプラン+"
                                   class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                        </div>

                        <!-- Monthly Fee -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#464555]">月額料金 (円) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#777587] font-bold text-xs">¥</span>
                                <input wire:model.live="monthly_fee" type="number" min="0" required placeholder="2970"
                                       class="w-full pl-8 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e] font-bold focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            </div>
                            @error('monthly_fee') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Data Capacity Presets -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-xs font-bold text-[#464555]">通信容量 (GB) *</label>
                            <div class="grid grid-cols-4 gap-2 mb-2">
                                <button type="button" wire:click="selectDataCapacity(3.0)" 
                                        class="py-2.5 px-3 rounded-xl border text-center transition-all text-xs {{ $data_capacity == 3.0 ? 'bg-[#3525cd] text-white border-[#3525cd] font-bold shadow-xs' : 'bg-[#faf8ff] border-[#c7c4d8]/60 text-[#131b2e] hover:bg-[#eaedff]' }}">
                                    <div class="font-bold text-sm">~3GB</div>
                                    <div class="text-[10px] opacity-80">ライト</div>
                                </button>
                                <button type="button" wire:click="selectDataCapacity(20.0)" 
                                        class="py-2.5 px-3 rounded-xl border text-center transition-all text-xs {{ $data_capacity == 20.0 ? 'bg-[#3525cd] text-white border-[#3525cd] font-bold shadow-xs' : 'bg-[#faf8ff] border-[#c7c4d8]/60 text-[#131b2e] hover:bg-[#eaedff]' }}">
                                    <div class="font-bold text-sm">20GB</div>
                                    <div class="text-[10px] opacity-80">標準</div>
                                </button>
                                <button type="button" wire:click="selectDataCapacity(30.0)" 
                                        class="py-2.5 px-3 rounded-xl border text-center transition-all text-xs {{ $data_capacity == 30.0 ? 'bg-[#3525cd] text-white border-[#3525cd] font-bold shadow-xs' : 'bg-[#faf8ff] border-[#c7c4d8]/60 text-[#131b2e] hover:bg-[#eaedff]' }}">
                                    <div class="font-bold text-sm">30GB</div>
                                    <div class="text-[10px] opacity-80">中大容量</div>
                                </button>
                                <button type="button" wire:click="selectDataCapacity(100.0)" 
                                        class="py-2.5 px-3 rounded-xl border text-center transition-all text-xs {{ $data_capacity == 100.0 ? 'bg-[#3525cd] text-white border-[#3525cd] font-bold shadow-xs' : 'bg-[#faf8ff] border-[#c7c4d8]/60 text-[#131b2e] hover:bg-[#eaedff]' }}">
                                    <div class="font-bold text-sm">無制限</div>
                                    <div class="text-[10px] opacity-80">大盛り/MAX</div>
                                </button>
                            </div>
                            <div class="relative">
                                <input wire:model.live="data_capacity" type="number" step="0.1" min="0" required
                                       class="w-full pr-10 pl-3.5 py-2 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e]">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-[#777587]">GB</span>
                            </div>
                            @error('data_capacity') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Contract Start Date -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-xs font-bold text-[#464555]">利用開始日 (乗り換え日) *</label>
                            <input wire:model="contract_start_date" type="date" required
                                   class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-white text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            @error('contract_start_date') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Financial Benefits & Terminal Options (端末代・特典オプション) -->
                <div class="bg-white rounded-3xl border border-[#c7c4d8]/40 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#c7c4d8]/20">
                        <h2 class="text-base font-bold text-[#131b2e] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#3525cd] text-2xl">redeem</span>
                            <span>端末代・還元特典・手数料 (実質収支オプション)</span>
                        </h2>
                        <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">収支シミュレーション</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <!-- Device Cost -->
                        <div class="space-y-1.5">
                            <label class="font-bold text-[#464555]">新端末購入費用 (円)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#777587] font-bold">¥</span>
                                <input wire:model.live="device_cost" type="number" min="0" placeholder="例: 1 (一括1円), 48000"
                                       class="w-full pl-7 pr-3 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-[#131b2e] font-bold focus:ring-2 focus:ring-[#3525cd]/20">
                            </div>
                        </div>

                        <!-- Cashback / Points -->
                        <div class="space-y-1.5">
                            <label class="font-bold text-[#464555]">キャッシュバック・ポイント還元 (円相当)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#777587] font-bold">¥</span>
                                <input wire:model.live="cashback_amount" type="number" min="0" placeholder="例: 20000"
                                       class="w-full pl-7 pr-3 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-emerald-700 font-bold focus:ring-2 focus:ring-[#3525cd]/20">
                            </div>
                        </div>

                        <!-- Admin Fee -->
                        <div class="space-y-1.5">
                            <label class="font-bold text-[#464555]">契約事務手数料等 (円)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#777587] font-bold">¥</span>
                                <input wire:model.live="admin_fee" type="number" min="0" placeholder="3850"
                                       class="w-full pl-7 pr-3 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-[#131b2e] font-bold focus:ring-2 focus:ring-[#3525cd]/20">
                            </div>
                        </div>

                        <!-- Device Sale Profit -->
                        <div class="space-y-1.5">
                            <label class="font-bold text-[#464555]">旧端末売却益・下取り額 (円)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#777587] font-bold">¥</span>
                                <input wire:model.live="device_sale_profit" type="number" min="0" placeholder="例: 35000"
                                       class="w-full pl-7 pr-3 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-emerald-700 font-bold focus:ring-2 focus:ring-[#3525cd]/20">
                            </div>
                        </div>

                        <!-- Custom Safe Period Days -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="font-bold text-[#464555]">目標安全維持日数 (短期解約防止目安)</label>
                            <input wire:model="custom_safe_period_days" type="number" min="0" placeholder="標準: {{ $recommendedSafeDays }}日 (空欄で標準値)"
                                   class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-[#131b2e] focus:ring-2 focus:ring-[#3525cd]/20">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Identity & Security -->
                <div class="bg-white rounded-3xl border border-[#c7c4d8]/40 p-6 shadow-xs space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#c7c4d8]/20">
                        <span class="material-symbols-outlined text-[#3525cd] text-2xl">badge</span>
                        <h2 class="text-base font-bold text-[#131b2e]">名義人・使用者・暗証番号 (Identity & Security)</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Contract Holder -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#464555]">契約名義人 *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#777587]">
                                    <span class="material-symbols-outlined text-[18px]">person</span>
                                </span>
                                <input wire:model="contract_holder" type="text" required placeholder="山田 太郎"
                                       class="w-full pl-9 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            </div>
                            @error('contract_holder') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Actual User -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#464555]">使用者 (契約者と異なる場合も明記) *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#777587]">
                                    <span class="material-symbols-outlined text-[18px]">account_box</span>
                                </span>
                                <input wire:model="actual_user" type="text" required placeholder="山田 太郎 または 山田 花子"
                                       class="w-full pl-9 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                            </div>
                            @error('actual_user') <span class="text-xs text-[#ba1a1a]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Security PIN with masking toggle -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-xs font-bold text-[#464555]">
                                ネットワーク暗証番号 (PIN) 
                                <span class="text-[11px] font-normal text-[#505f76]">※ 暗号化して厳重に保護されます</span>
                            </label>
                            <div class="relative max-w-md">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#777587]">
                                    <span class="material-symbols-outlined text-[18px]">lock</span>
                                </span>
                                <input wire:model="network_pin" 
                                       type="{{ $showPin ? 'text' : 'password' }}" 
                                       maxlength="8" 
                                       placeholder="4桁の数字など"
                                       class="w-full pl-9 pr-10 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm font-mono tracking-widest bg-[#faf8ff]/40 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd]">
                                <button type="button" wire:click="togglePin" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#777587] hover:text-[#3525cd] transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">
                                        {{ $showPin ? 'visibility_off' : 'visibility' }}
                                    </span>
                                </button>
                            </div>
                            <p class="text-[11px] text-[#505f76]">回線の変更手続きや問い合わせ時に必要な4桁等の暗証番号です。</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-3xl border border-[#c7c4d8]/40 p-6 shadow-xs space-y-3">
                    <label class="block text-xs font-bold text-[#464555]">メモ・特記事項</label>
                    <textarea wire:model="notes" rows="2" placeholder="通話オプションやキャンペーン、解約月などのメモ"
                              class="w-full px-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl text-sm bg-[#faf8ff]/40 text-[#131b2e]"></textarea>
                </div>
            </div>

            <!-- Right Column: Comparison Simulator & Submit -->
            <div class="space-y-6">
                <!-- Comparison Card -->
                <div class="bg-white rounded-3xl border-2 border-[#505f76]/30 p-6 shadow-sm space-y-5 relative overflow-hidden">
                    <div class="flex items-center justify-between pb-3 border-b border-[#c7c4d8]/20">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#505f76] text-2xl">compare_arrows</span>
                            <h2 class="text-base font-bold text-[#131b2e]">乗り換え元と比較</h2>
                        </div>
                        <span class="text-xs font-medium text-[#505f76] bg-[#f2f3ff] px-2.5 py-1 rounded-full">旧プラン</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <!-- Previous Carrier Name -->
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え元キャリア名</label>
                            <input wire:model.live="prev_carrier_name" type="text" placeholder="例: NTTドコモ, au, SoftBank"
                                   class="w-full px-3 py-2 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-[#131b2e] focus:border-[#505f76]">
                        </div>

                        <!-- Previous Monthly Fee -->
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え前の月額料金 (円)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#777587] font-bold">¥</span>
                                <input wire:model.live="prev_monthly_fee" type="number" min="0" placeholder="8500"
                                       class="w-full pl-7 pr-3 py-2 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-[#131b2e] font-bold focus:border-[#505f76]">
                            </div>
                        </div>

                        <!-- Previous Data Capacity -->
                        <div class="space-y-1">
                            <label class="font-bold text-[#464555]">乗り換え前の通信容量 (GB)</label>
                            <div class="relative">
                                <input wire:model.live="prev_data_capacity" type="number" step="0.1" min="0" placeholder="60"
                                       class="w-full pr-8 pl-3 py-2 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/40 text-[#131b2e]">
                                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#777587] font-bold">GB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Simulation Results (リアルタイムシミュレーション結果) -->
                    <div class="pt-4 border-t border-[#c7c4d8]/30 space-y-3">
                        <h3 class="text-xs font-bold text-[#505f76] uppercase tracking-wider">シミュレーション結果</h3>
                        
                        <!-- Monthly Difference -->
                        <div class="bg-[#faf8ff] p-3.5 rounded-2xl border border-[#dae2fd] flex items-center justify-between">
                            <span class="text-xs text-[#505f76]">月額差額:</span>
                            <div class="text-right">
                                <span class="text-base font-black {{ $monthlyDiff >= 0 ? 'text-emerald-700' : 'text-[#ba1a1a]' }}">
                                    {{ $monthlyDiff >= 0 ? '-' : '+' }}¥{{ number_format(abs($monthlyDiff)) }}
                                </span>
                                <span class="text-[10px] text-[#505f76]">/月</span>
                            </div>
                        </div>

                        <!-- Annual Monthly Saving -->
                        <div class="bg-[#faf8ff] p-3.5 rounded-2xl border border-[#dae2fd] flex items-center justify-between">
                            <span class="text-xs text-[#505f76]">月額削減の年間換算:</span>
                            <div class="text-right">
                                <span class="text-lg font-black {{ $annualDiff >= 0 ? 'text-emerald-700' : 'text-[#ba1a1a]' }}">
                                    {{ $annualDiff >= 0 ? '-' : '+' }}¥{{ number_format(abs($annualDiff)) }}
                                </span>
                                <span class="text-[10px] text-[#505f76]">/年</span>
                            </div>
                        </div>

                        <!-- Total Net Saving (端末代・CB・手数料込みのトータル実質収支) -->
                        <div class="bg-gradient-to-br from-emerald-500/10 to-teal-500/20 p-4 rounded-2xl border border-emerald-300 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-900">初年度トータル実質収支:</span>
                                <span class="text-xl font-black text-emerald-800">
                                    {{ $totalNetDiff >= 0 ? '+' : '-' }}¥{{ number_format(abs($totalNetDiff)) }}
                                </span>
                            </div>
                            <p class="text-[10px] text-emerald-700 leading-relaxed">
                                ※ 月額削減額×12 + CB/下取り - 端末代 - 手数料を合算した初年度の手元損益
                            </p>
                        </div>

                        <!-- Recommended Safe Days Badge -->
                        <div class="bg-[#f8fafc] p-3 rounded-xl border border-slate-200 text-xs flex items-center justify-between">
                            <span class="text-[#505f76]">推奨最低維持期間:</span>
                            <span class="font-bold text-[#131b2e]">{{ $recommendedSafeDays }}日 (BL対策)</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Area -->
                <div class="bg-white rounded-3xl border border-[#c7c4d8]/40 p-6 shadow-xs space-y-3">
                    <button type="submit"
                            class="w-full bg-[#3525cd] hover:bg-[#291cb0] text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-[#3525cd]/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        <span>{{ $from_line_id ? '乗り換えを完了して履歴に保存' : '回線情報を登録する' }}</span>
                    </button>
                    <a href="{{ route('dashboard') }}" 
                       class="block w-full text-center py-2.5 text-xs font-semibold text-[#505f76] hover:text-[#131b2e] transition-colors">
                        キャンセルしてダッシュボードに戻る
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

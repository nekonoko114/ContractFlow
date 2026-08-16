<div class="w-full max-w-md">
    <!-- Ambient Background Graphic -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-1/4 -right-1/4 w-3/4 h-3/4 bg-[#4f46e5]/10 rounded-full blur-3xl mix-blend-multiply"></div>
        <div class="absolute -bottom-1/4 -left-1/4 w-3/4 h-3/4 bg-[#d0e1fb]/25 rounded-full blur-3xl mix-blend-multiply"></div>
    </div>

    <!-- Brand Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#3525cd] to-[#4f46e5] text-white shadow-lg mb-3">
            <span class="material-symbols-outlined text-3xl">phonelink_setup</span>
        </div>
        <h1 class="text-3xl font-black text-[#3525cd] tracking-tight">ContractFlow</h1>
        <p class="text-sm font-medium text-[#505f76] mt-1">携帯回線・乗り換え管理システム</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-2xl border border-[#c7c4d8]/40 p-8 shadow-xl shadow-[#3525cd]/5">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-[#131b2e]">ログイン</h2>
            <p class="text-xs text-[#505f76] mt-1">アカウント情報を入力してダッシュボードへアクセス</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            <!-- Email Input -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-[#464555]" for="email">メールアドレス</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#777587]">
                        <span class="material-symbols-outlined text-[20px]">mail</span>
                    </span>
                    <input wire:model="email" 
                           id="email" 
                           type="email" 
                           required 
                           placeholder="name@example.com"
                           class="block w-full pl-10 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/50 text-[#131b2e] text-sm focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd] transition-all duration-200 @error('email') border-red-500 @enderror">
                </div>
                @error('email')
                    <span class="text-xs text-[#ba1a1a] font-medium block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-[#464555]" for="password">パスワード</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#777587]">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </span>
                    <input wire:model="password" 
                           id="password" 
                           type="password" 
                           required 
                           placeholder="••••••••"
                           class="block w-full pl-10 pr-3.5 py-2.5 border border-[#c7c4d8]/70 rounded-xl bg-[#faf8ff]/50 text-[#131b2e] text-sm focus:outline-none focus:ring-2 focus:ring-[#3525cd]/20 focus:border-[#3525cd] transition-all duration-200 @error('password') border-red-500 @enderror">
                </div>
                @error('password')
                    <span class="text-xs text-[#ba1a1a] font-medium block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded text-[#3525cd] focus:ring-[#3525cd] border-[#c7c4d8]">
                    <span class="text-xs font-medium text-[#505f76]">ログイン状態を保持</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-[#3525cd] hover:bg-[#291cb0] shadow-md shadow-[#3525cd]/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                    <span wire:loading.remove wire:target="login">ログイン</span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <span class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                        <span>認証中...</span>
                    </span>
                </button>
            </div>
        </form>

        <!-- Demo Account Info Box -->
        <div class="mt-6 p-3.5 bg-[#f2f3ff] rounded-xl border border-[#dae2fd] text-xs text-[#505f76]">
            <p class="font-bold text-[#3525cd] mb-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">info</span>
                <span>デモ用アカウント（入力済み）</span>
            </p>
            <p>メール: <code class="bg-white px-1.5 py-0.5 rounded border border-[#dae2fd] text-[#131b2e]">demo@example.com</code></p>
            <p class="mt-1">パスワード: <code class="bg-white px-1.5 py-0.5 rounded border border-[#dae2fd] text-[#131b2e]">password123</code></p>
        </div>
    </div>

    <!-- Register Link -->
    <div class="mt-6 text-center">
        <p class="text-xs text-[#505f76]">
            アカウントをお持ちでないですか？ 
            <a href="{{ route('register') }}" class="font-semibold text-[#3525cd] hover:underline">新規アカウント作成</a>
        </p>
    </div>
</div>

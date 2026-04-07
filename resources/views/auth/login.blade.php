<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | 営農調査システム</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-green-50 via-white to-white">
    <main class="mx-auto grid min-h-screen w-full max-w-5xl grid-cols-1 items-center gap-8 px-4 py-8 md:grid-cols-2">
        <section class="hidden rounded-2xl border border-green-100 bg-white/80 p-8 shadow-sm backdrop-blur md:block">
            <p class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">JA向け業務システム</p>
            <h1 class="mt-4 text-3xl font-bold text-gray-900">営農調査・位置情報管理</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">
                現地調査の記録、位置情報の収集、写真管理、Excel出力までを
                一貫してサポートします。
            </p>
            <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg border border-gray-200 bg-white p-3 text-gray-700">GPS自動取得</div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 text-gray-700">画像最適化保存</div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 text-gray-700">認証保護アクセス</div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 text-gray-700">日次Excel出力</div>
            </div>
        </section>

        <section class="w-full rounded-2xl border border-green-100 bg-white p-6 shadow-lg md:p-7">
            <h2 class="text-xl font-bold text-gray-900">ログイン</h2>
            <p class="mt-1 text-sm text-gray-600">登録済みアカウントでサインインしてください。</p>

            @if (session('status'))
                <div class="mt-4 rounded-md border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@example.com"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
                    <div class="relative mt-1">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-md border-gray-300 pr-20 text-sm shadow-sm focus:border-green-500 focus:ring-green-500"
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-500 hover:text-gray-700"
                        >
                            表示
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label for="remember" class="flex items-center gap-2 text-sm text-gray-600">
                    <input id="remember" type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    ログイン状態を保持する
                </label>

                <button
                    type="submit"
                    class="w-full rounded-md bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800"
                >
                    ログイン
                </button>

                <div class="flex items-center justify-between text-sm">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-green-700 hover:underline">パスワードを忘れた場合</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-gray-600 hover:underline">新規登録</a>
                    @endif
                </div>
            </form>
        </section>
    </main>

    <script>
        const togglePasswordButton = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');

        togglePasswordButton?.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            togglePasswordButton.textContent = isHidden ? '非表示' : '表示';
        });
    </script>
</body>
</html>

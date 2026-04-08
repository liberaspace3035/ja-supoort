<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>営農調査登録</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-green-50 via-white to-white">
<main class="mx-auto max-w-5xl py-10 px-4">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <p class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">営農管理サポート</p>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">営農調査登録</h1>
            <p class="mt-1 text-sm text-gray-600">現地調査情報を記録し、位置情報付きで安全に保管します。</p>
        </div>
        <a href="{{ route('surveys.export') }}" class="hidden sm:inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            Excelダウンロード
        </a>
    </div>

    <div class="rounded-2xl border border-green-100 bg-white p-6 shadow-lg">

        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('surveys.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="rounded-xl border border-gray-200 p-4">
                <h2 class="text-sm font-semibold text-gray-800">基本情報</h2>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="farmer_name" class="block text-sm font-medium text-gray-700">生産者名</label>
                    <input id="farmer_name" name="farmer_name" value="{{ old('farmer_name') }}" required class="mt-1 w-full rounded border-gray-300">
                </div>
                <div>
                    <label for="variety_name" class="block text-sm font-medium text-gray-700">品種名</label>
                    <input id="variety_name" name="variety_name" value="{{ old('variety_name') }}" required class="mt-1 w-full rounded border-gray-300">
                </div>
                <div>
                    <label for="survey_date" class="block text-sm font-medium text-gray-700">調査日</label>
                    <input id="survey_date" type="date" name="survey_date" value="{{ old('survey_date', now()->toDateString()) }}" required class="mt-1 w-full rounded border-gray-300">
                </div>
                <div>
                    <label for="temperature" class="block text-sm font-medium text-gray-700">気温 (0.0〜50.0)</label>
                    <input id="temperature" type="number" step="0.1" min="0" max="50" name="temperature" value="{{ old('temperature') }}" required class="mt-1 w-full rounded border-gray-300">
                </div>
            </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h2 class="text-sm font-semibold text-gray-800">生育情報</h2>
                <label for="growth_status" class="block text-sm font-medium text-gray-700">生育状況</label>
                <textarea id="growth_status" name="growth_status" rows="4" required class="mt-1 w-full rounded border-gray-300">{{ old('growth_status') }}</textarea>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-sm font-semibold text-gray-800">位置情報と写真</p>
                <p id="geo-status" class="mt-1 text-sm text-gray-600">GPS情報を取得中です...</p>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude_display" class="block text-sm text-gray-700">緯度</label>
                        <input id="latitude_display" type="text" readonly class="mt-1 w-full rounded border-gray-300 bg-gray-50 text-gray-700">
                    </div>
                    <div>
                        <label for="longitude_display" class="block text-sm text-gray-700">経度</label>
                        <input id="longitude_display" type="text" readonly class="mt-1 w-full rounded border-gray-300 bg-gray-50 text-gray-700">
                    </div>
                </div>

                <div class="mt-4">
                    <label for="photos" class="block text-sm font-medium text-gray-700">調査写真 (最大10枚)</label>
                    <input id="photos" type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required class="mt-1 block w-full text-sm text-gray-800">
                    <p class="mt-1 text-xs text-gray-500">対応形式: JPEG / PNG / WebP（1枚10MBまで、HEICは非対応）</p>
                    <p id="photo-error" class="mt-1 text-xs text-red-600"></p>
                    <div id="photo-preview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3"></div>
                </div>
            </div>

            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

            <div class="flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                <button id="submit-button" type="submit" disabled class="inline-flex h-11 min-w-28 items-center justify-center rounded-md border border-blue-700 bg-blue-700 px-5 text-base font-semibold text-white shadow-sm hover:bg-blue-800 disabled:border-gray-400 disabled:bg-gray-100 disabled:text-gray-500">
                    保存
                </button>
                <a href="{{ route('surveys.export') }}" class="inline-flex h-11 items-center rounded-md bg-emerald-600 px-5 text-base font-semibold text-white shadow-sm hover:bg-emerald-700 sm:hidden">
                    Excelダウンロード
                </a>
            </div>
        </form>
    </div>
</main>

<script>
    const submitButton = document.getElementById('submit-button');
    const geoStatus = document.getElementById('geo-status');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const latitudeDisplay = document.getElementById('latitude_display');
    const longitudeDisplay = document.getElementById('longitude_display');
    const photoInput = document.getElementById('photos');
    const photoError = document.getElementById('photo-error');
    const previewArea = document.getElementById('photo-preview');
    const supportedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxPhotoSizeBytes = 10 * 1024 * 1024;
    const maxTotalSizeBytes = 100 * 1024 * 1024;

    function enableSubmitWithLocation(lat, lng) {
        const latValue = Number(lat).toFixed(8);
        const lngValue = Number(lng).toFixed(8);
        latitudeInput.value = latValue;
        longitudeInput.value = lngValue;
        latitudeDisplay.value = latValue;
        longitudeDisplay.value = lngValue;
        submitButton.disabled = false;
        geoStatus.textContent = `GPS取得完了: ${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
    }

    function fetchLocation() {
        if (!navigator.geolocation) {
            geoStatus.textContent = 'このブラウザは位置情報取得に対応していません。';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => enableSubmitWithLocation(position.coords.latitude, position.coords.longitude),
            (error) => {
                geoStatus.textContent = `GPS取得に失敗しました: ${error.message}`;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    function renderPreview(files) {
        previewArea.innerHTML = '';
        [...files].slice(0, 10).forEach((file) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded border bg-white p-2';
                wrapper.innerHTML = `
                    <img src="${event.target.result}" alt="${file.name}" class="h-28 w-full object-cover rounded">
                    <p class="mt-1 text-xs text-gray-600 truncate">${file.name}</p>
                `;
                previewArea.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    function validatePhotos(files) {
        const picked = [...files];
        const unsupported = picked.find((file) => !supportedTypes.includes(file.type));
        const tooLarge = picked.find((file) => file.size > maxPhotoSizeBytes);
        const totalSize = picked.reduce((sum, file) => sum + file.size, 0);

        if (unsupported) {
            photoInput.value = '';
            previewArea.innerHTML = '';
            photoError.textContent = 'HEICは非対応です。iPhoneは「互換性優先（JPEG）」で撮影してください。';
            return false;
        }

        if (tooLarge) {
            photoInput.value = '';
            previewArea.innerHTML = '';
            photoError.textContent = '1枚10MB以下の写真を選択してください。';
            return false;
        }

        if (totalSize > maxTotalSizeBytes) {
            photoInput.value = '';
            previewArea.innerHTML = '';
            photoError.textContent = '合計サイズが大きすぎます。枚数を減らして再度お試しください。';
            return false;
        }

        photoError.textContent = '';
        return true;
    }

    window.addEventListener('load', fetchLocation);
    photoInput.addEventListener('change', (event) => {
        if (validatePhotos(event.target.files)) {
            renderPreview(event.target.files);
        }
    });
</script>
</body>
</html>

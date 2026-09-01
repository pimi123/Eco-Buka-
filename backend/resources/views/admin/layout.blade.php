<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Eco Buka Admin' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-950">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="border-b border-slate-200 bg-white p-4 lg:border-b-0 lg:border-r">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-black">Eco Buka CMS</a>
            @auth
                <nav class="mt-6 flex gap-2 overflow-x-auto lg:grid">
                    @foreach ([
                        'homepage-content-display' => ['label' => 'Homepage Content Display', 'route' => route('admin.homepage-content.index')],
                        'orders' => ['label' => 'Orders', 'route' => route('admin.orders.index')],
                        'contact-messages' => ['label' => 'Contact Messages', 'route' => route('admin.contact-messages.index')],
                        'categories' => 'Categories',
                        'collections' => 'Collections',
                        'products' => 'Products',
                        'hero-banners' => 'Hero Banners',
                    ] as $key => $label)
                        <a class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-950" href="{{ is_array($label) ? $label['route'] : route('admin.content.index', $key) }}">{{ is_array($label) ? $label['label'] : $label }}</a>
                    @endforeach
                </nav>
                <form class="mt-6" method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold">Logout</button>
                </form>
            @endauth
        </aside>
        <main class="min-w-0 p-4 sm:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    <script>
        (() => {
            const imageLimitBytes = 8 * 1024 * 1024;
            const videoLimitBytes = 50 * 1024 * 1024;

            function fileLimit(input) {
                const accept = String(input.getAttribute('accept') || '').toLowerCase();
                const name = String(input.getAttribute('name') || '').toLowerCase();

                if (accept.includes('video') || name.includes('video')) {
                    return {
                        bytes: videoLimitBytes,
                        label: '50 MB',
                        type: 'video',
                    };
                }

                if (accept.includes('image') || name.includes('image') || name.includes('gallery')) {
                    return {
                        bytes: imageLimitBytes,
                        label: '8 MB',
                        type: 'image',
                    };
                }

                return null;
            }

            function clearUploadError(input) {
                input.classList.remove('border-red-400', 'bg-red-50');
                input.parentElement?.querySelector('[data-upload-size-error]')?.remove();
            }

            function showUploadError(input, message) {
                clearUploadError(input);
                input.classList.add('border-red-400', 'bg-red-50');

                const error = document.createElement('p');
                error.dataset.uploadSizeError = 'true';
                error.className = 'text-sm font-semibold text-red-700';
                error.textContent = message;
                input.insertAdjacentElement('afterend', error);
            }

            function validateUpload(input) {
                const limit = fileLimit(input);
                if (!limit || !input.files?.length) {
                    clearUploadError(input);
                    return true;
                }

                const oversizedFile = Array.from(input.files).find((file) => file.size > limit.bytes);
                if (!oversizedFile) {
                    clearUploadError(input);
                    return true;
                }

                showUploadError(
                    input,
                    `File size is too large. Maximum ${limit.type} size is ${limit.label}. Please upload an optimized file.`,
                );
                input.value = '';
                return false;
            }

            document.addEventListener('change', (event) => {
                if (event.target instanceof HTMLInputElement && event.target.type === 'file') {
                    validateUpload(event.target);
                }
            });

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;

                const invalidInput = Array.from(form.querySelectorAll('input[type="file"]')).find((input) => !validateUpload(input));
                if (!invalidInput) return;

                event.preventDefault();
                invalidInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                invalidInput.focus();
            });
        })();
    </script>
</body>
</html>

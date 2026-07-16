@php $rejected = auth()->user()->status === \App\Models\User::STATUS_REJECTED; @endphp
<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $rejected ? 'Registration declined' : 'Awaiting approval' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-gray-100">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-md text-center">
            <img src="{{ asset('eaj-appicon.png') }}" alt="{{ config('app.name') }}" class="mx-auto mb-6 h-14 w-auto">

            <div class="rounded-2xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
                @if ($rejected)
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-500/15 text-red-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h1 class="text-xl font-bold text-white">Registration declined</h1>
                    <p class="mt-3 text-sm text-gray-400">
                        Your account request wasn’t approved. Please contact your school administrator if you believe this is a mistake.
                    </p>
                @else
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/15 text-amber-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h1 class="text-xl font-bold text-white">You’re almost in</h1>
                    <p class="mt-3 text-sm text-gray-400">
                        Thanks for registering, <span class="font-semibold text-gray-200">{{ auth()->user()->name }}</span>.
                        Your account is awaiting administrator approval. Once approved, your
                        <span class="font-semibold text-brand-300">{{ \App\Models\User::TRIAL_DAYS }}-day free trial</span> starts and you can sign in to begin.
                    </p>
                    @if (auth()->user()->school)
                        <p class="mt-4 rounded-lg bg-white/5 px-4 py-3 text-xs text-gray-400">
                            School: <span class="font-medium text-gray-200">{{ auth()->user()->school->name }}</span> · ID {{ auth()->user()->school->school_id }}
                        </p>
                    @endif
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="w-full rounded-lg border border-white/15 py-2.5 text-sm font-medium text-gray-200 transition hover:bg-white/5">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

@php
    $siteUrl = rtrim(config('seo.site_url'), '/');
    $key = strtolower($form['code']);
    $canonical = $siteUrl.'/school-forms/'.$key;
    $title = $form['title'].' — DepEd School Form Automation | EAJ SF';
    $description = $form['description'].' EAJ SF helps schools automate DepEd School Forms from SF1 to SF10.';
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $title,
        'url' => $canonical,
        'description' => $description,
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => $siteUrl,
        ],
        'about' => [
            '@type' => 'Thing',
            'name' => $form['code'].' '.$form['name'],
        ],
    ];
@endphp

<x-public-layout
    :title="$title"
    :description="$description"
    :canonical="$canonical"
    :keywords="'DepEd '.$form['code'].', '.$form['code'].', School Form '.$form['number'].', '.$form['title'].', School Forms, DepEd School Forms, Automated School Forms System, EAJ SF'"
    :schema="$schema"
>
    <section class="relative overflow-hidden bg-animated-gradient pt-28">
        <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6">
            <a href="{{ route('public.school-forms') }}" class="text-sm font-semibold text-brand-300 hover:text-brand-200">
                ← All DepEd School Forms
            </a>

            <div class="mt-8 rounded-3xl border border-white/10 bg-white/[0.06] p-8 shadow-2xl shadow-navy-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="eyebrow">{{ $form['code'] }} · School Form {{ $form['number'] }}</span>
                        <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                            {{ $form['title'] }}
                        </h1>
                    </div>
                    <span class="w-fit rounded-full px-3 py-1.5 text-xs font-bold {{ $form['live'] ? 'bg-emerald-400/15 text-emerald-200' : 'bg-slate-500/15 text-slate-300' }}">
                        {{ $form['live'] ? 'Available in EAJ SF' : 'Related DepEd form' }}
                    </span>
                </div>

                <p class="mt-6 text-lg leading-relaxed text-slate-300">
                    {{ $form['description'] }}
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-navy-950/30 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Search terms</p>
                        <p class="mt-2 text-sm text-slate-300">{{ $form['code'] }}, School Form {{ $form['number'] }}, DepEd {{ $form['code'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-navy-950/30 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">System</p>
                        <p class="mt-2 text-sm text-slate-300">EAJ SF Automated School Forms System</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-navy-950/30 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Output</p>
                        <p class="mt-2 text-sm text-slate-300">Teacher-managed records and print-ready DepEd reports</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary btn-lg">Start using EAJ SF</a>
                    <a href="{{ route('landing') }}#features" class="btn btn-lg border border-white/15 text-slate-200 hover:bg-white/5">View live modules</a>
                </div>
            </div>

            <div class="mt-10">
                <h2 class="text-2xl font-extrabold text-white">Other DepEd School Forms</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($forms as $otherKey => $otherForm)
                        <a href="{{ route('public.school-form', $otherKey) }}"
                           class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-bold {{ $otherForm['code'] === $form['code'] ? 'bg-brand-500 text-white' : 'text-slate-300 hover:border-brand-400 hover:text-white' }}">
                            {{ $otherForm['code'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-public-layout>

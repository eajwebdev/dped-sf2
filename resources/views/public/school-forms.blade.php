@php
    $siteUrl = rtrim(config('seo.site_url'), '/');
    $description = 'Browse DepEd School Forms SF1 to SF10. EAJ SF automates live school forms including SF1, SF2, SF3, SF5, SF8, SF9, and SF10 for teachers and advisers.';
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'DepEd School Forms SF1 to SF10',
        'url' => $siteUrl.'/school-forms',
        'description' => $description,
        'hasPart' => collect($forms)->map(fn ($form, $key) => [
            '@type' => 'WebPage',
            'name' => $form['title'],
            'url' => $siteUrl.'/school-forms/'.$key,
        ])->values()->all(),
    ];
@endphp

<x-public-layout
    title="DepEd School Forms SF1 to SF10 — EAJ SF"
    :description="$description"
    :canonical="$siteUrl.'/school-forms'"
    keywords="DepEd School Forms, School Forms, SF1, SF2, SF3, SF4, SF5, SF6, SF7, SF8, SF9, SF10, School Form 1, School Form 2, School Form 10, EAJ SF"
    :schema="$schema"
>
    <section class="relative overflow-hidden bg-animated-gradient pt-28">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
            <div class="max-w-3xl">
                <span class="eyebrow">DepEd School Forms</span>
                <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    School Forms SF1 to SF10 automation
                </h1>
                <p class="mt-5 text-lg leading-relaxed text-slate-300">
                    EAJ SF is an Automated School Forms System for Philippine teachers, advisers, and school heads.
                    It helps manage learner records, attendance, textbook accountability, promotion reports,
                    health records, report cards, and permanent academic records.
                </p>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($forms as $key => $form)
                    <a href="{{ route('public.school-form', $key) }}"
                       class="rounded-2xl border border-white/10 bg-white/[0.06] p-5 shadow-lg shadow-navy-950/20 transition hover:border-brand-400/60 hover:bg-white/[0.09]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-black uppercase tracking-widest text-brand-300">{{ $form['code'] }}</p>
                                <h2 class="mt-2 text-lg font-extrabold text-white">{{ $form['name'] }}</h2>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $form['live'] ? 'bg-emerald-400/15 text-emerald-200' : 'bg-slate-500/15 text-slate-300' }}">
                                {{ $form['live'] ? 'Live' : 'Related' }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ $form['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-public-layout>

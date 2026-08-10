<?php

use Livewire\Component;
use Symfony\Component\Process\Process;

new class extends Component
{
    public string $activeTheme = 'nord';
    public string $statusMessage = '';
    public string $errorMessage = '';

    public array $themes = [
        [
            'slug' => 'default',
            'name' => 'Default Flux',
            'desc' => 'Standard Flux UI defaults with zinc neutrals and Inter font',
            'colors' => ['#71717a', '#3f3f46', '#18181b'],
            'tag' => 'Standard',
        ],
        [
            'slug' => 'nord',
            'name' => 'Nord Arctic',
            'desc' => 'Arctic-inspired muted blue-gray palette with clean, minimal aesthetics',
            'colors' => ['#5e81ac', '#81a1c1', '#88c0d0', '#2e3440'],
            'tag' => 'Cool & Minimal',
        ],
        [
            'slug' => 'catppuccin',
            'name' => 'Catppuccin Pastel',
            'desc' => 'Soothing pastel purple, lavender, and soft violet dev theme',
            'colors' => ['#cba6f7', '#89b4fa', '#f5c2e7', '#1e1e2e'],
            'tag' => 'Pastel',
        ],
        [
            'slug' => 'claude',
            'name' => 'Claude Anthropic',
            'desc' => 'Warm terra cotta, parchment, and cream tones inspired by Claude',
            'colors' => ['#d97706', '#f59e0b', '#fef3c7', '#451a03'],
            'tag' => 'Warm Editorial',
        ],
        [
            'slug' => 'posty-fresh',
            'name' => 'Posty Fresh Mint',
            'desc' => 'Mint green Posty variant with raised buttons and fresh emerald accents',
            'colors' => ['#10b981', '#34d399', '#a7f3d0', '#064e3b'],
            'tag' => 'Fresh Green',
        ],
        [
            'slug' => 'forest',
            'name' => 'Forest Earthy',
            'desc' => 'Warm earthy greens with stone-tinted neutrals and muted shadows',
            'colors' => ['#059669', '#10b981', '#d1fae5', '#065f46'],
            'tag' => 'Earthy',
        ],
        [
            'slug' => 'ocean',
            'name' => 'Ocean Slate',
            'desc' => 'Cool blue-tinted slate palette with vibrant sky accents',
            'colors' => ['#0284c7', '#38bdf8', '#e0f2fe', '#0c4a6e'],
            'tag' => 'Ocean Blue',
        ],
        [
            'slug' => 'posty',
            'name' => 'Posty Amber',
            'desc' => 'PostHog-inspired design with raised buttons, warm creams, and amber accents',
            'colors' => ['#f59e0b', '#fbbf24', '#fef3c7', '#78350f'],
            'tag' => 'Raised Buttons',
        ],
        [
            'slug' => 'posty-ice',
            'name' => 'Posty Ice Blue',
            'desc' => 'Cool blue Posty variant with raised buttons, icy neutrals, and sky accents',
            'colors' => ['#3b82f6', '#60a5fa', '#dbeafe', '#1e3a8a'],
            'tag' => 'Cool Slate',
        ],
        [
            'slug' => 'posty-charcoal',
            'name' => 'Posty Charcoal',
            'desc' => 'Muted slate Posty variant with raised buttons, cool grays, and violet accents',
            'colors' => ['#6366f1', '#818cf8', '#e0e7ff', '#312e81'],
            'tag' => 'Dark Slate',
        ],
        [
            'slug' => 'laravel',
            'name' => 'Laravel Red',
            'desc' => 'Boxy cards, pure neutral grays, and iconic Laravel red aesthetic',
            'colors' => ['#ef4444', '#f87171', '#fee2e2', '#7f1d1d'],
            'tag' => 'Branded',
        ],
        [
            'slug' => 'dracula',
            'name' => 'Dracula Dark',
            'desc' => 'Iconic dark palette with vibrant pastels on deep purple-gray backgrounds',
            'colors' => ['#bd93f9', '#ff79c6', '#8be9fd', '#282a36'],
            'tag' => 'Vibrant Dark',
        ],
        [
            'slug' => 'neon',
            'name' => 'Terminal Neon',
            'desc' => 'Hackerman terminal aesthetic with neon green accents on dark backgrounds',
            'colors' => ['#22c55e', '#4ade80', '#bbf7d0', '#052e16'],
            'tag' => 'High Contrast',
        ],
        [
            'slug' => 'bubblegum',
            'name' => 'Bubblegum Pink',
            'desc' => 'Playful pink accents with warm rose-tinted neutrals and rounded corners',
            'colors' => ['#ec4899', '#f472b6', '#fce7f3', '#831843'],
            'tag' => 'Playful',
        ],
        [
            'slug' => 'coffee',
            'name' => 'Warm Coffee',
            'desc' => 'Warm brown and gold tones with a cozy coffeehouse feel',
            'colors' => ['#92400e', '#b45309', '#fef3c7', '#451a03'],
            'tag' => 'Cozy',
        ],
        [
            'slug' => 'sunset',
            'name' => 'Golden Hour Sunset',
            'desc' => 'Warm coral and orange tones inspired by golden hour horizons',
            'colors' => ['#f97316', '#fb923c', '#ffedd5', '#7c2d12'],
            'tag' => 'Vibrant',
        ],
        [
            'slug' => 'synthwave',
            'name' => '80s Synthwave',
            'desc' => 'Neon 80s retrowave with hot pink accents on deep purple backgrounds',
            'colors' => ['#d946ef', '#f0abfc', '#fae8ff', '#701a75'],
            'tag' => 'Retrowave',
        ],
        [
            'slug' => 'retro',
            'name' => 'Vintage Retro',
            'desc' => 'Warm vintage parchment tones with peach-salmon accents and cozy typography',
            'colors' => ['#ea580c', '#f97316', '#ffedd5', '#431407'],
            'tag' => 'Vintage',
        ],
        [
            'slug' => 'brutalist',
            'name' => 'Monospace Brutalist',
            'desc' => 'Sharp corners, hard shadows, high contrast monospace aesthetic',
            'colors' => ['#000000', '#525252', '#e5e5e5', '#ffffff'],
            'tag' => 'Sharp',
        ],
        [
            'slug' => 'perpetuity',
            'name' => 'Perpetuity Teal',
            'desc' => 'Monospace teal aesthetic with tight shadows and minimal rounding',
            'colors' => ['#0d9488', '#2dd4bf', '#ccfbf1', '#134e4a'],
            'tag' => 'Teal Monospace',
        ],
    ];

    public function mount(): void
    {
        $this->activeTheme = session('tweakflux_active_theme', 'nord');
    }

    public function applyTheme(string $slug): void
    {
        $bin = base_path('vendor/bin/tweakflux');
        if (! file_exists($bin)) {
            $this->errorMessage = t('TweakFlux binary not found at vendor/bin/tweakflux.');
            return;
        }

        $process = new Process([$bin, 'apply', $slug], base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            $this->errorMessage = t('Failed to apply theme: ').$process->getErrorOutput();
            return;
        }

        $this->activeTheme = $slug;
        session(['tweakflux_active_theme' => $slug]);
        $this->statusMessage = t('Theme ":theme" applied successfully!', ['theme' => strtoupper($slug)]);
        $this->errorMessage = '';

        $this->dispatch('theme-changed', theme: $slug);
        $this->js('window.location.reload()');
    }
};

?>

<div class="space-y-6">

    <!-- Header Section (Official Flux Card) -->
    <flux:card class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white flex items-center justify-center shrink-0">
                {!! icon('swatch', 24) !!}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl" class="font-extrabold">{{ t('TweakFlux Theme Generator') }}</flux:heading>
                    <flux:badge color="zinc" size="sm" class="uppercase font-bold">TweakFlux v1.6</flux:badge>
                </div>
                <flux:subheading class="mt-1">{{ t('Override Livewire Flux UI Tailwind v4 CSS custom properties to transform every component in real-time.') }}</flux:subheading>
            </div>
        </div>

        <!-- Active theme badge -->
        <div class="flex items-center gap-3 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 px-4 py-2.5 rounded-xl shrink-0">
            <flux:text size="sm" class="font-semibold">{{ t('Active Theme:') }}</flux:text>
            <flux:badge color="emerald" class="uppercase font-extrabold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                {{ $activeTheme }}
            </flux:badge>
        </div>
    </flux:card>

    <!-- Alert Notifications -->
    @if ($statusMessage)
        <flux:callout variant="success" icon="check" class="shadow-sm">
            {{ $statusMessage }}
        </flux:callout>
    @endif

    @if ($errorMessage)
        <flux:callout variant="danger" icon="exclamation-triangle" class="shadow-sm">
            {{ $errorMessage }}
        </flux:callout>
    @endif

    <!-- Preset Theme Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <flux:heading size="lg" class="font-extrabold flex items-center gap-2">
                    {!! icon('swatch', 20) !!}
                    {{ t('Available Preset Themes') }} ({{ count($themes) }})
                </flux:heading>
                <flux:subheading>{{ t('Click "Apply Theme" to override Flux CSS custom properties dynamically') }}</flux:subheading>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($themes as $theme)
                @php $isActive = $activeTheme === $theme['slug']; @endphp
                <flux:card class="flex flex-col justify-between transition-all duration-200 {{ $isActive ? 'ring-2 ring-emerald-500/50 border-emerald-500' : '' }}">
                    <div>
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <flux:heading size="base" class="font-bold">{{ $theme['name'] }}</flux:heading>
                                <flux:badge size="sm" color="zinc" class="mt-1 font-bold uppercase text-[10px]">{{ $theme['tag'] }}</flux:badge>
                            </div>
                            @if ($isActive)
                                <flux:badge color="emerald" size="sm" class="uppercase font-extrabold">
                                    {{ t('Active') }}
                                </flux:badge>
                            @endif
                        </div>

                        <!-- Description -->
                        <flux:text size="sm" class="line-clamp-2 my-3">
                            {{ $theme['desc'] }}
                        </flux:text>

                        <!-- Color Palette Swatches -->
                        <div class="flex items-center gap-2 mb-4 p-2 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700">
                            @foreach ($theme['colors'] as $c)
                                <span class="w-6 h-6 rounded-lg border border-black/10 shadow-2xs" style="background-color: {{ $c }}" title="{{ $c }}"></span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div>
                        <flux:button
                            wire:click="applyTheme('{{ $theme['slug'] }}')"
                            wire:loading.attr="disabled"
                            variant="{{ $isActive ? 'subtle' : 'primary' }}"
                            class="w-full font-bold"
                        >
                            <span wire:loading.remove wire:target="applyTheme('{{ $theme['slug'] }}')">
                                {{ $isActive ? t('Re-apply Theme') : t('Apply Theme') }}
                            </span>
                            <span wire:loading wire:target="applyTheme('{{ $theme['slug'] }}')">
                                {{ t('Applying...') }}
                            </span>
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    </div>

    <!-- Live Flux Component Showcase / Preview (Official Flux Controls) -->
    <flux:card class="space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div>
                <flux:heading size="lg" class="font-extrabold flex items-center gap-2">
                    {!! icon('eye', 20) !!}
                    {{ t('Live Flux Component Showcase') }}
                </flux:heading>
                <flux:subheading>{{ t('Every Flux UI control below inherits theme accent colors, font family, border radius, and raised button styles') }}</flux:subheading>
            </div>
            <flux:badge color="zinc" class="font-bold">THEME: {{ strtoupper($activeTheme) }}</flux:badge>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card 1: Buttons & Variants -->
            <flux:card class="bg-zinc-50/50 dark:bg-zinc-800/40 space-y-3">
                <flux:heading size="sm" class="font-bold uppercase tracking-wider text-zinc-500 mb-2">{{ t('Buttons & Variants') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    <flux:button variant="primary">{{ t('Primary') }}</flux:button>
                    <flux:button variant="filled">{{ t('Filled') }}</flux:button>
                    <flux:button variant="subtle">{{ t('Subtle') }}</flux:button>
                    <flux:button variant="danger">{{ t('Danger') }}</flux:button>
                </div>
            </flux:card>

            <!-- Card 2: Badges & Statuses -->
            <flux:card class="bg-zinc-50/50 dark:bg-zinc-800/40 space-y-3">
                <flux:heading size="sm" class="font-bold uppercase tracking-wider text-zinc-500 mb-2">{{ t('Badges & Colors') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    <flux:badge color="emerald">{{ t('Active Customer') }}</flux:badge>
                    <flux:badge color="amber">{{ t('Pending Approval') }}</flux:badge>
                    <flux:badge color="sky">{{ t('Meter Reading OK') }}</flux:badge>
                    <flux:badge color="rose">{{ t('Disconnected') }}</flux:badge>
                </div>
            </flux:card>

            <!-- Card 3: Form Controls -->
            <flux:card class="bg-zinc-50/50 dark:bg-zinc-800/40 space-y-3">
                <flux:heading size="sm" class="font-bold uppercase tracking-wider text-zinc-500 mb-2">{{ t('Form Controls') }}</flux:heading>
                <flux:input placeholder="Meter serial number..." icon="magnifying-glass" />
            </flux:card>

        </div>
    </flux:card>

</div>

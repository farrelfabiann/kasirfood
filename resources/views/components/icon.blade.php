@props(['name' => 'grid', 'class' => 'h-5 w-5'])

<svg {{ $attributes->merge([
    'class' => $class,
    'fill' => 'none',
    'viewBox' => '0 0 24 24',
    'stroke' => 'currentColor',
    'stroke-width' => '1.8',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
]) }}>
    @switch($name)
        @case('utensils')
            <path d="M6 3v7a2 2 0 0 0 2 2v9" />
            <path d="M10 3v7a2 2 0 0 1-2 2" />
            <path d="M6 3v4" />
            <path d="M17 3c-1.7 0-3 2-3 4.5s1.3 4.5 3 4.5v9" />
            <path d="M17 3v18" />
            @break

        @case('cup')
        @case('coffee')
            <path d="M5 8h11v6a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8Z" />
            <path d="M16 9h1.5a2.5 2.5 0 0 1 0 5H16" />
            <path d="M7 3v2M11 3v2M15 3v2" />
            <path d="M5 21h12" />
            @break

        @case('cake')
            <path d="M4 21h16" />
            <path d="M5 21v-7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v7" />
            <path d="M5 16c1.5 0 1.5 1.5 3 1.5s1.5-1.5 3-1.5 1.5 1.5 3 1.5 1.5-1.5 3-1.5 1.5 1.5 3 1.5" />
            <path d="M8 12V9M12 12V9M16 12V9" />
            @break

        @case('fire')
            <path d="M12 3c2.5 3 5 5 5 8.5a5 5 0 0 1-10 0C7 9 8.5 6.5 12 3Z" />
            <path d="M12 13c1.2 1 2 1.8 2 3a2 2 0 1 1-4 0c0-1.2.8-2 2-3Z" />
            @break

        @case('table')
            <path d="M3 8h18" />
            <path d="M5 8v11M19 8v11" />
            <path d="M8 12h8M12 8v4" />
            @break

        @case('bag')
        @case('shopping-bag')
            <path d="M6 8h12l1 12H5L6 8Z" />
            <path d="M9 8V6a3 3 0 0 1 6 0v2" />
            @break

        @case('cart')
        @case('shopping-cart')
            <circle cx="9" cy="20" r="1" />
            <circle cx="18" cy="20" r="1" />
            <path d="M3 4h2l2.2 10.2A2 2 0 0 0 9.2 16H18" />
            <path d="M6 8h14l-2 6H7.5" />
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break

        @case('minus')
            <path d="M5 12h14" />
            @break

        @case('trash')
            <path d="M4 7h16" />
            <path d="M9 7V4h6v3" />
            <path d="M6 7l1 13h10l1-13" />
            <path d="M10 11v5M14 11v5" />
            @break

        @case('x')
            <path d="m6 6 12 12M18 6 6 18" />
            @break

        @case('check')
            <path d="m5 12.5 4.5 4.5L19 6.5" />
            @break

        @case('check-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="m8.5 12 2.5 2.5 4.5-5" />
            @break

        @case('x-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="m9 9 6 6m0-6-6 6" />
            @break

        @case('chevron-right')
            <path d="m9 5 7 7-7 7" />
            @break

        @case('chevron-left')
            <path d="m15 5-7 7 7 7" />
            @break

        @case('chevron-down')
            <path d="m5 9 7 7 7-7" />
            @break

        @case('chevron-up')
            <path d="m5 15 7-7 7 7" />
            @break

        @case('arrow-left')
            <path d="M20 12H4" />
            <path d="m10 6-6 6 6 6" />
            @break

        @case('arrow-right')
            <path d="M4 12h16" />
            <path d="m14 6 6 6-6 6" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('credit-card')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="M3 9h18M7 15h3" />
            @break

        @case('banknotes')
        @case('cash')
            <rect x="3" y="6" width="18" height="12" rx="2" />
            <circle cx="12" cy="12" r="2.5" />
            <path d="M6 10v.01M18 14v.01" />
            @break

        @case('qr-code')
            <rect x="3" y="3" width="6" height="6" rx="1" />
            <rect x="15" y="3" width="6" height="6" rx="1" />
            <rect x="3" y="15" width="6" height="6" rx="1" />
            <path d="M15 15h2v2h-2zM19 19h2v2h-2zM15 21h2M21 15v2M18 18h3" />
            @break

        @case('chart')
            <path d="M4 20h16" />
            <path d="M7 16V9M12 16V5M17 16v-4" />
            @break

        @case('cube')
            <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" />
            <path d="m4 7.5 8 4.5 8-4.5M12 12v9" />
            @break

        @case('clipboard')
            <rect x="5" y="5" width="14" height="16" rx="2" />
            <path d="M9 5V3h6v2M9 11h6M9 15h6" />
            @break

        @case('pencil')
            <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" />
            <path d="m14 7 3 3" />
            @break

        @case('photo')
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <circle cx="8.5" cy="9.5" r="1.5" />
            <path d="m4 18 5-5 4 4 3-3 4 4" />
            @break

        @case('search')
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path d="m16 16 5 5" />
            @break

        @case('user')
            <circle cx="12" cy="8" r="4" />
            <path d="M4.5 21a7.5 7.5 0 0 1 15 0" />
            @break

        @case('logout')
            <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4" />
            <path d="m10 8-4 4 4 4" />
            <path d="M6 12h10" />
            @break

        @case('warning')
        @case('exclamation-circle')
            <path d="M10.3 4.3 2.8 18a2 2 0 0 0 1.8 3h14.8a2 2 0 0 0 1.8-3L13.7 4.3a2 2 0 0 0-3.4 0Z" />
            <path d="M12 9v4M12 17v.01" />
            @break

        @case('info')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5M12 8v.01" />
            @break

        @case('refresh')
            <path d="M20 11a8 8 0 1 0-2.3 6.7" />
            <path d="M20 4v7h-7" />
            @break

        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break

        @case('home')
            <path d="m3 11 9-8 9 8" />
            <path d="M5 10v10h14V10" />
            <path d="M10 20v-6h4v6" />
            @break

        @case('receipt')
            <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z" />
            <path d="M9 8h6M9 12h6" />
            @break

        @case('sparkles')
            <path d="m12 3 1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3Z" />
            <path d="m19 15 .7 2.3L22 18l-2.3.7L19 21l-.7-2.3L16 18l2.3-.7L19 15Z" />
            @break

        @case('tag')
            <path d="M20 13 13 20 4 11V4h7l9 9Z" />
            <circle cx="8" cy="8" r="1" />
            @break

        @case('promo')
            <circle cx="12" cy="12" r="9" />
            <path d="m8.5 15.5 7-7" />
            <circle cx="9" cy="9" r="1" />
            <circle cx="15" cy="15" r="1" />
            @break

        @case('star')
            <path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.2 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.7l5.9-.9L12 3.5Z" />
            @break

        @default
            <rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1.5" />
            <rect x="14" y="3.5" width="6.5" height="6.5" rx="1.5" />
            <rect x="3.5" y="14" width="6.5" height="6.5" rx="1.5" />
            <rect x="14" y="14" width="6.5" height="6.5" rx="1.5" />
    @endswitch
</svg>

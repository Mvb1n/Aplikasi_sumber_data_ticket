@props(['active', 'sidebar' => false])

@php
if ($sidebar) {
    $classes = ($active ?? false)
                ? 'flex items-center w-full px-4 py-2 mt-2 text-white bg-gray-700 rounded-md'
                : 'flex items-center w-full px-4 py-2 mt-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md';
} else {
    $classes = ($active ?? false)
                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
}
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} :class="isSidebarOpen ? '' : 'justify-center'">
    @if(isset($icon))
        <div class="flex-shrink-0">
            {{ $icon }}
        </div>
    @endif

    <span class="mx-4 font-medium whitespace-nowrap overflow-hidden transition-all duration-200"
          :class="isSidebarOpen ? 'w-auto opacity-100' : 'w-0 opacity-0'">
        {{ $slot }}
    </span>
</a>

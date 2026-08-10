@php
    $productImages = $product->images
        ->filter(fn ($image) => file_exists(public_path($image->path)))
        ->map(fn ($image) => [
            'url' => asset($image->path),
            'alt' => $image->alt_text ?? $product->name,
        ])
        ->values()
        ->all();
@endphp

@if(!empty($productImages))
    <button
        type="button"
        @click.stop="openProductImageLightbox(@js($product->name), @js($productImages), 0)"
        class="{{ $buttonClass ?? 'w-full h-full' }} cursor-zoom-in group/image relative"
        aria-label="View full size photos of {{ $product->name }}"
    >
        <img src="{{ $productImages[0]['url'] }}" alt="{{ $productImages[0]['alt'] }}" class="{{ $imgClass ?? 'w-full h-full object-cover' }}">
        <span class="absolute inset-0 bg-black/0 group-hover/image:bg-black/20 transition-colors flex items-center justify-center rounded-[inherit]">
            <i class="fa-solid fa-expand text-white opacity-0 group-hover/image:opacity-100 transition-opacity text-sm drop-shadow"></i>
        </span>
    </button>
@else
    <i class="fa-solid fa-{{ $fallbackIcon ?? 'utensils' }} {{ $fallbackIconClass ?? '' }}"></i>
@endif

<div class="max-w-7xl mx-auto px-4">
    <input 
        type="text" 
        class="form-control mb-4 w-full" 
        placeholder="Suche nach Filmen..." 
        wire:model="searchTerm"
        wire:keyup.debounce.500ms="search"
    >

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @if($errorMessage)
            <div class="bg-red-100 text-red-800 p-2 rounded mb-2">
                {{ $errorMessage }}
            </div>
        @endif
        @foreach($movies as $movie)
            <div wire:key="movie-{{ $movie->id }}" class="border rounded p-2 bg-white">
                @if(!empty($movie->poster_url))
                    <img src="{{ $movie->poster_url }}" class="w-full h-auto mb-2" alt="{{ $movie->title }}">
                @else
                    <div class="w-full h-64 flex items-center justify-center bg-gray-200 text-gray-500 mb-2">
                        🎬 No image available
                    </div>
                @endif
                <h3 class="font-bold mt-1">{{ $movie->title }}</h3>
                <p class="text-sm text-gray-500">{{ $movie->release_date ?? '–' }}</p>
                <p class="text-sm text-gray-500">{{ $movie->overview ?? '–' }}</p>
                
                <div class="mt-1">
                    @for($i = 1; $i <= 5; $i++)
                        <button 
                            wire:click="rateMovie({{ $movie->id }}, {{ $i }})" 
                            wire:loading.attr="disabled"
                            class="text-yellow-500"
                        >
                        @if($i <= $movie->current_user_rating)
                            ★
                        @else
                            ☆
                        @endif
                        </button>
                    @endfor
                    <span class="ml-2">{{ number_format($movie->average_rating, 1) }}/5</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $movies->links() }}
    </div>
</div>

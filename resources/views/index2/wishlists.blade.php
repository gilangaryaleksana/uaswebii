@extends('v_user.v_wishlists.app')

@section('content-wishlists')
    <div
        class="md:pt-[14rem] pt-4 px-5 w-full flex-1 md:grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 container overflow-y-auto">

        @if ($items->isEmpty())
            <div class="flex flex-col gap-2 md:justify-normal md:items-baseline md:pt-0 justify-center items-center pt-4">
                <h1 class="text-4xl font-sans select-none font-semibold font-sans">Your Wishlist</h1>
                <p>Your Favorite is Empty</p>
            </div>
        @else
            <h1 class="text-4xl font-sans select-none font-semibold font-sans">Your Wishlist</h1>
            @foreach ($items as $item)
                <a class="flex justify-center"
                    href="{{ route('product.show', ['id' => $item->product->id, 'slug' => $item->product->slug]) }}">
                    <div
                        class="relative grid grid-rows-[auto_auto_1fr_auto] p-3 rounded w-[18rem] h-[400px] hover:border-black border-white border-1">

                        <img src="{{ $item->product->image }}" class="rounded object-cover w-full" alt="">

                        @if ($item->product->badge)
                            <span class="px-2 py-1 bg-black text-white text-xs rounded mt-2">
                                {{ $item->product->badge }}
                            </span>
                        @endif

                        <h2 class="text-lg font-semibold mt-2">
                            {{ $item->product->name }}
                        </h2>

                        <p class="text-gray-600">
                            ${{ number_format($item->product->price, 2) }}
                        </p>

                        <form action="{{ route('wishlists.destroy', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cursor-pointer absolute top-7 right-7">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-heart-minus-icon lucide-heart-minus hover:fill-black">
                                    <path
                                        d="m14.876 18.99-1.368 1.323a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5a5.2 5.2 0 0 1-.244 1.572" />
                                    <path d="M15 15h6" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </a>
            @endforeach
        @endif
    </div>
@endsection
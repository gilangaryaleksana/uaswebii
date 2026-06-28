<div class="w-3xs flex flex-col items-center justify-center md:min-h-screen h-auto overflow-y-auto md:mt-0 mt-10">
    <div class="flex w-full flex-col gap-3 mt-10 pl-5">
        <div class="flex flex-col justify-start">
            <a href="{{ route('user.dashboard') }}"
            class="focus:border-b-red-600 px-2 py-2
            transition-all duration-100 border-b-1 cursor-pointer
            hover:text-black/80! hover:border-b-black hover:bg-gray-100
            {{ request()->routeIs('user.dashboard') ? 'text-white! bg-black' : 'border-b-white' }}
            ">
            Profil
            </a>
            <a href="{{ route('user.order') }}" 
            class="focus:border-b-red-600 px-2 py-2
            transition-all duration-100 border-b-1 cursor-pointer
            hover:text-black/80! hover:border-b-black hover:bg-gray-100
            {{ request()->routeIs('user.order') ? 'text-white! bg-black' : 'border-b-white' }}
            ">
            Order</a>
            <a href="{{ route('user.cart') }}" 
            class="focus:border-b-red-600 px-2 py-2
            transition-all duration-100 border-b-1 cursor-pointer
            hover:text-black/80! hover:border-b-black hover:bg-gray-100
            {{ request()->routeIs('user.cart') ? 'text-white! bg-black' : 'border-b-white' }}
            ">
            Cart</a>
            <a href="{{ route('user.wishlists') }}"
            class="focus:border-b-red-600 px-2 py-2
            transition-all duration-100 border-b-1 cursor-pointer
            hover:text-black/80! hover:border-b-black hover:bg-gray-100
            {{ request()->routeIs('user.wishlists') ? 'text-white! bg-black' : 'border-b-white' }}
            ">
            Wishlists</a>
            <form action="{{ route('logout') }}" method="POST" class="text-red-600 px-2 py-2 transition-all duration-100 hover:text-red-600/80 hover:bg-gray-100 cursor-pointer hover:border-b-red-600 border-b-white border-b-1 hover:border-b-1 focus:border-b-red-600 focus:border-b-1">
                @csrf
                <button class="cursor-pointer">Log Out</button>
            </form>
        </div>
    </div>
</div>
<div class="fixed hidden md:block bottom-0 left-0 -z-10">
    <img src="https://preview.thenewsmarket.com/Previews/ADID/StillAssets/1920x1440/689347.jpg" width="120">
</div>
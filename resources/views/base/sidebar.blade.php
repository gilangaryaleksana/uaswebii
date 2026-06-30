<!-- start sidebar -->
<div id="sideBar"
  class="relative flex overflow-y-auto flex-col flex-wrap bg-white border-r border-gray-300 p-6 flex-none w-64 md:-ml-64 md:fixed md:top-0 md:z-30 md:h-screen md:shadow-xl animated faster">
  <!-- sidebar content -->
  <div class="flex flex-col">
    <!-- sidebar toggle -->
    <div class="text-right hidden md:block mb-4">
      <button id="sideBarHideBtn">
        <i class="fad fa-times-circle"></i>
      </button>
    </div>
    <!-- end sidebar toggle -->

    <p class="uppercase text-xs text-gray-600 mb-4 tracking-wider">homes</p>

    <!-- link -->
    <a href="{{ route('admin.dashboard.analytics') }}" class="mb-3 capitalize font-medium text-sm transition
       {{ request()->routeIs('admin.dashboard.analytics') ? 'active-link' : 'hover:text-teal-600' }}">
      <i class="fad fa-chart-pie text-xs mr-2"></i>
      Analytics dashboard
    </a>

    <a href="{{ route('admin.dashboard.ecommerce') }}" class="mb-3 capitalize font-medium text-sm transition
       {{ request()->routeIs('admin.dashboard.ecommerce') ? 'active-link' : 'hover:text-teal-600' }}">
      <i class="fad fa-shopping-cart text-xs mr-2"></i>
      Ecommerce dashboard
    </a>

    <p class="uppercase text-xs text-gray-600 mb-4 mt-4 tracking-wider">apps</p>

    <a href="{{ route('admin.dashboard.users') }}" class="mb-3 capitalize font-medium text-sm transition
       {{ request()->routeIs('admin.dashboard.users') ? 'active-link' : 'hover:text-teal-600' }}">
      <i class="fad fa-user text-xs mr-2"></i> User
    </a>

    <!-- order dropdown -->
    @php
$orderActive = request()->routeIs('admin.dashboard.pendingOrder*');
    @endphp
    
    <div class="mb-3">
      <button onclick="toggleOrderMenu()" class="w-full flex items-center justify-between capitalize font-medium text-sm transition
        {{ $orderActive ? 'active-link' : 'hover:text-teal-600' }}" style="outline: none">
        <span>
          <i class="fad fa-shield-check text-xs mr-2"></i>
          Order
        </span>
        <i id="orderArrow" class="fas fa-chevron-left text-xs transition-transform duration-300 transform
          {{ $orderActive ? '-rotate-90' : '' }}">
        </i>
      </button>
    
      <!-- dropdown -->
      <div id="orderMenu" class="ml-6 mt-2 flex flex-col gap-2 text-xs text-gray-500 pl-5
           {{ $orderActive ? '' : 'hidden' }}">
        <a href="{{ route('admin.dashboard.pendingOrder') }}"
          class="{{ empty(request('status')) ? 'active-filter' : 'hover:text-teal-600' }} p-1">
          All ({{ $totalOrders }})
        </a>
    
        <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'pending']) }}"
          class="{{ request('status') == 'pending' ? 'active-filter' : 'hover:text-teal-600' }} p-1">
          Pending ({{ $pendingOrders }})
        </a>

        <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'paid']) }}"
          class="{{ request('status') == 'paid' ? 'active-filter' : 'hover:text-teal-600' }} p-1">
          Paid ({{ $paidOrders }})
        </a>
    
        <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'shipped']) }}"
          class="{{ request('status') == 'shipped' ? 'active-filter' : 'hover:text-teal-600' }} p-1">
          Shipped ({{ $shippedOrders }})
        </a>
    
        <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'delivered']) }}"
          class="{{ request('status') == 'delivered' ? 'active-filter' : 'hover:text-teal-600' }} p-1">
          Delivered ({{ $deliveredOrders }})
        </a>
    
        <a href="{{ route('admin.dashboard.pendingOrder', ['status' => 'cancelled']) }}"
          class="{{ request('status') == 'cancelled' ? 'active-filter' : 'hover:text-teal-600' }} p-1">
          Cancelled ({{ $cancelledOrders }})
        </a>
      </div>
    </div>

    <!-- end order dropdown -->

    <a href="{{ route('admin.dashboard.calendar') }}" class="mb-3 capitalize font-medium text-sm transition
       {{ request()->routeIs('admin.dashboard.calendar') ? 'active-link' : 'hover:text-teal-600' }}">
      <i class="fad fa-calendar-edit text-xs mr-2"></i>
      Calendar
    </a>

    <a href="{{ route('admin.dashboard.invoice') }}" class="mb-3 capitalize font-medium text-sm transition
       {{ request()->routeIs('admin.dashboard.invoice') ? 'active-link' : 'hover:text-teal-600' }}">
      <i class="fad fa-file-invoice-dollar text-xs mr-2"></i>
      Invoice
    </a>

    <a href="{{ route('admin.dashboard.salesReport') }}" class="mb-3 capitalize font-medium text-sm transition
       {{ request()->routeIs('admin.dashboard.salesReport') ? 'active-link' : 'hover:text-teal-600' }}">
      <i class="fad fa-chart-line text-xs mr-2"></i>
      Laporan Penjualan
    </a>

    <a href="#" class="mb-3 capitalize font-medium text-sm transition hover:text-teal-600">
      <i class="fad fa-folder-open text-xs mr-2"></i>
      File manager
    </a>

  </div>
  <!-- end sidebar content -->

</div>
<!-- end sidebar -->

<style>
  .active-link {
    color: #0d9488;
    font-weight: 600;
    background-color: #f0fdfa;
    border-radius: 0.375rem;
    padding: 0.25rem 0.5rem;
  }

  .active-filter {
    color: #0d9488;
    font-weight: 600;
    background-color: #f3f4f6;
  }
</style>

<script>
  function toggleOrderMenu() {
    const menu = document.getElementById('orderMenu');
    const arrow = document.getElementById('orderArrow');

    menu.classList.toggle('hidden');
    arrow.classList.toggle('-rotate-90');
  }
</script>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Main</h6>
                    <ul>
                        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}"><i data-feather="grid"></i><span>Dashboard</span></a>
                        </li>
                        <li class="{{ request()->routeIs('pos.index') ? 'active' : '' }}">
                            <a href="{{ route('pos.index') }}"><i data-feather="shopping-cart"></i><span>POS</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Inventory</h6>
                    <ul>
                        <li class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <a href="{{ route('products.index') }}"><i data-feather="box"></i><span>Products</span></a>
                        </li>
                        <li class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <a href="{{ route('categories.index') }}"><i data-feather="codepen"></i><span>Categories</span></a>
                        </li>
                        <li class="{{ request()->routeIs('brands.*') ? 'active' : '' }}">
                            <a href="{{ route('brands.index') }}"><i data-feather="tag"></i><span>Brands</span></a>
                        </li>
                        <li class="{{ request()->routeIs('units.*') ? 'active' : '' }}">
                            <a href="{{ route('units.index') }}"><i data-feather="speaker"></i><span>Units</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Sales</h6>
                    <ul>
                        <li class="{{ request()->routeIs('sells.*') ? 'active' : '' }}">
                            <a href="{{ route('sells.index') }}"><i data-feather="file-text"></i><span>Sales</span></a>
                        </li>
                        <li class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                            <a href="{{ route('purchases.index') }}"><i data-feather="shopping-bag"></i><span>Purchases</span></a>
                        </li>
                        <li class="{{ request()->routeIs('contacts.*','contacts.customers','contacts.suppliers') ? 'active' : '' }}">
                            <a href="{{ route('contacts.index') }}"><i data-feather="users"></i><span>Contacts</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Finance</h6>
                    <ul>
                        <li class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                            <a href="{{ route('expenses.index') }}"><i data-feather="dollar-sign"></i><span>Expenses</span></a>
                        </li>
                        <li class="{{ request()->routeIs('cash-registers.*') ? 'active' : '' }}">
                            <a href="{{ route('cash-registers.index') }}"><i data-feather="trello"></i><span>Cash Register</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Stock</h6>
                    <ul>
                        <li class="{{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}">
                            <a href="{{ route('stock-adjustments.index') }}"><i data-feather="package"></i><span>Stock Adjustment</span></a>
                        </li>
                        <li class="{{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}">
                            <a href="{{ route('stock-transfers.index') }}"><i data-feather="refresh-cw"></i><span>Stock Transfer</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Membership</h6>
                    <ul>
                        <li class="{{ request()->routeIs('memberships.*') ? 'active' : '' }}">
                            <a href="{{ route('memberships.index') }}"><i data-feather="award"></i><span>Memberships</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Barcode Center</h6>
                    <ul>
                        <li class="{{ request()->routeIs('barcode-center.*') ? 'active' : '' }}">
                            <a href="{{ route('barcode-center.index') }}"><i data-feather="grid"></i><span>Barcode Center</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Scale</h6>
                    <ul>
                        <li class="{{ request()->routeIs('scales.*') ? 'active' : '' }}">
                            <a href="{{ route('scales.index') }}"><i data-feather="scale"></i><span>Scales</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Marketplace</h6>
                    <ul>
                        <li class="{{ request()->routeIs('marketplace.*') ? 'active' : '' }}">
                            <a href="{{ route('marketplace.index') }}"><i data-feather="shopping-cart"></i><span>Marketplace</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Reports</h6>
                    <ul>
                        <li class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}"><i data-feather="bar-chart-2"></i><span>Reports</span></a>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Settings</h6>
                    <ul>
                        <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <a href="{{ route('settings.index') }}"><i data-feather="settings"></i><span>Settings</span></a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->

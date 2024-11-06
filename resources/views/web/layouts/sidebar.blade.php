<aside class="main-sidebar sidebar-dark-primary bg-navy elevation-4">
    <div class="sidebar">
        {{-- 驗證使用者是否登入 --}}
        {{-- @auth
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            嗨!!
            @if(Auth::user()->avatar)
            <div class="image">
                <a href="{{ route('users.showAvatar') }}" class="px-1">
                    <img src="{{ Auth::user()->getAvatarUrl() }}" style="width: 30px; height: 30px;" class="rounded-circle mt-1">
                </a>
            </div>
            <div class="info">
                <a href="{{ route('users.profile') }}" class="d-block">{{ Auth::user()->name ?? '' }}</a>
            </div>
            @else
            <div class="info">
                <i class="fas fa-user mr-2"></i>{{ Auth::user()->name ?? '' }}
            </div>
            @endif
        </div>
        @endauth --}}
        <nav id="sidebar" class="mt-2 nav-compact">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item has-treeview">
                    <a href="javascript:" class="nav-link">
                        <i class="fas fa-users"></i>
                        <p>
                            選單一
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="nav-icon far fa-list-alt"></i>
                                <p>選單一之一</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="nav-icon far fa-list-alt"></i>
                                <p>選單一之二</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="nav-icon far fa-list-alt"></i>
                                <p>選單一之三</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="javascript:" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>選單二</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>選單三</p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="javascript:" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>
                            選單四
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="fas fa-users nav-icon"></i>
                                <p>選單四之一</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="fas fa-users nav-icon"></i>
                                <p>選單四之二</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="javascript:" class="nav-link">
                        <i class="fas fa-users"></i>
                        <p>
                            選單五
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>選單五之一</p>
                                <span class="right badge badge-info">{{ $posts_total ?? '' }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="javascript:" class="nav-link">
                        <i class="fas fa-users"></i>
                        <p>
                            選單六
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="javascript:" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>選單六之一</p>
                                <span class="right badge badge-info">{{ $products_total ?? '' }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</aside>

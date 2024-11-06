<nav id="topbar" class="main-header navbar navbar-expand navbar-dark bg-navy">
    <a href="javascript:" title="側邊選單" class="nav-link" data-widget="pushmenu" role="button"><i class="fas fa-bars"></i></a>
    <a href="{{ route('index') }}" class="navbar-brand">
        <span class="brand-text font-weight-light">iCarry 開發團隊用測試站台</span>
    </a>
        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            {{-- 驗證使用是否登入 在使用者未登入前 只會看到登入兩個字 登入後就會切換到 另一個下拉表單 --}}
            {{-- @auth
            <li class="nav-item dropdown">
                <a id="userSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
                    @if(Auth::user()->avatar)
                    <img src="{{ Auth::user()->getAvatarUrl() }}" style="width: 30px; height: 30px;" class="rounded-circle">
                    @else
                    <i class="fas fa-user"></i>
                    @endif
                    {{ Auth::user()->name }}
                </a>
                <ul aria-labelledby="userSubMenu1" class="dropdown-menu border-0 shadow">
                    <li>
                        <a href="{{ route('users.profile') }}" class="dropdown-item">個人資料</a>
                    </li>
                    <li>
                        <a href="{{ route('users.showAvatar') }}" class="dropdown-item">修改頭像</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a href="{{ route('cart.index') }}" class="dropdown-item">購物車</a>
                    </li>
                    <li>
                        <a href="{{ route('order.index') }}" class="dropdown-item">我的訂單</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); $('#logout-form').submit();"><i class="nav-icon fas fa-door-open text-danger"></i> 登出</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
            @else
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">登入</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link">註冊</a>
            </li>
            @endauth --}}
            {{-- <li class="nav-item">
                <a class="nav-link" href="">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    <span class="badge badge-danger navbar-badge">{{ $carts_total ?? '' }}</span>
                  </a>
            </li> --}}
            {{-- <li class="nav-item">
                <a href="javascript:" title="側邊選單" class="nav-link" data-widget="pushmenu" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li> --}}
            <li class="nav-item"><a href="{{ route('payTest') }}" class="nav-link">金流測試</a></li>
            <li class="nav-item"><a href="https://{{ env('ADMIN_DOMAIN') }}" class="nav-link">後台管理</a></li>
            <li class="nav-item">
                <a href="javascript:" id="fullscreen-button" title="擴展成全螢幕" class="nav-link" data-widget="fullscreen" data-slide="true" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
</nav>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('web.layouts.meta')
    @yield('meta')

    <title>Roger 測試站台 - @yield('title')</title>

    @include('web.layouts.css')
    @yield('css')

    @include('web.layouts.js')
    @yield('script')
</head>

<body class="hold-transition sidebar-collapse layout-top-nav layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        @include('web.layouts.topbar')
        @include('web.layouts.sidebar')

        @section('content')
        @show

        @include('web.layouts.footer')
        <div id="particles-js"></div>
    </div>

    <script src="{{ asset('js/web.common.js') }}"></script>
    @yield('CustomScript')
    @yield('JsValidator')
</body>

</html>

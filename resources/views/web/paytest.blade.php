@extends('web.layouts.master')

@section('title', '首頁')

@section('meta')
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container bg-white">
            <div class="row">
                <div class="col-12">
                    <div class="card card-danger card-outline">
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center">iCarry 開發團隊用測試站台</h3>
                            <i class="fas fa-info text-primary"></i> 此網站主要是 iCarry 開發團隊用來做測試用。<br>
                            <i class="fas fa-info text-danger"></i> <span class="text-danger">此網站僅供 iCarry 開發團隊用測試用，並非完整資料或資訊，請勿以此網站內容當作依據。</span><br>
                        </div>
                    </div>
                    <div class="card card-danger card-outline">
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center">智付通(藍新)金流測試</h3>
                            <i class="fas fa-info text-primary"></i> 智付通(藍新)金流測試，信用卡號僅接受 4000-2211-1111-1111 ， 商店將於 2021/7/12 到期失效。<br>
                            <div class="row mt-2">
                                <div class="mb-2 col-2">
                                    <form action="https://dev-api.icarry.me/web/v1/order" method="POST">
                                        <input type="hidden" name="domain" value="icarry.me">
                                        <input type="hidden" name="from_country_id" value="1">
                                        <input type="hidden" name="to_country_id" value="1">
                                        <input type="hidden" name="create_type" value="web">
                                        <input type="hidden" name="pay_method" value="智付通信用卡">
                                        <input type="hidden" name="buyer_name" value="Roger Wu">
                                        <input type="hidden" name="buyer_email" value="weayzehoa@gmail.com">
                                        <input type="hidden" name="invoice_sub_type" value="1">
                                        <input type="hidden" name="shipping_method_id" value="4">
                                        <input type="hidden" name="take_time" value="2021-07-01">
                                        <input type="hidden" name="user_address_id" value="34360">
                                        <input type="hidden" name="user_memo" value="金流測試">
                                        <button type="submit" class="btn-block btn btn-success">智付通信用卡</button>
                                    </form>
                                </div>
                                <div class="mb-2 col-2">
                                    <form action="https://dev-api.icarry.me/web/v1/order" method="POST">
                                        <input type="hidden" name="domain" value="icarry.me">
                                        <input type="hidden" name="from_country_id" value="1">
                                        <input type="hidden" name="to_country_id" value="1">
                                        <input type="hidden" name="create_type" value="web">
                                        <input type="hidden" name="pay_method" value="智付通ATM">
                                        <input type="hidden" name="buyer_name" value="Roger Wu">
                                        <input type="hidden" name="buyer_email" value="weayzehoa@gmail.com">
                                        <input type="hidden" name="invoice_sub_type" value="1">
                                        <input type="hidden" name="shipping_method_id" value="4">
                                        <input type="hidden" name="take_time" value="2021-07-01">
                                        <input type="hidden" name="user_address_id" value="34360">
                                        <input type="hidden" name="user_memo" value="金流測試">
                                        <button type="submit" class="btn-block btn btn-success">智付通ATM</button>
                                    </form>
                                </div>
                                <div class="mb-2 col-3">
                                    <form action="https://dev-api.icarry.me/web/v1/order" method="POST">
                                        <input type="hidden" name="domain" value="icarry.me">
                                        <input type="hidden" name="from_country_id" value="1">
                                        <input type="hidden" name="to_country_id" value="1">
                                        <input type="hidden" name="create_type" value="web">
                                        <input type="hidden" name="pay_method" value="智付通CVS">
                                        <input type="hidden" name="buyer_name" value="Roger Wu">
                                        <input type="hidden" name="buyer_email" value="weayzehoa@gmail.com">
                                        <input type="hidden" name="invoice_sub_type" value="1">
                                        <input type="hidden" name="shipping_method_id" value="4">
                                        <input type="hidden" name="take_time" value="2021-07-01">
                                        <input type="hidden" name="user_address_id" value="34360">
                                        <input type="hidden" name="user_memo" value="金流測試">
                                        <button type="submit" class="btn-block btn btn-success">智付通超商代碼繳款</button>
                                    </form>
                                </div>
                                {{-- <div class="mb-2 col-2">
                                    <a href="{{ route('payTest.index', ['pay_method' => '智付通信用卡']) }}" class="btn btn-primary btn-block">智付通信用卡</a>
                                </div>
                                <div class="mb-2 col-2">
                                    <a href="{{ route('payTest.index', ['pay_method' => '智付通ATM']) }}" class="btn btn-primary btn-block">智付通ATM轉帳</a>
                                </div>
                                <div class="mb-2 col-3">
                                    <a href="{{ route('payTest.index', ['pay_method' => '智付通CVS']) }}" class="btn btn-primary btn-block">智付通超商代碼繳款</a>
                                </div> --}}
                                {{-- <div class="mb-2 col-2">
                                    <a href="{{ route('payTest.index', ['pay_method' => '付款方式']) }}" class="btn btn-danger btn-block">付款方式錯誤</a>
                                </div>
                                <form class="mb-2 col-4" action="{{ route('newebpayCancel') }}" method="GET">
                                    <div class="input-group">
                                        <input class="form-control" type="text" name="order_number" placeholder="輸入訂單號碼取消智付通信用卡交易">
                                        <div class="input-group-prepend">
                                            <button type="submit" class="btn btn-danger">取消</button>
                                        </div>
                                    </div>
                                </form> --}}
                            </div>
                        </div>
                    </div>
                    @if(!empty($message))
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center">返回訊息</h3>
                            <div class="row mt-2">
                                <div class="mb-2 col-12 text-center">
                                    {{ $message }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(!empty($order))
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center">付款完成返回訂單訊息</h3>
                            <div class="row mt-2">
                                <div class="mb-2 col-12 text-center">
                                    訂單ID : {{ $order->id }}<br>
                                    訂單號碼 : {{ $order->order_number }}<br>
                                    訂單狀態 : {{ $order->status }}<br>
                                    付款方式 : {{ $order->pay_method }}<br>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
@endsection

@section('script')
@endsection

@section('JsValidator')
@endsection

@section('CustomScript')
@endsection

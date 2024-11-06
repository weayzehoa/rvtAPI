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

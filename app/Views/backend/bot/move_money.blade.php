@extends('layouts.backend.default')
@section('content')
    @php
        $walletAmount = \Illuminate\Support\Arr::get($balance, 'usdt_available_balance');
        $liveAmount = \Illuminate\Support\Arr::get($balance, 'available_balance');
    @endphp
    <div class="col-md-6 mx-auto">
        <div class="row m-0">
            <div class="col-12  text-center">
                <img height="50" src="{{ public_url('images/backend/logo.svg') }}" alt="Aresbo" style="height: 50px;">
            </div>
        </div>
    </div>
    <div class="move-money-wrap">
        <div class="col-md-6 mx-auto move-money">
            <form method="post" data-action="{{ route('bot.move.money.valid') }}" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
                <div class="row m-0 move-money-header">
                    <div class="col-6 left-header">
                        <div class="col-12 text-center">
                            <span class="font-14"><span class="text-danger">USDT</span>&nbsp;Wallet</span>
                        </div>
                        <div class="col-12 text-center">
                            <span class="font-26 font-weight-700 amount" data-amount="{{ intval($walletAmount * 100) / 100 }}" id="wallet-amount">{{ number_format(intval($walletAmount * 100) / 100, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-6 right-header">
                        <div class="col-12 text-center">
                            <span class="font-14">Tài khoản Thực</span>
                        </div>
                        <div class="col-12 text-center">
                            <span class="font-26 font-weight-700 amount" data-amount="{{ intval($liveAmount * 100) / 100 }}" id="live-amount">{{ number_format(intval($liveAmount * 100) / 100, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="row m-0 move-money-body">
                    <div class="col-md-8 col-12 p-2 mx-auto text-center"><span class="changeAmount pointer" onclick="BotController.changeAmount()"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="iconTransfer">
                        <g id="Group_10897" data-name="Group 10897" transform="translate(-559.431 -202.553)">
                            <g id="Group_4613" data-name="Group 4613" transform="translate(559.431 202.553)">
                                <g id="Rectangle_2919" data-name="Rectangle 2919" transform="translate(0)" fill="#e9f0fa" stroke="#e5e5e5" stroke-width="1">
                                    <rect width="30" height="30" rx="15" stroke="none"></rect>
                                    <rect x="0.5" y="0.5" width="29" height="29" rx="14.5" fill="none"></rect>
                                </g>
                            </g>
                            <g id="conversion" transform="translate(567.976 210.905)">
                                <path id="Path_13963" data-name="Path 13963" d="M13.474,6.51H1V5.376H12.1l-3.4-3.4.8-.8,4.37,4.37a.567.567,0,0,1-.4.968Z" transform="translate(-1 -1.172)" fill="#031219"></path>
                                <path id="Path_13964" data-name="Path 13964" d="M5.536,33.338l-4.37-4.37a.567.567,0,0,1,.4-.968H14.041v1.134H2.936l3.4,3.4Z" transform="translate(-1 -20.395)" fill="#031219"></path>
                            </g>
                        </g>
                    </svg></span></div>
                    <div class="col-md-8 col-12 mx-auto">
                        <div class="input-group">
                            <input type="number" name="number" class="form-control" id="number" placeholder="Nhập số tiền">
                            <span class="input-group-text text-danger-custom all-amount" onclick="BotController.moveAllMoney()"><span>Tất cả</span></span>
                        </div>
                    </div>
                    <div class="col-md-8 col-12 mt-4 mx-auto">
                        <input type="hidden" name="move_type" id="move_type" value="1">
                        <button type="button" class="btn btn-danger col-12" onclick="BotController.moveMoney()">Chuyển Tiền</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@push('scripts')
    <script type="application/javascript">
        $(document).ready(function () {

        });
    </script>
@endpush

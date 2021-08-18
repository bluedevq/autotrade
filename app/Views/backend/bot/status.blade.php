<div class="mt-2">
    <h3>Chọn tài khoản</h3>
    <div class="row">
        <div class="col-md-6 col-12">
            <form method="POST" class="form-horizontal start-auto" enctype="multipart/form-data" show-loading="1">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <select onchange="BotController.changeAccountBalance(this)" class="form-select bot-account {{ $isRunning ? 'disabled' : '' }}" name="account_type" {{ $isRunning ? 'disabled' : '' }}>
                            <option value="{{ $demoType }}" {{ $isRunning && $accountType ==  $demoType ? 'selected="selected' : ''}}>DEMO</option>
                            <option value="{{ $liveType }}" {{ $isRunning && $accountType ==  $liveType ? 'selected="selected' : ''}}>LIVE</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-{{ $isRunning ? 'danger' : 'success' }} btn-block col-12 bot-status-btn" type="button" onclick="BotController.startAuto()" {{ $userExpired && $userExpired->greaterThanOrEqualTo(\Carbon\Carbon::now()) ? '' : 'disabled' }}>
                            <span class="fas fa-{{ $isRunning ? 'stop' : 'play' }}-circle bot-status-icon" aria-hidden="true">&nbsp;</span><span class="bot-status-text">{{ $isRunning ? 'Dừng' : 'Chạy' }}</span> Auto
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6 col-12 mt-sp-2 mx-auto text-center setting-profit">
            <div class="bg-secondary">
                <div class="col-2 stop-loss">{!! $botQueue->getStopLoss() !!}</div>
                <div class="col-4 stop-loss-btn" onclick="BotController.profitSettingForm()"><span class="setting-profit-text">Cắt lỗ</span><span class="percent stop-loss-percent"></span></div>
                <div class="col-4 take-profit-btn" onclick="BotController.profitSettingForm()"><span class="setting-profit-text">Chốt lãi</span><span class="percent take-profit-percent"></span></div>
                <div class="col-2 take-profit">{!! $botQueue->getTakeProfit() !!}</div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="mx-auto col-12 mt-2 position-relative">
            <h6 id="clock-title" class="text-center clock-title">{{ date('s') < 30 ? 'Có thể đặt lệnh' : 'Đang chờ kết quả' }}</h6>
            <h3 id="clock-countdown" class="text-center clock text-info"></h3>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mx-auto mt-sp-2">
            <ul class="list-inline list-prices">
                @if(!blank($resultCandles))
                @foreach($resultCandles as $resultCandle)
                    @php $title = date('H:i d-m-Y', \Illuminate\Support\Arr::get($resultCandle, 'open_order') / 1000); @endphp
                    <li class="list-inline-item" data-time="{{ \Illuminate\Support\Arr::get($resultCandle, 'open_order') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $title }}"><span class="candle-item fas fa-circle candle-{{ \Illuminate\Support\Arr::get($resultCandle, 'order_result') == 'T' ? 'success' : 'danger' }}">&nbsp;</span></li>
                @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>

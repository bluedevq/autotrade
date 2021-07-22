<div class="mt-2">
    <div class="row">
        <div class="col-md-6 col-12">
            <form method="post" action="{{ $isRunning ? route('bot.stop_auto') : route('bot.start_auto') }}" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
                @csrf
                <h3>Chọn tài khoản</h3>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <select onchange="BotController.changeAccountBalance(this)" class="form-select form-select-lg {{ $isRunning ? 'disabled' : '' }}" name="account_type" {{ $isRunning ? 'disabled' : '' }}>
                            <option value="{{ $demoType }}" {{ $isRunning && $accountType ==  $demoType ? 'selected="selected' : ''}}>DEMO</option>
                            <option value="{{ $liveType }}" {{ $isRunning && $accountType ==  $liveType ? 'selected="selected' : ''}}>LIVE</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-12 mt-sp-2">
                        <button class="btn btn-lg btn-{{ $isRunning ? 'danger' : 'success' }} btn-block col-12" type="submit">
                            <span class="fas fa-{{ $isRunning ? 'stop' : 'play' }}-circle" aria-hidden="true">&nbsp;</span>{{ $isRunning ? 'Dừng' : 'Chạy' }} Auto
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6 col-12 mt-sp-2">
            <div class="mx-auto col-12 position-relative">
                <h6 id="clock-title" class="text-center clock-title">{{ date('s') < 30 ? 'Có thể đặt lệnh' : 'Đang chờ kết quả' }}</h6>
                <h3 id="clock-countdown" class="text-center clock text-info" onload="showTime()"></h3>
            </div>
        </div>
    </div>
</div>
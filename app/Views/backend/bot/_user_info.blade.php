<div class="container p-2 border border-1 rounded-3" style="border-color: #303539">
    <div class="row pt-2">
        <div class="col-md-4 col-12">
            <a href="{{ route('bot.clear_token') }}" class="btn btn-secondary float-md-start float-sm-end">
                <span class="fas fa-sign-out-alt" aria-hidden="true">&nbsp;</span>Đăng xuất
            </a>
        </div>
    </div>
    <div class="mt-2 text-break">
        <h3 class="text-center">Thông tin tài khoản</h3>
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-user">&nbsp;&nbsp;</i>Biệt danh</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold">{{ $userInfo->nick_name }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-user-friends">&nbsp;</i>Người giới thiệu</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold">{{ $userInfo->reference_name }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-medal">&nbsp;&nbsp;</i>Cấp bậc</label>
                    </div>
                    <div class="col-6 col-md-8">
                        @php $rank = isset($userInfo) ? $userInfo->rank : 0 @endphp
                        <span class="fw-bold text-warning">{{ $rank }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-clock">&nbsp;&nbsp;</i>Hết hạn</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold text-danger">Dùng thử</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-funnel-dollar">&nbsp;</i>Số dư hiện tại</label>
                    </div>
                    <div class="col-6 col-md-8">
                        @php $liveAccount = isset($botQueue) && $botQueue && $botQueue->account_type == \App\Helper\Common::getConfig('aresbo.account_live') ? true: false; @endphp
                        <div class="account-balance demo-balance {{ $liveAccount ? 'hide' : ''}}">
                            <i class="fas fa-dollar-sign">&nbsp;</i><span class="current-amount">{{ $userInfo->demo_balance > 0 ? number_format($userInfo->demo_balance, 2) : 0 }}</span>
                        </div>
                        <div class="account-balance live-balance {{ $liveAccount ? '' : 'hide'}}">
                            <i class="fas fa-dollar-sign">&nbsp;</i><span class="current-amount">{{ $userInfo->available_balance > 0 ? number_format($userInfo->available_balance, 2) : 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-hand-holding-usd">&nbsp;</i>Tổng lãi</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <i class="fas fa-dollar-sign">&nbsp;</i><span class="profit">0</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-donate">&nbsp;</i>Tổng giao dịch</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <i class="fas fa-dollar-sign">&nbsp;</i><span class="volume">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="mt-2">
        <div class="row">
            <div class="col-md-6 col-12">
                @php
                    $accountType = isset($botQueue) && $botQueue ? $botQueue->account_type : 0;
                    $demoType = \App\Helper\Common::getConfig('aresbo.account_demo');
                    $liveType = \App\Helper\Common::getConfig('aresbo.account_live');
                    $isRunning = isset($botQueue) && $botQueue->status ? true : false;
                @endphp
                <form method="post" action="{{ $isRunning ? route('bot.stop_auto') : route('bot.start_auto') }}" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    <h3>Chọn tài khoản để chạy auto</h3>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <select onchange="BotController.changeAccountBalance(this)" class="form-select form-select-lg {{ $isRunning ? 'disabled' : '' }}" name="account_type" {{ $isRunning ? 'disabled' : '' }}>
                                <option value="{{ $demoType }}" {{ $isRunning && $accountType ==  $demoType ? 'selected="selected' : ''}}>DEMO</option>
                                <option value="{{ $liveType }}" {{ $isRunning && $accountType ==  $liveType ? 'selected="selected' : ''}}>LIVE</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <button class="btn btn-lg btn-{{ $isRunning ? 'danger' : 'success' }} btn-block col-12" type="submit">
                                <span class="fas fa-{{ $isRunning ? 'stop' : 'play' }}-circle" aria-hidden="true">&nbsp;</span>{{ $isRunning ? 'Dừng' : 'Chạy' }} Auto
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 col-12">
                <div class="mx-auto col-12 position-relative">
                    <h6 id="clock-title" class="text-center clock-title">{{ date('s') < 30 ? 'Có thể đặt lệnh' : 'Đang chờ kết quả' }}</h6>
                    <h3 id="clock-countdown" class="text-center clock text-info" onload="showTime()"></h3>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="mt-2">
        <h3>Lịch sử lệnh đặt</h3>
        <div class="row">
            <div class="col-12 bet-result-scroll">
                <table class="table-bordered text-center col-12">
                    <thead>
                    <th>Thời gian</th>
                    <th>Phương pháp</th>
                    <th>Lệnh</th>
                    <th>Tiền</th>
                    <th>Kết quả</th>
                    </thead>
                    <tbody class="bet-result">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">
    $(document).ready(function () {
        BotController.canBet = '{{ $isRunning ? 'true' : 'false' }}';
        BotController.betUrl = '{{ route('bot.bet') }}';
        BotController.showTime();
    });
</script>

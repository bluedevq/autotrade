<div class="mt-2 text-break">
    <h3>Thông tin tài khoản</h3>
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
                <div class="col-6 col-md-8 rank">
                    @php $rank = isset($userInfo) ? $userInfo->rank : 0 @endphp
                    <span class="fw-bold text-warning">{{ $rank }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-calendar-times">&nbsp;&nbsp;</i>Hết hạn</label>
                </div>
                <div class="col-6 col-md-8 expired-date">
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
                        <i class="fas fa-dollar-sign">&nbsp;</i><span class="current-amount">{{ $userInfo->demo_balance }}</span>
                    </div>
                    <div class="account-balance live-balance {{ $liveAccount ? '' : 'hide'}}">
                        <i class="fas fa-dollar-sign">&nbsp;</i><span class="current-amount">{{ $userInfo->available_balance }}</span>
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
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-clock">&nbsp;</i>Thời gian đã chạy</label>
                </div>
                <div class="col-6 col-md-8">
                    <span class="text-info total-time">00:00:00</span>
                </div>
            </div>
        </div>
    </div>
</div>

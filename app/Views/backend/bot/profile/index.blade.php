<div class="mt-2 text-break">
    <h3>Thông tin tài khoản</h3>
    <div class="row">
        <div class="col-md-6 col-12">
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-user">&nbsp;&nbsp;</i>Biệt danh</label>
                </div>
                <div class="col-6 col-md-8">
                    <span class="">{{ $botUserInfo->nick_name }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-user-friends">&nbsp;</i>Người giới thiệu</label>
                </div>
                <div class="col-6 col-md-8">
                    <span class="">{{ $botUserInfo->reference_name }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-medal">&nbsp;&nbsp;</i>Cấp bậc</label>
                </div>
                <div class="col-6 col-md-8 rank">
                    @php $rank = isset($botUserInfo) ? $botUserInfo->rank : 0 @endphp
                    <span class="text-warning">{{ $rank }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-calendar-times">&nbsp;&nbsp;</i>Hết hạn</label>
                </div>
                <div class="col-6 col-md-8 expired-date">
                    <span class="text-danger">{{ $userExpired && $userExpired->greaterThanOrEqualTo(\Carbon\Carbon::now()) ? $userExpired->format('d-m-Y') : 'Đã hết hạn' }}</span>
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
                        <i class="fas fa-dollar-sign">&nbsp;</i><span class="current-amount">{{ $botUserInfo->demo_balance > 0 ? number_format($botUserInfo->demo_balance, 2) : 0 }}</span>
                    </div>
                    <div class="account-balance live-balance {{ $liveAccount ? '' : 'hide'}}">
                        <i class="fas fa-dollar-sign">&nbsp;</i><span class="current-amount">{{ $botUserInfo->available_balance > 0 ? number_format($botUserInfo->available_balance, 2) : 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-4">
                    <label class="form-label" aria-hidden="true"><i class="fas fa-hand-holding-usd">&nbsp;</i>Lợi nhuận</label>
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
                    <i class="fas fa-dollar-sign">&nbsp;</i><span class="volume">0</span>&nbsp;<br class="sp">(~&nbsp;<i class="fas fa-dollar-sign">&nbsp;</i><span class="commission">0</span>&nbsp;<span class="text-danger-custom"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 49.009 55.129"><g id="savings" transform="translate(-4.441 -1)"><path id="Path_29852" data-name="Path 29852" d="M30.559,1A11.559,11.559,0,1,0,42.119,12.559,11.559,11.559,0,0,0,30.559,1Zm5.075,12.188-4.446,4.446a.889.889,0,0,1-1.257,0l-4.446-4.446a.889.889,0,0,1,0-1.257l4.446-4.446a.889.889,0,0,1,1.257,0l4.446,4.446A.889.889,0,0,1,35.634,13.188Z" transform="translate(-1.613)" fill="#ff3333"></path><path id="Path_29853" data-name="Path 29853" d="M19.367,49l1.459,11.67a.889.889,0,0,0,.882.779H39.492a.889.889,0,0,0,.882-.779L41.833,49Z" transform="translate(-1.654 -5.32)" fill="#ffffff"></path><path id="Path_29854" data-name="Path 29854" d="M53.149,32.773a15.086,15.086,0,0,0-23.314,4.239V28.95c-.3.019-.589.044-.889.044s-.594-.026-.889-.044v8.062A15.086,15.086,0,0,0,4.742,32.773a.889.889,0,0,0,0,1.334,15.056,15.056,0,0,0,19.339.513,13.3,13.3,0,0,1,3.93,8.6H18.276a.889.889,0,0,0-.882,1l.1.779H40.4l.1-.779a.889.889,0,0,0-.882-1H29.88a13.3,13.3,0,0,1,3.93-8.6,15.056,15.056,0,0,0,19.339-.513.889.889,0,0,0,0-1.334Z" transform="translate(0 -3.098)" fill="#33cc66"></path></g></svg></span>)
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

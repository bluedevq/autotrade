<div class="container p-2 border border-1 rounded-3" style="border-color: #303539">
    <div class="row pt-2">
        <div class="col-md-4 col-12">
            <a href="{{ route('bot.clear_token') }}" class="btn btn-secondary float-md-start float-sm-end">Đăng xuất</a>
        </div>
    </div>
    <div class="mt-2 text-break">
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-envelope">&nbsp;&nbsp;</i>Email</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold">{{ Arr::get($userInfo, 'e') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-user">&nbsp;&nbsp;</i>Biệt danh</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold">{{ Arr::get($userInfo, 'nn') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-user-friends">&nbsp;</i>Người giới thiệu</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold">{{ Arr::get($userInfo, 'sponsor') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-medal">&nbsp;&nbsp;</i>Cấp bậc</label>
                    </div>
                    <div class="col-6 col-md-8">
                        @php $rank = Arr::get($userInfo, 'rank') @endphp
                        @if($rank)
                            <span class="fw-bold" style="color: yellow">{{ $rank }}</span>
                        @else
                            <span class="fw-bold" style="color: red">Chưa kích hoạt đại lý</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <label class="form-label" aria-hidden="true"><i class="fas fa-money-bill-alt">&nbsp;</i>Thông tin ví</label>
                    </div>
                    <div class="col-6 col-md-8">
                        Tổng tài sản (USDT): <br class="sp"><i class="fas fa-dollar-sign">&nbsp;</i>{{ number_format(Arr::get($userInfo, 'usdtAvailableBalance'), 2) }}
                        <br>
                        Tài khoản Demo: <br class="sp"><i class="fas fa-dollar-sign">&nbsp;</i>{{ number_format(Arr::get($userInfo, 'demoBalance'), 2) }}
                        <br>
                        Tài khoản Thực: <br class="sp"><i class="fas fa-dollar-sign">&nbsp;</i>{{ number_format(Arr::get($userInfo, 'availableBalance'), 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

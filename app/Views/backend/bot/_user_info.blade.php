<div class="container mt-5" style="background-color: #171b2c;border-color: #303539;border-radius: 20px;color: #fff">
    <div class="row pt-2">
        <div class="col-md-4 col-sm-12">
            <a href="{{ route('bot.clear_token') }}" class="btn btn-secondary float-md-start float-sm-end">Đăng xuất</a>
        </div>
    </div>
    <div class="row mt-1 text-center col-md-12">
        <img height="50" src="{{ public_url('images/backend/logo.601a65eb.svg') }}" alt="Aresbo" style="height: 50px;">
        <h3 class="mt-5">Thông tin tài khoản AresBO của bạn</h3>
    </div>
    <div class="list-group mt-5">
        <div class="row col-md-12">
            <div class="col-md-3 col-sm-0">&nbsp;</div>
            <div class="col-md-3 col-sm-4">
                <label class="form-label" aria-hidden="true">Email</label>
            </div>
            <div class="col-md-6 col-sm-8">
                <span class="fw-bold">{{ Arr::get($userInfo, 'e') }}</span>
            </div>
        </div>

        <div class="row col-md-12">
            <div class="col-md-3 col-sm-0">&nbsp;</div>
            <div class="col-md-3 col-sm-4">
                <label class="form-label" aria-hidden="true">Biệt danh</label>
            </div>
            <div class="col-md-6 col-sm-8">
                <span class="fw-bold">{{ Arr::get($userInfo, 'nn') }}</span>
            </div>
        </div>

        <div class="row col-md-12 pb-2">
            <div class="col-md-3 col-sm-0">&nbsp;</div>
            <div class="col-md-3 col-sm-4">
                <label class="form-label" aria-hidden="true">Thông tin ví</label>
            </div>
            <div class="col-md-6 col-sm-8">
                Tổng tài sản (USDT): ${{ number_format(Arr::get($userInfo, 'usdtAvailableBalance'), 2) }}
                <br>
                Tài khoản Demo: ${{ number_format(Arr::get($userInfo, 'demoBalance'), 2) }}
                <br>
                Tài khoản Thực: ${{ number_format(Arr::get($userInfo, 'availableBalance'), 2) }}
            </div>
        </div>
    </div>
</div>

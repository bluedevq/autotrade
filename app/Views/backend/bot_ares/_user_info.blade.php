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
                        <label class="form-label" aria-hidden="true"><i class="fas fa-envelope">&nbsp;&nbsp;</i>Email</label>
                    </div>
                    <div class="col-6 col-md-8">
                        <span class="fw-bold">{{ $userInfo->email }}</span>
                    </div>
                </div>
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
                        @php $rank = $userInfo->rank @endphp
                        @if($rank)
                            <span class="fw-bold text-warning">{{ $rank }}</span>
                        @else
                            <span class="fw-bold text-danger">Chưa kích hoạt đại lý</span>
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
                        Tổng tài sản (USDT): <br class="sp"><i class="fas fa-dollar-sign">&nbsp;</i><span class="text-info">{{ number_format($userInfo->usdt_available_balance, 2) }}</span>
                        <br>
                        Tài khoản Demo: <br class="sp"><i class="fas fa-dollar-sign">&nbsp;</i><span class="text-info">{{ number_format($userInfo->demo_balance, 2) }}</span>
                        <br>
                        Tài khoản Live: <br class="sp"><i class="fas fa-dollar-sign">&nbsp;</i><span class="text-info">{{ number_format($userInfo->available_balance, 2) }}</span>
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
                    $liveType = \App\Helper\Common::getConfig('aresbo.live_demo');
                    $isRunning = isset($botQueue) && $botQueue->status ? true : false;
                @endphp
                <form method="post" action="{{ $isRunning ? route('bot.stop_auto') : route('bot.start_auto') }}" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    <h3>Chọn tài khoản để chạy auto</h3>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <select class="form-select form-select-lg {{ $isRunning ? 'disabled' : '' }}" name="account_type" {{ $isRunning ? 'disabled' : '' }}>
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
            <div class="col-12">
                <table class="table-bordered text-center col-12">
                    <thead>
                    <th width="100">Thời gian</th>
                    <th>Phương pháp</th>
                    <th>Lệnh</th>
                    <th>Tiền</th>
                    <th>Kết quả</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>00:02</td>
                        <td>NBB_1</td>
                        <td>Giảm</td>
                        <td class="text-info"><span class="fas fa-dollar-sign"></span><span class="fw-bold">2</span></td>
                        <td class="text-success fw-bold">Win</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    localStorage.hasBet = false;

    function showTime() {
        var date = new Date(),
            s = date.getSeconds();

        // bet
        if (2 < s && s < 30) {
            if (!localStorage.hasBet) {
                console.log('auto bet');
                sendRequest({
                    url: '{{ route('bot.bet') }}',
                    type: 'POST',
                    data: {},
                    beforeSend: function () {},
                    complete: function () {}
                }, function (response) {
                    if (!response.status) {
                        window.location.href = '{{ route('bot.clear_token') }}';
                    }
                });
            }
            localStorage.hasBet = true;
        } else {
            localStorage.hasBet = false;
        }

        // show title
        document.getElementById('clock-title').innerText = (s < 30) ? 'Có thể đặt lệnh' : 'Đang chờ kết quả';
        document.getElementById('clock-title').textContent = (s < 30) ? 'Có thể đặt lệnh' : 'Đang chờ kết quả';

        // show time
        s = (s > 30) ? (60 - s) : (30 - s);
        s = (s == 30) ? "0" : s;
        s = (s < 10) ? "0" + s : s;
        document.getElementById('clock-countdown').innerText = s;
        document.getElementById('clock-countdown').textContent = s;
        setTimeout(showTime, 1000);
    }
</script>

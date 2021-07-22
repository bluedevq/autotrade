@extends('layouts.backend.default')
@section('content')
    <div class="wrapper">
        @include('layouts.backend.elements.messages')
        @if(isset($require2Fa) && $require2Fa)
            @include('backend.bot.login.2fa')
        @else
            @if(!isset($userInfo) || blank($userInfo))
                @include('backend.bot.login.index')
            @else
                <div class="container mt-2 border border-1 rounded-3" style="border-color: #303539">
                    @php
                        $accountType = isset($botQueue) && $botQueue ? $botQueue->account_type : 0;
                        $demoType = \App\Helper\Common::getConfig('aresbo.account_demo');
                        $liveType = \App\Helper\Common::getConfig('aresbo.account_live');
                        $isRunning = isset($botQueue) && $botQueue->status ? true : false;
                    @endphp
                    <div class="row mt-2">
                        <div class="col-md-2 col-12">
                            <a href="{{ route('bot.clear_token') }}" class="btn btn-secondary"><span class="fas fa-sign-out-alt" aria-hidden="true">&nbsp;</span>Thoát</a>
                        </div>
                    </div>
                    <div class="row pt-2 text-center col-12">
                        <img height="50" src="{{ public_url('images/backend/logo.svg') }}" alt="Aresbo" style="height: 50px;">
                    </div>
                    <hr>
                    @include('backend.bot.profile.index')
                    <hr>
                    @include('backend.bot.status')
                    <hr>
                    @include('backend.bot.method.index')
                    @include('backend.bot.history')
                    @include('backend.bot.method.modal')
                    <script type="application/javascript">
                        $(document).ready(function () {
                            BotController.canBet = '{{ $isRunning ? 'true' : 'false' }}';
                            BotController.betUrl = '{{ route('bot.bet') }}';
                            BotController.researchUrl = '{{ route('bot_method.research') }}';
                            BotController.showTime();
                        });
                    </script>
                </div>
            @endif
        @endif
    </div>
@stop

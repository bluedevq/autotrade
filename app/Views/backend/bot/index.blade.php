@extends('layouts.backend.default')
@section('content')
    @php
        $accountType = isset($botQueue) && $botQueue ? $botQueue->account_type : 0;
        $demoType = \App\Helper\Common::getConfig('aresbo.account_demo');
        $liveType = \App\Helper\Common::getConfig('aresbo.account_live');
        $isRunning = isset($botQueue) && $botQueue->status ? true : false;
        $userExpired = \Carbon\Carbon::parse(backendGuard()->user()->expired_date);
    @endphp
    @if(!isset($botUserInfo) || blank($botUserInfo))
        @include('backend.bot.login.index')
        @include('backend.bot.login.2fa')
    @else
        <div class="bot-container">
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
            @include('backend.bot.modal')
        </div>
    @endif
@stop
@push('scripts')
    <script type="application/javascript">
        $(document).ready(function () {
            BotController.options.isRunning = '{{ $isRunning ? 'true' : 'false' }}';
            BotController.config.url.login = '{{ route('bot.login') }}';
            BotController.config.url.requestCode = '{{ route('bot.request.code') }}';
            BotController.config.url.bet = '{{ route('bot.bet') }}';
            BotController.config.url.research = '{{ route('bot_method.research') }}';
            BotController.config.url.startAuto = '{{ route('bot.start_auto') }}';
            BotController.config.url.stopAuto = '{{ route('bot.stop_auto') }}';
            BotController.config.url.statusMethod = '{{ route('bot.method.update.status') }}';
            @if($isRunning)
            BotController.config.startAt = '{{ time() * 1000 }}';
            @endif

            // show clock
            @if(isset($botUserInfo) && !blank($botUserInfo))
            BotController.showTime();
            @endif

            // scroll prices
            $('.list-prices').scrollLeft($('.list-prices').width())
        });
    </script>
@endpush

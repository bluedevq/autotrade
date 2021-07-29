@extends('layouts.backend.register')
@section('content')
    <div class="row text-center mx-auto col-md-6 mt-4">
        <img class="img-fluid img-logo col-12" src="{{ public_url('images/backend/logo.png') }}" alt="">
    </div>
    @if(isset($verify))
        @include('backend.register.verify.' . $verify)
    @endif
@stop
@push('scripts')
    <script type="application/javascript">
        BotController.verifyCount(5, '{{ route('backend.login') }}');
    </script>
@endpush

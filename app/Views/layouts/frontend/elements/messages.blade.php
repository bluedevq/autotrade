@if(session()->has('success'))
    <div class="mt-2">
        @php
            $successMsg = Session()->get('success');
        @endphp
        <ul class="list-group">
            @foreach($successMsg as $msg)
                <li class="list-group-item alert-success text-success"><i class="fas fa-check">&nbsp;</i><strong>{{$msg}}</strong></li>
            @endforeach
        </ul>
    </div>
@endif

@if (!empty($errors) && count($errors) > 0)
    <div class="mt-2">
        <ul class="list-group">
            @foreach ($errors->all() as $error)
                <li class="list-group-item alert-danger text-danger"><i class="fas fa-exclamation-triangle">&nbsp;</i>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

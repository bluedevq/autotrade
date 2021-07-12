@if(Session()->has('success'))
    <div class="col-6 mx-auto">
        @php
            $successMsg = (array)Session()->get('success')
        @endphp
        <ul class="list-group-item-success">
            @foreach($successMsg as $msg)
                <li>
                    <i class="fa fa-check"></i>
                    <strong>{{$msg}}</strong>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if (!empty($errors) && count($errors) > 0)
    <div class="col-6 mx-auto">
        <ul class="list-group-item-danger">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

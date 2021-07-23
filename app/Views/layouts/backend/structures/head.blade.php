<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title : '' }}</title>
    <meta name="description" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ public_url('favicon.png') }}">
    <?php $cssFiles = [
        'lib/bootstrap.min',
        'lib/bootstrap-theme.min',
        'lib/fontawesome.min',
        'lib/solid',
        'backend/custom',
    ];
    $jsFiles = [
        'lib/jquery.min',
        'lib/bootstrap.min',
        'lib/bootstrap.bundle.min',
        'lib/loadingoverlay.min',
        'lib/fontawesome.min',
        'lib/chart.min',
    ];
    ?>
    {!! loadFiles($jsFiles, '', 'js') !!}
    {!! loadFiles($cssFiles) !!}
    @include('layouts.backend.structures.head_autoload')
    <!--[if lt IE 9]>
    {{Html::script('https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js')}}
    {{Html::script('https://oss.maxcdn.com/respond/1.4.2/respond.min.js')}}
    <![endif]-->
</head>

<?php
$jsFiles = [
    'common/min',
    'common/xhr',
    'common/common',
    'backend/custom',
];
?>
{!! loadFiles($jsFiles, '', 'js') !!}
@include('layouts.backend.structures.footer_autoload')

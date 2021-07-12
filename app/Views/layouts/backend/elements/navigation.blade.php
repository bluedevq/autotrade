<div class="col-12">
    <a href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();">Logout</a>
</div>
<form id="form-logout" method="post" action="{{ route('backend.logout') }}" class="form-horizontal" enctype="multipart/form-data">
    @csrf
</form>

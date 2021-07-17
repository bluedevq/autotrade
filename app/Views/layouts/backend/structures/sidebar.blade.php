<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation
						</span>
                <span class="icon-bar">
						</span>
                <span class="icon-bar">
						</span>
                <span class="icon-bar">
						</span>
            </button>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="{{route('admin.edit', frontendGuard()->user()->getKey())}}">
                        <i class="ls-icon-user"></i> {{frontendGuard()->user()->email}}
                    </a>
                </li>
                <li>
                <a href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();">
                    <i class="ls-icon-logout"></i>ログアウト
                </a>
                </li>
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</nav>
{{MyForm::open(['route'=>'logout','class'=>'form-logout', 'id' => 'form-logout'])}}
{!! MyForm::close() !!}

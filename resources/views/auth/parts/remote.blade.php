@if(env('REMOTE_AUTH_LOGIN_URL', false))
	<form action="{{ str_replace('%url%', \URL::full(), env('REMOTE_AUTH_LOGIN_URL')) }}" method="GET" id="login-form" class="mt-l">
	    <div>
	        <button id="saml-login" class="button outline block svg">
	            <span>{{ Str::title(trans('auth.log_in')) }}</span>
	        </button>
	    </div>
	</form>
@endif

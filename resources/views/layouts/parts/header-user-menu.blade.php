<div class="dropdown-container" component="dropdown" option:dropdown:bubble-escapes="true">
    <span class="user-name py-s hide-under-l" refs="dropdown@toggle"
          aria-haspopup="true" aria-expanded="false" aria-label="{{ trans('common.profile_menu') }}" tabindex="0">
        <img class="avatar" src="{{$user->getAvatar(30)}}" alt="{{ $user->name }}">
        <span class="name">{{ $user->getShortName(9) }}</span> @icon('caret-down')
    </span>
    <ul refs="dropdown@menu" class="dropdown-menu" role="menu">
        <li>
            <a href="{{ url('/favourites') }}" data-shortcut="favourites_view" class="icon-item">
                @icon('star')
                <div>{{ trans('entities.my_favourites') }}</div>
            </a>
        </li>
        <li>
            <a href="{{ $user->getProfileUrl() }}" data-shortcut="profile_view" class="icon-item">
                @icon('user')
                <div>{{ trans('common.view_profile') }}</div>
            </a>
        </li>
        <li>
            <a href="{{ url('/my-account') }}" class="icon-item">
                @icon('user-preferences')
                <div>{{ trans('preferences.my_account') }}</div>
            </a>
        </li>
        <li><hr></li>
        <li>
            @include('common.dark-mode-toggle', ['classes' => 'icon-item'])
        </li>
        <li><hr></li>
        <li>
            @php
                $logoutPath = match (config('auth.method')) {
                    'saml2' => '/saml2/logout',
                    'oidc' => '/oidc/logout',
                    'remote' => env('REMOTE_AUTH_LOGOUT_URL', '/logout'),
                    default => '/logout',
                };
				$label = trans('auth.logout');
				$csrf = csrf_field();

				if(config('auth.method') === 'remote') {
					$label = env('REMOTE_AUTH_LOGOUT_LABEL', trans('auth.logout'));
					$csrf = '';
				}
            @endphp

            @if(config('auth.method') === 'remote' && env('REMOTE_AUTH_LOGOUT_METHOD', 'post') === 'get')
                <a href="{{ $logoutPath }}" class="icon-item" data-shortcut="logout">
                    @icon('logout')
                    <div>{{ $label }}</div>
                </a>
            @else
                <form action="{{ $logoutPath }}" method="post" data-shortcut="logout">
                    {{ $csrf }}
                    <button class="icon-item">
                        @icon('logout')
                        <div>{{ $label }}</div>
                    </button>
                </form>
            @endif
        </li>
    </ul>
</div>

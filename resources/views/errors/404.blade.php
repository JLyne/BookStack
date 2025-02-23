@extends('layouts.simple')
@inject('popular', \BookStack\Entities\Queries\QueryPopular::class)
@section('content')
    <div class="container mt-l">

        <div class="card mb-xl px-l pb-l pt-l">
            <div class="grid half v-center">
                <div>
                    @include('errors.parts.not-found-text', [
                        'title' => $message ?? trans('errors.404_page_not_found'),
                        'subtitle' => $subtitle ?? trans('errors.sorry_page_not_found'),
                        'details' => $details ?? trans('errors.sorry_page_not_found_permission_warning'),
                    ])
                </div>
                <div class="text-right">
                    @if(user()->isGuest())
                        @if(config('auth.method') === 'remote')
	                        @if(env('REMOTE_AUTH_LOGIN_URL', false))
	                            <a href="{{ str_replace('%url%', \URL::full(), env('REMOTE_AUTH_LOGIN_URL'))  }}">@icon('login'){{ env('REMOTE_AUTH_LOGIN_LABEL', trans('auth.log_in')) }}</a>
	                        @endif
	                    @else
	                        <a href="{{ url('/login') }}" class="button outline">{{ trans('auth.log_in') }}</a>
	                    @endif
                    @endif
                    <a href="{{ url('/') }}" class="button outline">{{ trans('errors.return_home') }}</a>
                </div>
            </div>

        </div>

        @if (setting('app-public') || !user()->isGuest())
            <div class="grid third gap-xxl">
                <div>
                    <div class="card mb-xl">
                        <h3 class="card-title">{{ trans('entities.pages_popular') }}</h3>
                        <div class="px-m">
                            @include('entities.list', ['entities' => $popular->run(10, 0, ['page']), 'style' => 'compact'])
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card mb-xl">
                        <h3 class="card-title">{{ trans('entities.books_popular') }}</h3>
                        <div class="px-m">
                            @include('entities.list', ['entities' => $popular->run(10, 0, ['book']), 'style' => 'compact'])
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card mb-xl">
                        <h3 class="card-title">{{ trans('entities.chapters_popular') }}</h3>
                        <div class="px-m">
                            @include('entities.list', ['entities' => $popular->run(10, 0, ['chapter']), 'style' => 'compact'])
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@stop

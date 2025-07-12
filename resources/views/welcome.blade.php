@extends('layouts.app')

@section('content')
<div class="relative isolate px-6 pt-14 lg:px-8">
    <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
        <div class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                {{ config('app.name', 'Gist Manager') }}
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600">
                {{ __('common.messages.app_description') }}
            </p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ __('common.navigation.dashboard') }}
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ __('common.actions.get_started') }}
                    </a>
                    <a href="{{ route('login') }}"
                       class="text-sm font-semibold leading-6 text-gray-900">
                        {{ __('common.navigation.login') }} <span aria-hidden="true">→</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', '2 Factor Challenge')

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg authentication-basic px-4">
        <div class="authentication-inner py-4">

            <!-- Two Steps Verification -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="mt-4"><img src="{{ asset('assets\img\logo\bbox-express-logo.png') }}"
                                    alt="logo" width="45" height="45"
                                    style="margin-top: -25px; margin-left: -25px" /></span>
                            <span
                                class="app-brand-text demo text-body fw-bold ms-1">{{ config('variables.templateName') }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h3 class="mb-1">Two Step Verification 💬</h3>
                    <div x-data="{ recovery: false }">
                        <div class="mb-3" x-show="! recovery">
                            Please confirm access to your account by entering the authentication code provided by your
                            Google Authenticator application.
                        </div>

                        <div class="mb-3" x-show="recovery">
                            Please confirm access to your account by entering one of your emergency recovery codes.
                        </div>

                        <x-validation-errors class="mb-1" />

                        <form method="POST" action="{{ route('two-factor.login') }}">
                            @csrf

                            <div class="mb-3" x-show="! recovery">
                                <x-label class="form-label" value="{{ __('Code') }}" />
                                <x-input class="{{ $errors->has('code') ? 'is-invalid' : '' }}" type="text"
                                    inputmode="numeric" name="code" autofocus x-ref="code"
                                    autocomplete="one-time-code" />
                                <x-input-error for="code"></x-input-error>
                            </div>

                            <div class="mb-3" x-show="recovery">
                                <x-label class="form-label" value="{{ __('Recovery Code') }}" />
                                <x-input class="{{ $errors->has('recovery_code') ? 'is-invalid' : '' }}" type="text"
                                    name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                                <x-input-error for="recovery_code"></x-input-error>
                            </div>

                            <div class="d-flex justify-content-end my-2 gap-2">
                                <div x-show="! recovery"
                                    x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus()})">
                                    <button type="button" class="btn btn-outline-secondary me-1">Use a recovery
                                        code</button>
                                </div>
                                <div x-cloak x-show="recovery"
                                    x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                                    <button type="button" class="btn btn-outline-secondary me-1">Use an authentication
                                        code</button>
                                </div>
                                <x-button>Log in</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- / Two Steps Verification -->
        </div>
    </div>


@endsection

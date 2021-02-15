@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mx-auto" style="max-width: 400px">
        <div class="card-header">{{ __('Login') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username"> @lang("application.username") </label>
                    <input
                            id="username"
                            type="text"
                            placeholder="@lang("application.username")"
                            class="form-control @error("username") is-invalid @enderror"
                            name="username"
                            value="{{ old("username") }}"
                    />
                    @error("username")
                    <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password"> @lang("application.password") </label>
                    <input
                            id="password"
                            type="password"
                            placeholder="@lang("application.password")"
                            class="form-control @error("password") is-invalid @enderror"
                            name="password"
                            value="{{ old("password") }}"
                    />
                    @error("password")
                    <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                    @enderror
                </div>

                <div class="form-group d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

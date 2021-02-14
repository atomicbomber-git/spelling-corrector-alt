@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="username"> Nama Pengguna:</label>
                            <input
                                id="username"
                                type="text"
                                placeholder="Nama Pengguna"
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
                            <label for="password"> Kata Sandi:</label>
                            <input
                                id="password"
                                type="password"
                                placeholder="Kata Sandi"
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
    </div>
</div>
@endsection

@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        <a href="{{ route("mahasiswa.index") }}">
            @lang("application.mahasiswa")
        </a>

        /

        @lang("application.edit")
    </h1>

    <x-messages></x-messages>

    <div class="card">
        <div class="card-body">
            <form action="{{ route("mahasiswa.update", $mahasiswa) }}"
                  method="POST"
            >
                @csrf
                @method("PATCH")

                <div class="form-group">
                    <label for="name"> @lang("application.real_name") </label>
                    <input
                            id="name"
                            type="text"
                            placeholder="@lang("application.real_name")"
                            class="form-control @error("name") is-invalid @enderror"
                            name="name"
                            value="{{ old("name", $mahasiswa->name) }}"
                    />
                    @error("name")
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nomor_induk"> @lang("application.student_id") </label>
                    <input
                            id="nomor_induk"
                            type="text"
                            placeholder="@lang("application.student_id")"
                            class="form-control @error("nomor_induk") is-invalid @enderror"
                            name="nomor_induk"
                            value="{{ old("nomor_induk", $mahasiswa->nomor_induk) }}"
                    />
                    @error("nomor_induk")
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username"> @lang("application.username") </label>
                    <input
                            id="username"
                            type="text"
                            placeholder="@lang("application.username")"
                            class="form-control @error("username") is-invalid @enderror"
                            name="username"
                            value="{{ old("username", $mahasiswa->username) }}"
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

                <div class="form-group">
                    <label for="password_confirmation"> @lang("application.password_confirmation") </label>
                    <input
                            id="password_confirmation"
                            type="password"
                            placeholder="@lang("application.password_confirmation")"
                            class="form-control @error("password_confirmation") is-invalid @enderror"
                            name="password_confirmation"
                            value="{{ old("password_confirmation") }}"
                    />
                    @error("password_confirmation")
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    Kosongkan kolom <strong> @lang("application.password") </strong> jika Anda tidak
                    ingin merevisi data tersebut.
                </div>


                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary">
                        @lang("application.update")
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
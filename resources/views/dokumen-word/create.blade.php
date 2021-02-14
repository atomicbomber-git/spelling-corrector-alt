@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        <a href="{{ route("dokumen-word.index") }}">
            @lang("application.dokumen_word")
        </a>

        /

        @lang("application.create")
    </h1>

    <div class="card">
        <div class="card-body">
            <form
                    enctype="multipart/form-data"
                    action="{{ route("dokumen-word.store") }}"
                    method="POST"
            >
                @csrf
                @method("POST")

                <div class="form-group">
                    <label for="nama"> @lang("application.name") </label>
                    <input
                            id="nama"
                            type="text"
                            placeholder="@lang("application.name")"
                            class="form-control @error("nama") is-invalid @enderror"
                            name="nama"
                            value="{{ old("nama") }}"
                    />
                    @error("nama")
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="berkas"> @lang("application.file") </label>
                    <input
                            id="berkas"
                            type="file"
                            placeholder="@lang("application.file")"
                            class="form-control-file @error("berkas") is-invalid @enderror"
                            name="berkas"
                            aria-describedby="fileHelpId"
                    />
                    @error("berkas")
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                    @enderror
                    <small id="fileHelpId"
                           class="form-text text-muted"
                    >
                        @lang("application.word_upload_help_text")
                    </small>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary">
                        @lang("application.create")
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
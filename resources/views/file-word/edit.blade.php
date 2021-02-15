@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        <a href="{{ route("file-word.index") }}">
            @lang("application.file_word")
        </a>

        /

        @lang("application.edit")
    </h1>

    <div class="card">
        <div class="card-body">
            <form
                    enctype="multipart/form-data"
                    action="{{ route("file-word.update", $file_word) }}"
                    method="POST"
            >
                @csrf
                @method("PATCH")

                <div class="form-group">
                    <label for="nama"> @lang("application.name") </label>
                    <input
                            id="nama"
                            type="text"
                            placeholder="@lang("application.name")"
                            class="form-control @error("nama") is-invalid @enderror"
                            name="nama"
                            value="{{ old("nama", $file_word->nama) }}"
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

                <div class="alert alert-warning">
                    <strong> Kosongkan </strong> kolom di atas jika Anda tidak ingin merevisi dokumen word.
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
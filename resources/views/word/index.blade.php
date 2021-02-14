@extends("layouts.app")

@section("content")
    <div class="container">
        <h1>
            Word List
        </h1>

        @include("layouts._messages")

        <div class="card my-3">
            <div class="card-header">
                Impor dari Dokumen
            </div>

            <div class="card-body">
                <form enctype="multipart/form-data" action="{{ route("import-words-from-document") }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="document"> Dokumen: </label>
                        <input
                            class="form-control"
                            name="document"
                            id="document"
                            type="file">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm">
                            Impor
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @foreach($words->chunk(15) AS $wordChunk)
                <div class="col-md-4">
                    @foreach($wordChunk AS $word)
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        {{ $word->isi }}
                                    </div>

                                    <form method="POST" action="{{ route("word.destroy", $word) }}">
                                        @method("DELETE")
                                        @csrf
                                        <button class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="alert alert-info my-3">
             Menampilkan data ke-<strong>{{ number_format($words->firstItem()) }}</strong>
            hingga data ke-<strong>{{ number_format($words->lastItem()) }}</strong>
            dari <strong>{{ number_format($words->total()) }}</strong> data yang ada.
        </div>

        <div class="d-flex justify-content-center my-2">
            {{ $words->links() }}
        </div>
    </div>
@endsection

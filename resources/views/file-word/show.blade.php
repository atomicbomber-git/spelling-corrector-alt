@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        <a href="{{ route("file-word.index") }}">
            @lang("application.file_word")
        </a>

        /

        @lang("application.show")
    </h1>

    <x-messages/>

    <div id="app">
        <h2> {{ $file_word->nama }} </h2>

        <dokumen-word-show
                data-url="{{ route("file-word.show", $file_word)}}"
                recommender-url="{{ route("rekomendasi-pembenaran") }}"
                corrector-url="{{ route("file-word.koreksi-ejaan", $file_word) }}"
        >

        </dokumen-word-show>
    </div>
@endsection
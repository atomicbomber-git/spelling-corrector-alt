@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        <a href="{{ route("dokumen-word.index") }}">
            @lang("application.dokumen_word")
        </a>

        /

        @lang("application.show")
    </h1>

    <x-messages/>

    <div id="app">
        <h2> {{ $dokumen_word->nama }} </h2>

        <dokumen-word-show
                data-url="{{ route("dokumen-word.show", $dokumen_word)}}"
                recommender-url="{{ route("rekomendasi-pembenaran") }}"
                corrector-url="{{ route("dokumen-word.koreksi-ejaan", $dokumen_word) }}"
        >

        </dokumen-word-show>
    </div>
@endsection
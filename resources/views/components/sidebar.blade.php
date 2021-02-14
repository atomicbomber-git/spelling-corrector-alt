<nav class="col-md-2 h5" style="font-size: 12pt">
    <span class="d-block font-weight-bolder text-dark mb-3 text-uppercase">
        @lang("application.menu")
    </span>

    <ul class="nav justify-content-start">
        @can(\App\Providers\AuthServiceProvider::MANAGE_MAHASISWA)
            <li class="nav-item {{ Route::is("mahasiswa.*") ? "active" : "" }}">
                <a class="nav-link pt-0"
                   href="{{ route("mahasiswa.index") }}"
                >
                    @lang("application.mahasiswa")
                </a>
            </li>
        @endcan

        @can(\App\Providers\AuthServiceProvider::MANAGE_DOKUMEN_WORD)
            <li class="nav-item {{ Route::is("dokumen-word.*") ? "active" : "" }}">
                <a class="nav-link pt-0"
                   href="{{ route("dokumen-word.index") }}"
                >
                    @lang("application.dokumen_word")
                </a>
            </li>
        @endcan

{{--        <li class="nav-item {{ Route::is("word.*") ? "active" : "" }}">--}}
{{--            <a class="nav-link pt-0"--}}
{{--               href="{{ route("word.index") }}"--}}
{{--            >--}}
{{--                @lang("application.word_list")--}}
{{--            </a>--}}
{{--        </li>--}}
    </ul>
</nav>
<nav class="col-md-2 h5" style="height: 100%; font-size: 12pt; background: rgba(236, 226, 226, 0.2); padding: 20px; box-shadow: 0 2px 4px 0 rgba(76, 55, 55, 0.04)">
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
            <li class="nav-item {{ Route::is("file-word.*") ? "active" : "" }}">
                <a class="nav-link pt-0"
                   href="{{ route("file-word.index") }}"
                >
                    @lang("application.file_word")
                </a>
            </li>
        @endcan
    </ul>
</nav>
@auth
    <div class="alert alert-info">
        @if(auth()->user()->level === \App\User::LEVEL_MAHASISWA)
            Anda masuk sebagai <strong>{{ auth()->user()->level }}</strong> dengan
            nama <strong>{{ auth()->user()->name }} ({{ auth()->user()->nomor_induk }})</strong>.
        @elseif(auth()->user()->level === \App\User::LEVEL_ADMIN)
            Anda masuk sebagai <strong>{{ auth()->user()->level }}</strong>.
        @endif
    </div>
@endauth
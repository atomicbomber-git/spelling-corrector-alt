@auth
    @if(auth()->user()->level === \App\User::LEVEL_MAHASISWA)
        <strong>{{ auth()->user()->name }} ({{ auth()->user()->nomor_induk }})</strong>
    @elseif(auth()->user()->level === \App\User::LEVEL_ADMIN)
        <strong>{{ auth()->user()->name }}</strong>
    @endif
@endauth
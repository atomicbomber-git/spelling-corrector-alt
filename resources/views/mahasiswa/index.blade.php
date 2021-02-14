@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        @lang("application.mahasiswa")
    </h1>

    <x-messages></x-messages>

    <div class="my-3 d-flex justify-content-end">
        <a
                class="btn btn-primary"
                href="{{ route("mahasiswa.create") }}"
        >
            @lang("application.create")
        </a>
    </div>

    <div>
        @if($mahasiswas->isNotEmpty())
            <x-table>
                <x-thead>
                    <tr>
                        <th> @lang("application.number_symbol") </th>
                        <th> @lang("application.real_name") </th>
                        <th> @lang("application.student_id") </th>
                        <th> @lang("application.username") </th>
                        <th class="text-center"> @lang("application.controls") </th>
                    </tr>
                </x-thead>

                <tbody>
                @foreach ($mahasiswas as $mahasiswa)
                    <tr>
                        <td> {{ $mahasiswas->firstItem() + $loop->index }} </td>
                        <td> {{ $mahasiswa->name }} </td>
                        <td> {{ $mahasiswa->nomor_induk }} </td>
                        <td> {{ $mahasiswa->username }} </td>
                        <td class="text-center">
                            <a href="{{ route("mahasiswa.edit", $mahasiswa) }}"
                               class="btn btn-primary btn-sm"
                            >
                                @lang("application.edit")
                            </a>

                            <a href="{{ route("mahasiswa.show", $mahasiswa) }}"
                               class="btn btn-primary btn-sm"
                            >
                                @lang("application.show")
                            </a>

                            <form
                                    class="d-inline-block"
                                    x-data="{}"
                                    @submit.prevent="confirmDialog().then(res => res.isConfirmed && $event.target.submit())"
                                    action="{{ route("mahasiswa.destroy", $mahasiswa) }}"
                                    method="POST"
                            >
                                @csrf
                                @method("DELETE")

                                <button class="btn btn-outline-danger btn-sm">
                                    @lang("application.destroy")
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </x-table>

            <div class="d-flex justify-content-center">
                {{ $mahasiswas->links() }}
            </div>

        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __("messages.errors.no_data") }}
            </div>
        @endif
    </div>
@endsection
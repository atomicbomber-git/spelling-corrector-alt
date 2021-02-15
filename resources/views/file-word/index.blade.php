@extends("layouts.app")

@section("content")
    <h1 class="feature-title">
        @lang("application.file_word")
    </h1>

    <x-messages></x-messages>

    <div class="my-3 d-flex justify-content-end">
        <a
                class="btn btn-primary"
                href="{{ route("file-word.create") }}"
        >
            @lang("application.create")
        </a>
    </div>

    <div>
        @if($file_words->isNotEmpty())
            <x-table>
                <x-thead>
                    <tr>
                        <th> @lang("application.number_symbol") </th>
                        <th> @lang("application.title") </th>
                        <th> @lang("application.created_at") </th>
                        <th> @lang("application.updated_at") </th>
                        <th class="text-center"> @lang("application.controls") </th>
                    </tr>
                </x-thead>

                <tbody>
                @foreach ($file_words as $file_word)
                    <tr>
                        <td> {{ $file_words->firstItem() + $loop->index }} </td>
                        <td> {{ $file_word->nama }} </td>

                        <td> {{ \App\Support\Formatter::formatDatetime($file_word->created_at) }} </td>
                        <td> {{ \App\Support\Formatter::formatDatetime($file_word->updated_at) }} </td>

                        <td class="text-center">
                            <a href="{{ route("file-word.download", $file_word) }}"
                               class="btn btn-primary btn-sm"
                            >
                                @lang("application.download")
                            </a>

                            <a href="{{ route("file-word.edit", $file_word) }}"
                               class="btn btn-primary btn-sm"
                            >
                                @lang("application.edit")
                            </a>

                            <a href="{{ route("file-word.show", $file_word) }}"
                               class="btn btn-primary btn-sm"
                            >
                                @lang("application.show")
                            </a>

                            <form
                                    class="d-inline-block"
                                    x-data="{}"
                                    @submit.prevent="confirmDialog().then(res => res.isConfirmed && $event.target.submit())"
                                    action="{{ route("file-word.destroy", $file_word) }}"
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
                {{ $file_words->links() }}
            </div>

        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __("messages.errors.no_data") }}
            </div>
        @endif
    </div>
@endsection
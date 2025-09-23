<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
    <title>Document</title>
</head>
<body>
<section class="text-gray-800 w-full min-h-screen flex flex-col justify-center items-center px-4 bg-gray-50">
    <h1 class="text-3xl font-bold mb-6">Import Amazon/Yahoo/楽天 </h1>
    <!-- 操作ボタン -->
    <div class="flex justify-center gap-4 mb-6">
        <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 shadow" id="add">Add
        </button>
        <button type="button" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 shadow" id="delete">
            Delete
        </button>
    </div>
    <!-- フォーム -->
    <form class="shadow-md rounded-md bg-white w-full max-w-2xl p-8"
          method="POST"
          action="{{ route('amazon.create') }}"
          enctype="multipart/form-data">
        @csrf
        @php
            $csvErrors = session('csv_errors');
        @endphp
        <div>
            @if(!empty($csvErrors))

                <div class="text-red-600">
                    {{$csvErrors['file_name']}}
                    @foreach ($csvErrors as $line => $messages)
                        @if(is_array($messages))
                            <p>row {{ $line }}:</p>
                            <ul>
                                @foreach ($messages as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </div>

            @endif
        </div>
        <!-- Tour ID -->


        <!-- ホテル+ファイルエリア -->
        <div id="target_rooming">
            <!-- Hotel Name -->


            <!-- File input -->
            <div class="csv-block mb-6">
                <label class="block text-sm font-semibold mb-1">CSV File:</label>
                @php $inputId = 'csvFile_' . uniqid(); @endphp
                <input type="file" class="csv-file hidden" id="{{$inputId}}" name="csvFile" multiple
                       onchange="updateFileName(this)">
                <label for="{{$inputId}}"
                       class="cursor-pointer inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 shadow">
                    Choose CSV File
                </label>
                <span class="file-name ml-2 text-gray-700">No file selected</span>
            </div>
        </div>

        <!-- Submit -->
        <div class="text-center mt-8">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow">
                Import File
            </button>
        </div>
    </form>

    <div class="text-gray-800 w-full flex flex-col justify-center items-center px-4 bg-gray-50">
        <form method="GET" action="{{route('amazon.download')}}" >
        <table>
            <thead>
            <tr>
                <th class="border border-slate-600 bg-blue-300">
                    NO.
                </th>
                <th class="border border-slate-600 bg-blue-300">
                    Name
                </th>
                <th class="border border-slate-600 bg-blue-300">
                    設定時間
                </th>
                <th class="border border-slate-600 bg-blue-300">
                    ファイルダウンロード
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($amazon_data as $key=>$value)
                <tr>
                    <td class="border border-slate-600">
                        {{ $value->id }}
                    </td>
                    <td class="border border-slate-600">
                        {{ $value->name }}
                    </td>
                    <td class="border border-slate-600">
                        {{ $value->created_at }}
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary" name="download" value="{{$value->id}}">
                            ダウンロード
                        </button>
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>
        </form>



    </div>
    <div class="mt-10 mb-10 ml-20 mr-20">
        {{$amazon_data->links()}}
    </div>
</section>








<script>
function updateFileName(input) {
    console.log('ddddd');
    //const container = input.parentElement;
    const container = input.closest('.csv-block');
    const fileNameSpan = container.querySelector('.file-name');
    const files = input.files;

    console.log(files.length);

    if (!files.length) {
        fileNameSpan.textContent = 'No file selected';
    } else {
        fileNameSpan.textContent = [...files].map(f => f.name).join(', ');
    }
}
</script>




</body>
</html>

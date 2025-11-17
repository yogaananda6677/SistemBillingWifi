<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>@yield('title')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">@


        <style>
            body { overflow-x: hidden; }
        </style>
</head>

<body>

    {{-- NAVBAR --}}
    @include('layouts.navbar')

    <div class="d-flex">

        {{-- SIDEBAR --}}
        @include('layouts.sidebar')

        {{-- PAGE CONTENT --}}
        <div class="flex-grow-1 p-4">
            @yield('content')
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

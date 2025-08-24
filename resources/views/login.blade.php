<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full font-sans">

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">iShelf</h2>
    </div>
    @if($errors->any())
        {!! implode('', $errors->all('<div style="color: red; text-align: center;">:message</div>')) !!}
    @endif

    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <form action="/login" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm/6 font-medium text-gray-900">Phone</label>
                <div class="mt-2">
                    <input id="phone" type="text" name="phone" required="required" autocomplete="off" class="cursor-default outline-none block border border-black border-opacity-20 w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 placeholder:text-gray-400 sm:text-sm/6" />
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                <div class="mt-2">
                    <input id="password" type="password" name="password" required="required" autocomplete="off" class="cursor-default outline-none block border border-black border-opacity-20 w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 placeholder:text-gray-400 sm:text-sm/6" />
                </div>
            </div>

            <div>
                <button type="submit" class="cursor-default flex w-full justify-center rounded-md bg-black px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-opacity-80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">Login</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

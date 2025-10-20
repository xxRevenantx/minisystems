<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">



<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- <script src="https://cdn.tiny.cloud/1/1ebweotq439cl3bk11wscr1wf0h3iemo2t74u6ve9sjcy7cl/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script> --}}


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

@fluxAppearance

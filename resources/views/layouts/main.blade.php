<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        Cloudflare
    </title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>
<!-- ====== Navbar Section Start -->
<div
    class="ud-header py-2 px-3 flex w-full items-center bg-blue-800 text-white"
>
    <div class="container">
        <div class="relative -mx-4 flex items-center justify-between">
            <div class="w-60 max-w-full px-4">
                <a href="{{ route('cloudflare.index') }}" class="navbar-logo block w-full py-5">
                    CLOUDFLARE
                </a>
            </div>
        </div>
    </div>
</div>
<!-- ====== Navbar Section End -->

<!-- ====== Features Section Start -->
<section class="py-5 dark:bg-dark">
    <div class="container mx-auto">
        @yield('contents')
    </div>
</section>
<!-- ====== Features Section End -->


<!-- ====== All Scripts -->

</body>
</html>

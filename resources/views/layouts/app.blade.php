<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title') -"2 Factores"</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS Link -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.1/tailwind.min.css">

    <!-- Fontawesome Link -->
    <link href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" rel="stylesheet">

  </head>
  <body>
    <nav class="flex py-5 bg-blue-500 text-white">
        <div class="w-1/2 px-12 mr-auto">
          <p class="text-2xl font-bold">App 2fa</p>
        </div>


        <ul class="w-1/2 px-16 ml-auto flex justify-end pt-1">
            @if(auth()->check())
              <li class="mx-8">
                <p class="text-xl">¡Bienvenido de Nuevo! <b>{{ auth()->user()->name }}</b></p>
              </li>

              <li>
                <a href="{{ route('login.logout') }}" class="font-bold
                py-3 px-4 rounded-md bg-red-500 hover:bg-red-600">Cerrar Sesión</a>
              </li>
            @else
              <li class="mx-6">
                <a href="{{ route('login.index') }}" class="font-semibold
                hover:bg-blue-700 py-3 px-4 rounded-md">Iniciar Sesión</a>
              </li>
              <li>
                <a href="{{ route('register.index') }}" class="font-semibold
                border-2 border-white py-2 px-4 rounded-md hover:bg-white
                hover:text-blue-700">Registrar</a>
              </li>
            @endif
            </ul>


    </nav>
    @yield('content')
    
<footer class="bg-white text-dark text-center py-4 text-lg font-semibold">
        Servidor actual: 2
    </footer>


  </body>
</html>

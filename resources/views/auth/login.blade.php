@extends('layouts.app')

@section('title','Login' )


@section('content')

<div class="block mx-auto my-12 p-8 bg-white w-1/3 border border-gray-200
rounded-lg shadow-lg">

  <h1 class="text-3xl text-center font-bold">Inicia Sesión</h1>

  <form class="mt-4" method="POST" action="">
    @csrf

    <input type="email" class="border border-gray-200 rounded-md bg-gray-200 w-full
    text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" placeholder="Correo electrónico" autofocus
    id="email" name="email">

    <input type="password" class="border border-gray-200 rounded-md bg-gray-200 w-full
    text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" placeholder="Contraseña"
    id="password" name="password">

    @error('message')
      <p class="border border-red-500 rounded-md bg-red-100 w-full
      text-red-600 p-2 my-2">* {{ $message }}</p>
    @enderror

    <div class="form-group mt-3">
      {!! NoCaptcha::renderJs('es', false, 'onLoadCallback') !!}
      {!! NoCaptcha::display() !!}
    </div>
    @if($errors->has('g-recaptcha-response'))
    <p class="border border-red-500 rounded-md bg-red-100 w-full
        text-red-600 p-2 my-2">
        * {{ $errors->first('g-recaptcha-response') }}
    </p>
    @endif
    <button type="submit" class="rounded-md bg-blue-500 w-full text-lg
    text-white font-semibold p-2 my-3 hover:bg-blue-600">Iniciar</button>


  </form>


</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  @if(session('success'))
    Swal.fire({
      icon: 'success',
      title: '¡Registro exitoso!',
      text: {!! json_encode(session('success')) !!},
      toast: true,  // Muestra la alerta en forma de notificación
      position: 'top-end', // Ubicación en la pantalla (puedes cambiar a 'center' si prefieres)
      showConfirmButton: false, // Oculta el botón de confirmación
      timer: 3000, // Tiempo en milisegundos (3 segundos)
      timerProgressBar: true, // Muestra barra de tiempo en la alerta
    });
  @endif
</script>


<script>
    @if(session('logout_success'))
        Swal.fire({
            icon: 'success',
            title: 'Has cerrado session!',
            text: '{{ session('success') }}',
            timer: 5000,  // El mensaje desaparecerá automáticamente después de 3 segundos
            showConfirmButton: false
        });
    @endif
</script>


@endsection


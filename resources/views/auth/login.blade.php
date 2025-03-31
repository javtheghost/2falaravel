@extends('layouts.app')

@section('title','Login')

@section('content')

<div class="flex justify-center items-center min-h-screen px-4">
  <div class="block mx-auto my-12 p-8 bg-white w-full max-w-lg border border-gray-200
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

      <div class="form-group mt-3 flex justify-center">
        {!! NoCaptcha::renderJs('es', false, 'onLoadCallback') !!}
        <div class="overflow-hidden w-full flex justify-center">
          {!! NoCaptcha::display() !!}
        </div>
      </div>

      @if($errors->has('g-recaptcha-response'))
      <p class="border border-red-500 rounded-md bg-red-100 w-full
          text-red-600 p-2 my-2">
          * {{ $errors->first('g-recaptcha-response') }}
      </p>
      @endif

      <button type="submit" class="rounded-md bg-blue-500 w-full text-lg
      text-white font-semibold p-2 my-3 hover:bg-blue-600">Iniciar</button>
      @if ($errors->has('sms'))
      <p class="border border-yellow-500 rounded-md bg-yellow-100 w-full text-yellow-800 p-2 my-2">
          * {{ $errors->first('sms') }}
      </p>
  @endif
  
      
    </form>
  </div>
</div>

<script>
  @if(session('success'))
    Swal.fire({
      icon: 'success',
      title: '¡Registro exitoso!',
      text: {!! json_encode(session('success')) !!},
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
    });
  @endif
</script>

<script>
  @if(session('logout_success'))
    Swal.fire({
      icon: 'success',
      title: 'Has cerrado sesión!',
      text: '{{ session('success') }}',
      timer: 5000,
      showConfirmButton: false
    });
  @endif
</script>
@endsection

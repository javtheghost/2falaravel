@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="flex justify-center items-center min-h-screen px-4">
  <div class="block mx-auto my-12 p-8 bg-white w-full max-w-lg border border-gray-200 rounded-lg shadow-lg">

    <h1 class="text-3xl text-center font-bold">Registro</h1>

    <form class="mt-4" method="POST" action="{{ route('register.store') }}">
      @csrf

      <input type="text" class="border border-gray-200 rounded-md bg-gray-200 w-full text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" 
             placeholder="Nombre completo" id="name" name="name" value="{{ old('name') }}" required>
      @error('name')
        <p class="border border-red-500 rounded-md bg-red-100 w-full text-red-600 p-2 my-2">* {{ $message }}</p>
      @enderror

      <input type="email" class="border border-gray-200 rounded-md bg-gray-200 w-full text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" 
             placeholder="Correo electrónico" id="email" name="email" value="{{ old('email') }}" required>
      @error('email')
        <p class="border border-red-500 rounded-md bg-red-100 w-full text-red-600 p-2 my-2">* {{ $message }}</p>
      @enderror

      <input type="password" class="border border-gray-200 rounded-md bg-gray-200 w-full text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" 
             placeholder="Contraseña" id="password" name="password" required>
      @error('password')
        <p class="border border-red-500 rounded-md bg-red-100 w-full text-red-600 p-2 my-2">* {{ $message }}</p>
      @enderror

      <input type="password" class="border border-gray-200 rounded-md bg-gray-200 w-full text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" 
             placeholder="Confirmar Contraseña" id="password_confirmation" name="password_confirmation" required>

      <div class="mt-4">
        <input type="tel" 
               class="border border-gray-200 rounded-md bg-gray-200 w-full text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" 
               placeholder="Número telefónico" 
               id="phone_number" 
               name="phone_number"
               value="{{ old('phone_number') }}"
               required
               pattern="[0-9]{10}">
        
        @if(config('app.trial_mode'))
          <small class="text-blue-500 text-sm">
           
          </small>
        @endif
        
        @error('phone_number')
          <p class="border border-red-500 rounded-md bg-red-100 w-full text-red-600 p-2 my-2">* {{ $message }}</p>
        @enderror
      </div>

      <div class="form-group mt-3 flex justify-center">
        {!! NoCaptcha::renderJs('es', false, 'onLoadCallback') !!}
        <div class="overflow-hidden w-full flex justify-center">
          {!! NoCaptcha::display() !!}
        </div>
        @error('g-recaptcha-response')
          <p class="border border-red-500 rounded-md bg-red-100 w-full text-red-600 p-2 my-2">
            * {{ $errors->first('g-recaptcha-response') }}
          </p>
        @enderror
      </div>

      <button type="submit" class="rounded-md bg-blue-500 w-full text-lg text-white font-semibold p-2 my-3 hover:bg-blue-600">
        Registrarse
      </button>
    </form>
  </div>
</div>
@endsection
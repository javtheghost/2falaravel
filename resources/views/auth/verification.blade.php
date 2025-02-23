@extends('layouts.app')

@section('title', 'Verification')

@section('content')
<div class="block mx-auto my-12 p-8 bg-white w-1/3 border border-gray-200 rounded-lg shadow-lg">
    <h1 class="text-3xl text-center font-bold">Ingresar Codigo De Confirmacion</h1>

    <form class="mt-4" method="POST" action="{{ route('auth.storeve') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">

        <input type="tel" class="border border-gray-200 rounded-md bg-gray-200 w-full
        text-lg placeholder-gray-900 p-2 my-2 focus:bg-white" placeholder="Imgrese el codigo de confirmacion"
        id="verification_code" name="verification_code">

        @error('verification_code')
        <p class="border border-red-500 rounded-md bg-red-100 w-full
          text-red-600 p-2 my-2">* {{ $message }}</p>
        @enderror

        <button type="submit" class="rounded-md bg-indigo-500 w-full text-lg
        text-white font-semibold p-2 my-3 hover:bg-indigo-600">Confirmar</button>
    </form>
</div>
@endsection

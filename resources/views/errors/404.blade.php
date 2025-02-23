
@extends('layouts.app') <!-- Ajusta a tu layout principal si es necesario -->

@section('title', 'Página no encontrada')

@section('content')
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center row">
            <div class=" col-md-6">
                <img src="https://cdn.pixabay.com/photo/2017/03/09/12/31/error-2129569__340.jpg" alt=""
                    class="img-fluid">
            </div>
            <div class=" col-md-6 mt-5">
                <p class="fs-3"> <span class="text-danger">Opps!</span> Página no encontrada.</p>
                <p class="lead">
                    Lo siento, no pudimos encontrar la página que buscas.
                </p>
                <a href="{{ route('home') }}" class="mt-6 inline-block px-6 py-3 bg-blue-500 text-white rounded-lg shadow-lg hover:bg-blue-600">
                    Ir al inicio
                </a>
            </div>

        </div>
    </div>

@endsection

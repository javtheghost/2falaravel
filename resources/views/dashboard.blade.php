@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Dashboard</h1>
        <!-- Contenido específico del dashboard aquí -->

        <form  id="logout-form" action="{{ route('login.logout') }}" method="post">
            @csrf
            <button  type="submit" onclick="confirmarLogout(event)">Logout</button>
        </form>
    </div>
    
@endsection
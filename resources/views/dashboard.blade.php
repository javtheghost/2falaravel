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
    <script>
        function confirmarLogout(event) {
          event.preventDefault(); // Evita que el formulario se envíe automáticamente

          Swal.fire({
            title: '¿Estás seguro?',
            text: 'Vas a cerrar sesión',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
          }).then((result) => {
            if (result.isConfirmed) {
              document.getElementById('logout-form').submit(); // Envía el formulario
            }
          });
        }
      </script>

@endsection

@extends('layouts.app')

@section('title','Home' )

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  @if(session('login_success'))
    Swal.fire({
      icon: 'success',
      title: '¡Bienvenido!',
      text: {!! json_encode(session('login_success')) !!},
      toast: true, // Notificación flotante
      position: 'top-end', // Ubicación en la pantalla
      showConfirmButton: false, // Oculta el botón de confirmación
      timer: 3000, // Se cierra automáticamente en 3 segundos
      timerProgressBar: true, // Muestra barra de tiempo
    });
  @endif
</script>


<script>
    @if(session('verify2fa_success'))
        Swal.fire({
            icon: 'success',
            title: '¡Bienvenido!',
            text: '{{ session('success') }}',
            timer: 3000,  // El mensaje desaparecerá automáticamente después de 3 segundos
            showConfirmButton: false
        });
    @endif
</script>



@endsection

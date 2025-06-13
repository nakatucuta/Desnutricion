<!-- Animate.css para animaciones -->
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
/* Ajuste para la animación de la card */
.card {
  animation-duration: 1s;
  animation-delay: 0.3s;
}
</style>


<style>
/* Fondo difuminado: combinamos una capa semitransparente con la imagen de fondo */
body {
/* Imagen centrada, sin repetición, abarcando todo */
background: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.7)), 
          url("{{ asset('vendor/adminlte/dist/img/logo.png') }}") center center no-repeat;
background-size: 30% auto; /* Ajusta el tamaño de la imagen */
background-attachment: fixed;
}





/* Contenedor del header para alinear el botón a la derecha */
.header-container {
display: flex;
justify-content: center; /* Alinea al centro horizontalmente */
align-items: center;     /* Alinea verticalmente */
padding: 20px;
border-bottom: 2px solid #ccc;
margin-bottom: 20px;
}


/* Estilo profesional para el título */
.executive-title {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
font-size: 36px;
font-weight: 700;
color: #2C3E50;
text-transform: uppercase;
letter-spacing: 2px;
text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
padding: 10px 20px;
background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
border-radius: 8px;
box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
border-left: 6px solid #2980b9;
}

/* Estilo del botón animado */
.btn-download {
background-color: #ff4b5c;
color: white;
padding: 15px 30px;
border-radius: 50px;
font-size: 18px;
text-align: center;
display: inline-block;
text-decoration: none;
position: relative;
overflow: hidden;
box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
animation: pulse 1s infinite;
transition: background-color 0.3s ease;
}

.btn-download:hover {
background-color: #ff616f;
color: white;
}

/* Efecto de palpitación */
@keyframes pulse {
0% {
  transform: scale(1);
}

50% {
  transform: scale(1.05);
}

100% {
  transform: scale(1);
}
}
</style>

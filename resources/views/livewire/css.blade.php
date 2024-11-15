 <style>
        .spinner-large {
            width: 20rem;
            height: 20rem;
            border-width: 5.0rem;
        }

        .spinner-large {
            width: 10rem;
            height: 10rem;
            border-width: 1rem;
            color: #28a745; /* Verde */
        }

        .modal-content {
            background-color: #ffffff; /* blanco */
            border: 2px solid #28a745; /* Verde */
        }

        .modal-header {
            background-color: #28a745; /* Verde */
            color: #ffffff;
        }

        .modal-indicator {            
            color: #202020;
            font-size: 1rem;            
        }

        .text-gray-custom {
            color: #202020; /* Gris personalizado */
        }


        .drag-drop-area {
            background-color: #ffffff; /* Verde claro */
            border: 2px dashed #28a745; /* Verde */
        }

        .drag-drop-area:hover {
            background-color: #e6f9e6; /* Verde claro más oscuro */
        }

        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }


        /*PARA EL ICONO DE ENVIAR MENSAJE PAROPADEE */

       /* Estilo base del botón más pequeño */
.blinking-button {
    display: inline-block;
    padding: 5px 10px;  /* Reducido el padding */
    font-size: 12px;    /* Tamaño de texto más pequeño */
    font-weight: bold;
    color: white;
    background-color: #28a745; /* Verde base */
    border: none;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.5s ease;
    position: relative;
}

/* Agregar el icono dentro del botón, con un tamaño más pequeño */
.blinking-button i {
    margin-right: 5px;
    font-size: 12px; /* Reducir el tamaño del icono */
}

/* Parpadeo con variaciones de color verde */
@keyframes blinking {
    0% { background-color: #28a745; }  /* Verde base */
    50% { background-color: #34d058; } /* Verde más claro */
    100% { background-color: #28a745; } /* Regresar al verde base */
}

/* Aplicar la animación de parpadeo */
.blinking-button {
    animation: blinking 1.5s infinite;
}

/* Efecto de hover */
.blinking-button:hover {
    background-color: #218838; /* Verde más oscuro al pasar el mouse */
    text-decoration: none;
}

/* Sombra al pasar el mouse */
.blinking-button:hover {
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
}

    </style>
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>

@keyframes explode {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

.custom-alert {
  animation-name: explode;
  animation-duration: 3s; /* Cambia la duración a 4 segundos */
  animation-timing-function: ease-in-out;
}


@keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(255, 99, 132, 0.7);
    }
    70% {
      box-shadow: 0 0 0 20px rgba(255, 99, 132, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(255, 99, 132, 0);
    }
  }

  .btn-pulse {
  animation: pulse 1s ease-in-out infinite;
}

 .dataTables_filter input {
  width: 500px !important;
  height: 100%;
  background-color: #555 ;
  border: solid 3px !important;
  border-radius: 20px !important;
  color: rgb(64, 125, 232);
  padding: 10px !important;
}





  .dataTables_filter input {
    width: 500px !important;
    height: 100%;
    background-color: #555 ;
    border: solid 3px !important;
    border-radius: 20px !important;
    color: rgb(64, 125, 232);
    padding: 10px !important;
    font-weight: bold !important;
  }
  
  .dataTables_filter label {
    font-weight: bold !important;
  }
  
   .dataTables_length label {
    
    font-weight: bold !important;
  } 
  .dataTables_length select {
    display: flex ;
    border: solid 3px !important;
    border-radius: 20px !important;
    align-items: center !important;
    margin-bottom: 10px !important;
    color: rgb(64, 125, 232) !important;
  }
  
  /*
  
   */



      /*AQUI COMIENZA EL CSS PARA EL BUSCADOR */
        /* Contenedor principal */
        .loading-spinner {
            text-align: center;
            font-size: 1rem;
            color: #007bff;
            margin-top: 10px;
        }



        .search-container {
            position: relative;
            max-width: 100%;
            /* Ocupa todo el ancho disponible */
            width: 500px;
            /* Ancho deseado */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            margin-left: auto;
            /* Para asegurar que esté alineado a la derecha */

        }

        /* Input de búsqueda con icono */
        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 6px;
            transition: all 0.3s ease;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0px 4px 12px rgba(0, 123, 255, 0.2);
        }

        /* Icono de búsqueda dentro del input */
        .search-icon {
            position: absolute;
            right: 15px;
            font-size: 18px;
            color: #888;
            transition: color 0.3s ease;
        }

        .search-input:focus+.search-icon {
            color: #007bff;
        }

        /* Resultados de búsqueda */
        #search-results {
            max-height: 300px;
            overflow-y: auto;
            width: 100%;
            position: absolute;
            bottom: 100%;
            /* Hacer que el contenedor suba sobre el input */
            left: 0;
            background-color: #fff;
            box-shadow: 0px -8px 16px rgba(0, 0, 0, 0.1);
            /* Sombra hacia arriba */
            border-radius: 6px;
            z-index: 1000;
            animation: slideUp 0.3s ease;
            padding-top: 8px;
        }

        .list-group-item {
            padding: 12px 20px;
            font-size: 16px;
            color: #333;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .list-group-item:hover {
            background-color: #007bff;
            color: #fff;
        }

        /* Animación de deslizar para mostrar resultados */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive: pantalla pequeña */
        @media (max-width: 576px) {
            .search-container {
                width: 90%;
                padding: 15px;
            }

            .search-input {
                padding: 10px 35px 10px 15px;
                font-size: 14px;
            }

            .list-group-item {
                font-size: 14px;
            }
        }

        /* AQUI TERMINA EL CSS DEL BUSCADOR */
</style>
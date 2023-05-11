<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">
<style>

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
  
</style>
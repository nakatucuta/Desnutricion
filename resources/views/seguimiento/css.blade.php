<style>
  .title-wrapper {
    text-align: center;
    margin: 20px 0;
  }

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
    display: inline-block;
  }



  .dataTables_filter {
    float: right !important;
    margin-bottom: 1rem;
  }

  .dataTables_filter input {
    border-radius: 20px;
    border: 1px solid #ced4da;
    padding: 6px 12px;
  }

  .dataTables_length {
    float: left !important;
    margin-top: 10px;
  }

  #overlay-spinner {
    display: none;
    position: fixed;
    z-index: 9999;
    background: rgba(255, 255, 255, 0.85);
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }

  .spinner-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
  }

  .spinner-border {
    width: 4rem;
    height: 4rem;
  }

  .selected-callout {
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    border: 2px solid #007bff;
  }

/* estilo de las cajas de filtro */
  .stat-box {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background-color: #fff;
  border-left: 6px solid transparent;
  text-decoration: none;
  position: relative;
  overflow: hidden;
  transition: all 0.2s ease-in-out;
  
}
.stat-box:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  text-decoration: none;
  transform: scale(1.02);
}

.stat-box .icon {
  font-size: 2rem;
  margin-right: 15px;
  color: #fff;
  background-color: #007bff;
  padding: 12px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
}

.stat-box .content h5 {
  margin: 0;
  font-weight: 600;
  color: #333;
}
.stat-box .content h2 {
  margin: 0;
  font-size: 1.8rem;
  color: #007bff;
}
.stat-box.selected {
  background-color: rgba(0, 123, 255, 0.05);
  border-left: 6px solid #007bff !important;
  box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
}

.stat-box .icon i {
  font-size: 24px;
}
.stat-abiertos .icon { background-color: #17a2b8; }
.stat-abiertos { border-left-color: #17a2b8; }

.stat-proximos .icon { background-color: #28a745; }
.stat-proximos { border-left-color: #28a745; }

.stat-cerrados .icon { background-color: #dc3545; }
.stat-cerrados { border-left-color: #dc3545; }

.selected-callout {
  outline: 3px solid rgba(0, 123, 255, 0.5);
  background: rgba(0, 123, 255, 0.05);
}

/* estilo boton nuevo seguimiento */


.btn-nuevo-seguimiento {
  border-radius: 50px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.8px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: white !important;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
  transition: all 0.3s ease-in-out;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-transform: uppercase;
}

.btn-nuevo-seguimiento i {
  margin-right: 8px;
  font-size: 1rem;
}

.btn-nuevo-seguimiento:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 16px rgba(0, 123, 255, 0.6);
  text-decoration: none;
}

/* css del boton  de los reporte */
.btn-exportar {
  border-radius: 50px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.8px;
  background: linear-gradient(135deg, #28a745, #218838);
  color: white !important;
  border: none;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  transition: all 0.3s ease-in-out;
  text-transform: uppercase;
}

.btn-exportar:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 18px rgba(40, 167, 69, 0.6);
}

.export-dropdown .dropdown-menu {
  border-radius: 10px;
  border: none;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.2s ease-in-out;
}

.export-dropdown .dropdown-item {
  padding: 10px 18px;
  font-size: 0.95rem;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.export-dropdown .dropdown-item:hover {
  background-color: #f1f1f1;
  color: #000;
}



/* css del boton  de los reporte */
.btn-exportar {
  border-radius: 50px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.8px;
  background: linear-gradient(135deg, #28a745, #218838);
  color: white !important;
  border: none;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  transition: all 0.3s ease-in-out;
  text-transform: uppercase;
}

.btn-exportar:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 18px rgba(40, 167, 69, 0.6);
}

.export-dropdown .dropdown-menu {
  border-radius: 10px;
  border: none;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.2s ease-in-out;
}

.export-dropdown .dropdown-item {
  padding: 10px 18px;
  font-size: 0.95rem;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.export-dropdown .dropdown-item:hover {
  background-color: #f1f1f1;
  color: #000;
}

/* Simple fade-in effect */
/* @keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to   { opacity: 1; transform: translateY(0); }
} */

</style>
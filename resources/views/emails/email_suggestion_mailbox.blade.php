<!doctype html>
<html lang="es">
<head>
    <style>
        .bg-img {
             min-height: 380px;
              background-color: #ACACAC;
              background-position: center;
              background-repeat: no-repeat;
              background-size: cover;
              position: relative;
        }
        .container,
        .container-fluid,
        .container-xxl,
        .container-xl,
        .container-lg,
        .container-md,
        .container-sm {
          width: 100%;
          padding-right: var(--bs-gutter-x, 0.75rem);
          padding-left: var(--bs-gutter-x, 0.75rem);
          margin-right: auto;
          margin-left: auto;
        }
        
        .table td, .table th {
          font-size: 14px;
          padding: 0%;
          font-family: system-ui, -apple-system, "Segoe UI", Roboto,
            "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif,
            "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji",SFMono-Regular, Menlo, Monaco, Consolas,
            "Liberation Mono", "Courier New", monospace;
        }
        .table_bg{
        background-color: #ACACAC;
        width: 100%;
        }
        .table_container{
            display: block !important; 
            max-width: 1000px !important; 
            margin: 0 auto !important;
            clear: both !important;
        }
        
        .div-firstsize{
            max-width: 600px; 
            margin: 0 auto; 
            display: block; 
            padding: 20px;
        }
        .table-container-2{
            background: #fff; 
            border-radius: 3px; 
            width:100% 
        }
        
        .table-td-padding{
            padding: 20px;
        }
        .text-center {
          text-align: center !important;
          padding-top: 20px;
          padding-bottom: 20px;
        }
        
        .center-line-logo {
          border-left: 2px solid; 
          color: #636363;
          vertical-align: middle;
        }
        .banner-email{
        background-color:#C0495D;
        padding-left: 8px;
        padding-right: 8px;
        padding-top: 8px;
        padding-bottom: 8px;
        font-weight: bold;
        color: white;
        }
        .div-sub-text{
            padding-left: 50px;
            padding-right: 50px;
        }
        .text-click{
            color: #2EDB5D;
            font-weight: bold;
        }
        
        .btn-pending {
          color: #fff;
          background-color: #ffc107;
          border: none;
          padding: 10px 15px;
          text-align: center;
          text-decoration: none;
          display: inline-block;
          border-radius: 12px;
          font-size: 20px;
        }
        
        
        
        .table-stripe td, .table-stripe th{
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #DDD;
            border-collapse: collapse;
        }
        
        .text-bold{
            font-weight: bold;
        }
        .table-icons td, .table-icons th{
            padding: 8px;
            font-weight: bold;
            text-align: center !important;
        }
        .text-cancel{
            font-weight: bold;
            color:#FF0000;
        }
        .text-footer{
            font-size: 9px;
        }
        
        .btn-confirm {
          color: #fff;
          background-color: #2EDB5D;
          border: none;
          padding: 10px 15px;
          text-align: center;
          text-decoration: none;
          display: inline-block;
          border-radius: 12px;
          font-size: 20px;
        }
        .btn-cancel {
          color: #fff;
          background-color: #FF0000;
          border: none;
          padding: 10px 15px;
          text-align: center;
          text-decoration: none;
          display: inline-block;
          border-radius: 12px;
          font-size: 20px;
        }
        
    </style>
</head>
<body class="bg-img">
    <div class="container">
        <div>
            <table class="table_bg">
                <tr>
                   <td></td>
                     <td class='container table_container'>
                         <div class="div-firstsize">
                             <table class="table table-container-2" cellpadding='0' cellspacing='0'>
                                 <tr>
                                    <td class="table">
                                       <table width='100%' cellpadding='0' cellspacing='0'>
                                           <tr>
                                            <div class="text-center">
                                                <img src={{asset('img/logo_iusa_gray.svg')}} alt="iusa_logo">
                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                                <img src={{asset('img/logo_pe.svg')}} alt="portal_logo">
                                            </div>
                                        </tr>
                                        <tr>
                                           <td>                                            
                                              <img class='img-responsive' style='max-width: 100%;' src={{asset('img/image_mailing_bs.png')}} />
                                              <div class="banner-email text-center">
                                                <h2><img src={{asset('img/icon_buzon.png')}} /> BUZ??N DE SUGERENCIAS</h2>
                                            </div>
                                          </td>
                                       </tr>
                                       <tr>
                                        <td>
                                         <br>
                                           <div class="div-sub-text">
                                              <p>Hola <span> {{$data_user['VORNA']}} {{$data_user['NACHN']}} {{$data_user['NACH2']}},</span></p>
                                              <p>tu solicitud se ha enviado correctamente, gracias por acercarte a nosotros</p>
                                              <p class="text-bold">A la brevedad posible recibir??s respuesta por parte del administrador del buz??n.</p>
                                              <br>
                                              <p>Saludos</p>
                                              <div class="text-center">
                                              <p>Ir a Portal del Empleado, <span class="text-click">Click aqu??</span></p>
                                              </div>
                                           </div>
                                           
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>

                                           <div class="div-sub-text text-center">
                                              <p class="text-footer">Copyright 2021 Grupo IUSA. Todos los Derechos Reservados.| <span class="text-cancel">Aviso de Privacida.</span></p>
                                           </div>
                                         </td>
                                    </tr>
                                </table>
                             </td>
                          </tr>
                      </table>
                 </div>
             </td>
            <td></td>
        </tr>
     </table>
 </div>
</div>
</body>
</html>
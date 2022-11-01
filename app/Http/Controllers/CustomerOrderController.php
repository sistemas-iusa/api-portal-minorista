<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;

class CustomerOrderController extends Controller
{
    //
    public function InfoCustomer(Request $request){
        /*Funcion para obtener la información del cliente sap*/

        $id_seller = $request->id_seller;
        $customer = $request->customer;
        $VKORG = $request->VKORG;
        $VTWEG = $request->VTWEG;
        $VKBUR = $request->VKBUR;
        
        //OBTENER INFORMACION DEL CLIENTE
        try{
            $service="http://172.16.176.25/webservices/IEL_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL"; 
            $parameters=array();
            //$parameters['Username']="$id_seller";
            $parameters['KKBER']="217";
            $parameters['KUNNR1']="$customer";                
            $parameters['VKORG']=$VKORG;
            $parameters['VTWEG']=$VTWEG;
            $client = new SoapClient($service,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result = $client->Vb_Cliente_Datosgrales($parameters);
            $result = obj2array($result);
            $result=$result['Vb_Cliente_DatosgralesResult']['RDCliente_Datosgrales'];
            $customer_data = collect($result);
            //$customer_data = $customer_data->first();
        } catch (Exception $e) {
            $customer_data = [];
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        //return $customer_data;
        //OBTENER DIAS CARTERA
        try {
            //$servicio="http://172.16.176.25/WebServices/PGC360_Des_Clientes_Agotacart/Clientes_Agotacart.asmx?WSDL"; 
            $service2="http://172.16.171.10/WebServices/PGC360_Pro_Clientes_Agotacart/Clientes_Agotacart.asmx?WSDL"; 
            $parameters2=array();
            $parameters2['KUNNR']="$customer";
            $parameters2['KUNNR2']="$customer";
            $client2 = new SoapClient($service2,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result2 = $client2->Vb_Clientes_Agotacart($parameters2);
            $result2 = obj2array($result2);
            $result2 = $result2['Vb_Clientes_AgotacartResult']['MyResultData'];
            $customer_purse = collect($result2);
            $customer_purse= $customer_purse->first();
        } catch (Exception $e) {
            $customer_purse = [];
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        
        //OBTENER DESTINOS CLIENTE
        try {              
            //$service3="http://172.16.176.25/WebServices/PGC360_Dest_Mercancia/Dest_Mercancia.asmx?WSDL"; 
            $service3="http://172.16.176.25/webservices/IEL_Dest_Mercancia/Dest_Mercancia.asmx?WSDL"; 
            $parameters3=array();
            $parameters3['KUNNR']="$customer";
            $parameters3['SPART']="90";
            $parameters3['VKORG']="IUS2";
            $parameters3['VTWEG']="$VTWEG";
            $client3 = new SoapClient($service3,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result3 = $client3->Vb_Dest_Mercancia($parameters3);
            $result3 = obj2array($result3);
            $customer_destiny[0]=$result3['Vb_Dest_MercanciaResult']['RDDest_Mercancia'];
            //$customer_destiny = collect($customer_destiny);
        } catch (Exception $e) {
            $customer_destiny = [];
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        //CALCULAR DIAS VENCIDOS
         //********* WEBSERVICE PARA PARTIDAS VENCIDAS
         try {   
            $service4="http://172.16.171.10/WebServices/PGC360_Pro_Partvenc_Oficclte/Partvenc_Oficclte.asmx?WSDL"; 
            $parameters4=array();
            $parameters4['KUNNR']="$customer";
            $parameters4['VKBUR']="$VKBUR";
            $client4 = new SoapClient($service4,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result4 = $client4->Vb_Partvenc_Oficclte($parameters4);
            $result4 = obj2array($result4);
            $result4 =$result4['Vb_Partvenc_OficclteResult']['MyResultData'];
            $games_array = collect($result4);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        $games_array_count = count($games_array);
        $games_count = 0;
        for ($i=0; $i < $games_array_count-1 ; $i++) { 
            $games_item = $games_array[$i];
            if ($games_item['ESTAT'] == "Vencido") {
                $games_count++;
            }
        }
        //LISTAS PARA CASO CONTADO
        $type_of_payment = [];
        $payment_method = [];
        $cfdi_use = [];
        $cfdi_relac = [];
        $cfdi_relac[0] = ['id' => '00', 'des' =>'0 - Sin selección'];
        $cfdi_relac[1] = ['id' => '04', 'des' =>'04 - Refacturacion'];
        //valor para determinar si el cliente es de contado
        if ($customer_data['ZTERM'] == 'IU00') {
            $type_of_payment[0] = ['id' => 'PPD', 'des' =>'Pago Parc o Diferido'];
            $type_of_payment[1] = ['id' => 'PUE', 'des' =>' Pago en 1 Sola Exhib'];
            
            $payment_method[0] = ['id' => 'S', 'des' =>' 01 Efectivo'];
            $payment_method[1] = ['id' => 'T', 'des' =>' 03 Transferencia'];
            $payment_method[2] = ['id' => 'U', 'des' =>' 03 Transf Electrónica d Fondos'];
            $payment_method[3] = ['id' => '5', 'des' =>' 15 Condonación'];
            $payment_method[4] = ['id' => '6', 'des' =>' 06 Dinero electrónico'];
            $payment_method[5] = ['id' => '7', 'des' =>' 27 A satisfacción del acreedor'];
            $payment_method[6] = ['id' => 'A', 'des' =>' 02 Cheque nominativo'];
            $payment_method[7] = ['id' => 'B', 'des' =>' 03 Transfer. Bancomer Terceros'];
            $payment_method[8] = ['id' => 'C', 'des' =>' 02 Cheque'];
            $payment_method[9] = ['id' => 'D', 'des' =>' 02 CHEQUE OCURRE'];
            $payment_method[10] = ['id' => 'E', 'des' =>' 01 Efectivo'];
            $payment_method[11] = ['id' => 'F', 'des' =>' 99 No Identificado'];
            $payment_method[12] = ['id' => 'G', 'des' =>' 03 Transferencia InterbancHSBC'];
            $payment_method[13] = ['id' => 'H', 'des' =>' 03 Transferencia Bancaria HSBC'];
            $payment_method[14] = ['id' => 'I', 'des' =>' 03 Transferencia Interbancaria'];
            $payment_method[15] = ['id' => 'J', 'des' =>' 30 Aplicación Anticipos'];
            $payment_method[16] = ['id' => 'K', 'des' =>' 12 Dación de pago'];
            $payment_method[17] = ['id' => 'L', 'des' =>' 02 Cheque sin Leyenda p/Abono'];
            $payment_method[18] = ['id' => 'M', 'des' =>' Pagado/Prepaid'];
            $payment_method[19] = ['id' => 'N', 'des' =>' Por Cobrar/Collect'];
            $payment_method[20] = ['id' => 'O', 'des' =>' 28 Tarjeta de Débito'];
            $payment_method[21] = ['id' => 'P', 'des' =>' Pagaré'];
            $payment_method[22] = ['id' => 'Q', 'des' =>' 17 Compensación'];
            $payment_method[23] = ['id' => 'R', 'des' =>' 04 Tarjeta de Crédito'];
            $payment_method[24] = ['id' => 'V', 'des' =>' 03 TRANSFERENCIA ELECTRONICA'];
            $payment_method[25] = ['id' => 'W', 'des' =>' 99 Otros'];
            $payment_method[26] = ['id' => 'X', 'des' =>' Check BOFA - FX or Single Curr'];
            $payment_method[27] = ['id' => 'Y', 'des' =>' Bank Transfer BOFA - IFT'];
            $payment_method[28] = ['id' => 'Z', 'des' =>' Wire BOFA - Fx Funds Transfer'];
            
            $cfdi_use[0] = ['id' => 'G03', 'des' =>' Gastos en general'];
            $cfdi_use[1] = ['id' => 'D01', 'des' =>' Hon Medicos,Dentales'];
            $cfdi_use[2] = ['id' => 'D02', 'des' =>' Gts Med x incap o di'];
            $cfdi_use[3] = ['id' => 'D04', 'des' =>' Donativos'];
            $cfdi_use[4] = ['id' => 'D05', 'des' =>' Int reales efec pag'];
            $cfdi_use[5] = ['id' => 'D06', 'des' =>' Aport volunt al SAR'];
            $cfdi_use[6] = ['id' => 'D07', 'des' =>' Primas x seg gts med'];
            $cfdi_use[7] = ['id' => 'D08', 'des' =>' Gts transp esc oblig'];
            $cfdi_use[8] = ['id' => 'D09', 'des' =>' Dep ctas p el ahorro'];
            $cfdi_use[9] = ['id' => 'D10', 'des' =>' Pago x serv educativ'];
            $cfdi_use[10] = ['id' => 'G01', 'des' =>' Adq de mercancías'];
            $cfdi_use[11] = ['id' => 'G02', 'des' =>' Devol, desc y bonif'];
            $cfdi_use[12] = ['id' => 'D03', 'des' =>' Gastos funerales'];
            $cfdi_use[13] = ['id' => 'I01', 'des' =>' Construcciones'];
            $cfdi_use[14] = ['id' => 'I02', 'des' =>' Mob y eq of x invers'];
            $cfdi_use[15] = ['id' => 'I03', 'des' =>' Equipo de transporte'];
            $cfdi_use[16] = ['id' => 'I04', 'des' =>' Eq de computo y accs'];
            $cfdi_use[17] = ['id' => 'I05', 'des' =>' Dados,troq,mod,matr'];
            $cfdi_use[18] = ['id' => 'I06', 'des' =>' Comunic telefónicas'];
            $cfdi_use[19] = ['id' => 'I07', 'des' =>' Comunic satelitales'];
            $cfdi_use[20] = ['id' => 'I08', 'des' =>' Otra maquinaria y eq'];
            $cfdi_use[21] = ['id' => 'P01', 'des' =>' Por definir'];          
        }//fin del if contado
        $data = [
            'customer_data' => $customer_data,
            'customer_purse' => $customer_purse, 
            'customer_destiny' => $customer_destiny, 
            'games_count' => $games_count, 
            'type_of_payment' => $type_of_payment,
            'payment_method' => $payment_method,
            'cfdi_use' => $cfdi_use,
            'cfdi_relac' => $cfdi_relac];
        
        return response()->json($data, 200);
    }
    public function getMaterialInfo(Request $request){
        /*Funcion para obtener la información del material*/

        //$usuario_vendedor = $request->usuario;
        //$puesto = $request->puesto;
        $material_code = $request->code;
        $units = $request->units;
        $customer = $request->customer;
        $VKORG = $request->VKORG;
        $VTWEG = $request->VTWEG;
        $VKBUR = $request->VKBUR;

        //completar el código de material a 18 digitos
        /*$n1=strlen($material_code);
        $n1_aux=18-$n1;
        $mat="";
        for ($i=0; $i <$n1_aux ; $i++) { 
            $mat.="0";
        }
        $material_code=$mat.$material_code;*/
        //validacion si el material se encuentra en carretes
        /*try {
            $service1="http://172.16.171.10/webservices/PGC360_Pro_Carretes_Materiales/Carretes_Materiales.asmx?WSDL";
            $client1 = new SoapClient($service1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result1 = $client1->Vb_Carretes_Materiales ();
            $result1 = obj2array($result1);
            $result1=$result1['Vb_Carretes_MaterialesResult']['MyResultData'];
            $reels_list = collect($result1);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        
        $reels_list_count = count($reels_list);
        $reels_alert = 0;
        for ($j=0; $j < $reels_list_count-1 ; $j++) { 
            $item_reels = $reels_list[$j];
            if($item_reels['MATNR'] == $material_code){
            $reels_alert = 1;
            }
        }
        if($reels_alert == 1){
            return response()->json(['error' => 'Material asignado a carrete' ], 400);
        }else*/if($units <= 0){
            return response()->json(['error' => 'Cantidad ingresada no debe ser menor o igual a 0' ], 400);
        }else{
          //Se necesita obtener la cantidad acorde a los empaques del material , esto por un descuento de acuerdo a cantidad
          try{
            $service2="http://172.16.176.25/webservices/IEL_Mater_Exist_Precios/Mater_Exist_Precios.asmx?WSDL";
            $parameters2=array();  
            $parameters2['P_VKBUR']="$VKBUR";
            $parameters2['P_MATNR']="$material_code";
            $parameters2['P_KUNNR']="$customer";
            $parameters2['P_VTWEG']="$VTWEG";
            $parameters2['P_VKORG']="$VKORG";
            $parameters2['P_CANT']="$units";
            
            $client2 = new SoapClient($service2,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result2 = $client2->Vb_Mater_Exist_Precios($parameters2);
            $result2 = obj2array($result2);
            $result2=$result2['Vb_Mater_Exist_PreciosResult']['MyResultData'];
            $material_info_1 = collect($result2);
            //$material_info_1 = $material_info_1->first();
            }catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            }
            $material_type=$material_info_1['MAABC'];
            if($material_info_1['MYERR'] == 1 && $material_info_1['FNMSG'] == 'Arithmetic operation resulted in an overflow.'){
                return response()->json(['error' => 'Código erroneo, revisar empaque, costo o bloqueos de cliente' ], 400);
            }else if($material_info_1['MYERR'] == 1){
                return response()->json(['error' => 'Código erroneo, intente con otro código' ], 400);
            }else if($material_info_1['BSTRF'] == "0.000" || $material_info_1 == "0"){
                return response()->json(['error' => 'Código de material con empaque de cero' ], 400);
            }else if($material_info_1 == null){
                return response()->json(['error' => 'Código no encontrado o erroneo' ], 400);
            }else if($material_type == "C"){
                return response()->json(['error' => 'Código erroneo. Producto Bajo Pedido' ], 400);
            }else if($material_type == "D"){
                return response()->json(['error' => 'Código erroneo. Producto Descontinuado' ], 400);
            }else if($material_type == "O"){
                return response()->json(['error' => 'Código erroneo. Producto Obsoleto' ], 400);
            }else if($material_type == "Z"){
                return response()->json(['error' => 'Código erroneo. Producto Pendiente de Verificación Comercial' ], 400);
            }else if($material_type == ""){
                return response()->json(['error' => 'Código de material sin genetica de producto, Centro: '.$material_type['WERKS'] ], 400);
            }else{
                //$material_code=$material_info_1['MATNR'];                
                $material_name=str_replace("\"", "",$material_info_1['MAKTX']); 
                $material_name=str_replace("'", "",$material_name);
                $material_name=str_replace("#", "",$material_name);
                $branch_office=$material_info_1['VKBUR'];
                $measure=$material_info_1['MEINS'];
                $stock=$material_info_1['LABST'];
                $stock_cdpt=$material_info_1['LABS1'];
                $transit=$material_info_1['TRAME'];
                $packing=$material_info_1['BSTRF'];
                //$price_list=$material_info_1['PLIST'];
                $amount=$material_info_1['KBETR'];
                //$actual_amount=$material_info_1['ZCOSTO3'];  
                $discount_amount=$material_info_1['PCDESC'];                
                $material_type=$material_info_1['MAABC'];
                $error=$material_info_1['MYERR'];
                $mall=$material_info_1['WERKS'];
                //$discount=$material_info_1['PDPER'];
                $sales_center=$material_info_1['WERKS']; 
                $sector=$material_info_1['SPART'];
                $pormar=$material_info_1['PORMAR'];
                $porcom=$material_info_1['PROCOM'];
                //$margin=$material_info_1['ZCOSTO3'];
                $inventory=$material_info_1['LABST'];
                $ZK14=$material_info_1['ZK14'];
                $ZK71=$material_info_1['ZK71'];
                $ZK73=$material_info_1['ZK73'];
                $ZK08=$material_info_1['ZK08'];
                $ZK66=$material_info_1['ZK66'];
                $ZK69=$material_info_1['ZK69'];
                $ZK25=$material_info_1['ZK25'];
                //$K004=$material_info_1['K004'];
                $margin_min = 13;
                //$gpom4_id = $material_info_1['GERPRO']; 
                $gpom4_name = $material_info_1['BEZEI'];
                $imagen_url = $material_info_1['IMGLS'];
                $array_packing = $material_info_1['AMATN'];
                $array_packing =explode(";", $array_packing);
                $array_packing_count = count($array_packing);
                $total= 0;
                for($k=0;$k < $array_packing_count;$k++){
                    $array_packing[$k] = explode(",", $array_packing[$k]);
                    $item=$array_packing[$k];
                    $total = $total + ($item[0]*($item[2]*$item[4]));
                    $item[5] = $item[0]*$item[2];
                    $item[6] = ($item[0]*($item[2]*$item[4]));
                    $array_packing[$k] = $item;
                }
                // Error de WS Mario 07/10/22
                $imagen_url = substr($imagen_url, 0, -20);
                // Tabla de margen minimo defindo actualizada 30/09/2020
                if ($sector != "") {
                    if ($sector == "00") {
                    //Alta tension
                    $margin_min = 15;
                    }else if ($sector == "05") {
                    //ARTEFACTOS ELECTRIC.
                    $margin_min = 15;
                    }else if ($sector == "11") {
                    //LAMPARAS LED
                    $margin_min = 15;
                    }else if ($sector == "12") {
                    //MICROINVERSOR
                    $margin_min = 7;
                    }else if ($sector == "13") {
                    //PANEL
                    $margin_min = 7;
                    }else if ($sector == "14") {
                    //KIT SOLAR
                    $margin_min = 7;
                    }else if ($sector == "20") {
                    //COBRE Y ALEACIONES
                    $margin_min = 10;
                    }else if ($sector == "30") {
                    //CONDUCTORES
                    $margin_min = 10;
                    }else if ($sector == "35") {
                    //CONTROLES
                    $margin_min = 15;
                    }else if ($sector == "36") {
                    //CONTROLES FORGAMEX
                    $margin_min = 15;
                    }else if ($sector == "37") {
                    //TRANSFORMADORES
                    $margin_min = 10;
                    }else if ($sector == "38") {
                    //CALENTADORES
                    $margin_min = 15;
                    }else if ($sector == "39") {
                    //TINACOS
                    $margin_min = 15;
                    }else if ($sector == "40") {
                    //ELECTROCERAMICA
                    $margin_min = 13;
                    }else if ($sector == "45") {
                    //ELECTROVIDRIO
                    $margin_min = 20;
                    }else if ($sector == "51") {
                    //EMPAQUES SINTETICOS
                    $margin_min = 15;
                    }else if ($sector == "63") {
                    //MERCADERIAS
                    $margin_min = 15;
                    }else if ($sector == "70") {
                    //MOLDEO DE PLASTICO
                    $margin_min = 15;
                    }else if ($sector == "71") {
                    //GABINETES
                    $margin_min = 15;
                    }else if ($sector == "72") {
                    //TUBERIA DE PLASTICO
                    $margin_min = 15;
                    }else if ($sector == "76") {
                    //TUBERIA CPVC
                    $margin_min = 10;
                    }else if ($sector == "90") {
                    //TUBERIA
                    $margin_min = 15;
                    }else if ($sector == "C3") {
                    //Material y EquipoMed
                    $margin_min = 35;
                    }
                }

                /*$sales_pormar_cien = number_format((100 - $pormar),2);
                $sales_minimum_price = number_format((($margin / $sales_pormar_cien) * 100),2);
                $sales_limit_manager_pctj = number_format(($pormar * 0.8),2);
                $sales_commercial_limit_pctj = number_format(($pormar * 0.1),2);
                $sales_limit_manager_hundred = number_format((100 - $sales_limit_manager_pctj),2);
                $sales_limit_commercial_hundred = number_format((100 - $sales_commercial_limit_pctj),2);
                $sales_limit_manager = number_format((($margin / $sales_limit_manager_hundred) * 100),2);
                $sales_limit_commercial = number_format((($margin / $sales_limit_commercial_hundred) * 100),2);
                */
                $errorcommercial = 0;
                $errormanager = 0;
                /*  if ($discount_amount <= $sales_limit_commercial) {
                    $errorcommercial = 1;
                  }

                if ($discount_amount >= $sales_limit_commercial) {
                    if ($discount_amount < $sales_limit_manager &&  $errorcommercial == 0) {
                        $errorcommercial = 1;
                    }
                }
        
                if ($discount_amount >= $sales_limit_manager) {
                    if ($discount_amount < $sales_minimum_price &&  $errorcommercial == 0) {
                        $errormanager = 1;
                    }
                }*/

                //existencias
                if ($stock>0) {
                    $stock_number= floor($stock/$packing);
                }else{
                    $stock_number=0;
                }
                if ($stock_cdpt>0) {
                    $stock_cdpt_number=floor($stock_cdpt/$packing);
                }else{
                    $stock_cdpt_number=0;
                }
                $total_stock = ($stock_number*$packing)+($stock_cdpt_number*$packing);
                if ($total_stock>=$units) {
                    $stock_label="SI";
                }else{
                    $stock_label="NO";
                }

                //$amount_discount_number = round((float)$discount_amount,2);
                //$actual_amount_number =  round((float)$actual_amount,2);
               
                //if ($amount_discount_number>=$actual_amount_number) {
                    if($stock_label == "SI"){
                        $product_amount=($units*$discount_amount); 
                        //$profit_margin=($discount_amount - $margin) * $units;
                        $validation = "Disponible";
                        $reminder = 0;
                    }else if($stock_label=="NO" && $total_stock>0){
                        $product_amount=($total_stock*$discount_amount); 
                        //$profit_margin=($discount_amount - $margin) * $total_stock;
                        $validation = "Parcial";
                        $reminder = $units-$total_stock;
                    }else if($stock_label=="NO" && $total_stock==0){
                        $product_amount=($total_stock*$discount_amount); 
                        //$profit_margin=($discount_amount - $margin) * $total_stock;
                        $validation = "Sin Existencia";
                        $reminder = $units-$total_stock;
                    }

                    $material_info_3 = [
                        'codigo_material' => $material_code,
                        'nombre_material' => $material_name,
                        'unidad_medida' => $measure,
                        'existencia' => $stock,
                        'existencia_cdpt' => $stock_cdpt,
                        'empaque' => $packing,
                        'u_pedidas' => $units,
                        //'u_confirm' => $rounding_packing,
                        'recordatorios' => $reminder,
                        //'precio_lista' => $price_list,
                        'importe_desciento' => $discount_amount,
                        'importe_producto' => $total,
                        'validacion' => $validation,
                        //'descuento' => $discount,
                        'ventas_centro' =>$sales_center,
                        'sector' =>$sector,
                        'pormar' =>$pormar,
                        'porcom' =>$porcom,
                        //'margen' =>$margin,
                        'inventario' =>$inventory,
                        //'K004' =>$K004,
                        'ZK14' =>$ZK14,
                        'ZK71' =>$ZK71,
                        'ZK73' =>$ZK73,
                        'ZK08' =>$ZK08,
                        'ZK66' =>$ZK66,
                        'ZK69' =>$ZK69,
                        'ZK25' =>$ZK25,
                        //'gpom4' => $gpom4_id,
                        //'margen_utilidad' => $profit_margin,
                        'margen_minimo_definido' => $margin_min,
                        'errorcomercial' => $errorcommercial,
                        'errorgerente' => $errormanager,
                        'image_url' => $imagen_url,
                        'array_packing'=> $array_packing                    
                        ];
                        return response()->json($material_info_3);
                /*}else{
                    return response()->json(['error' => 'Código erroneo. Revisar precio' ], 400);
                }*/
                 
            }
            
        }
    }

    public function purchaseValidation(Request $request){
        //funcion para obtener totales de la compra y validaciones de credito
        $shopping_cart = $request->shopping_cart;
        //return $shopping_cart;
        $sector_1 = "";
        $id_seller = $request->id_seller;
        $customer = $request->customer;
        $VKORG = $request->VKORG;
        $VTWEG = $request->VTWEG;
        $VKBUR = $request->VKBUR;
        $purchase_validation = 1;
        $subtotal =0;
        $iva =0;
        $total =0;
        $margin_util_total = 0;
        $errorgerente = 0;
        $errorcomercial = 0;
        //OBTENER INFORMACION DEL CLIENTE
        try{
            $service="http://172.16.171.10/WebServices/PGC360_Pro_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL"; 
            $parameters=array();
            $parameters['Username']="$id_seller";
            $parameters['KKBER']="217";
            $parameters['KUNNR1']="$customer";                
            $parameters['VKORG']=$VKORG;
            $parameters['VTWEG']=$VTWEG;
            $client = new SoapClient($service,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result = $client->Vb_Cliente_Datosgrales($parameters);
            $result = obj2array($result);
            $result=$result['Vb_Cliente_DatosgralesResult']['MyResultData'];
            $customer_data = collect($result);
            $customer_data = $customer_data->first();
        } catch (Exception $e) {
            $customer_data = [];
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        $credit = str_replace('/','/gi', "",$customer_data['CREDD']);
        if (count($shopping_cart) == 1) {
            $shopping_cart_item = $shopping_cart[0];
            $sector_1 = $shopping_cart_item["sector"];
        }
        for ($i=0; $i < count($shopping_cart) ; $i++) {
            $shopping_cart_item = $shopping_cart[$i];
            $product_amount = $shopping_cart_item["importe_producto"];
            $margin = $shopping_cart_item["margen_utilidad"];
            $mimim_margin = $shopping_cart_item["margen_minimo_definido"];
            $sector2 = $shopping_cart_item["sector"];
            if ($sector2 != $sector_1) {
            $mimim_margin = 8; //mezcla
            }
            $subtotal += (float) $product_amount;
            $margin_util_total += $margin;
            $errorgerente += $shopping_cart_item["errorgerente"];
            $errorcomercial += $shopping_cart_item["errorcomercial"];
        }
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;
        $margen_total= $margin_util_total / ($subtotal * 100);
        if ($margen_total< $mimim_margin) {
            $errorgerente = $errorgerente + 1;
        }
        $credit_total = $credit-$total;
        if ($credit_total < 1) {
            $purchase_validation = 2; //error credito
        }
        if ($purchase_validation == 1) //si no hay problema de credito
            {
            if ($errorgerente == 0 && $errorcomercial == 0) {
                $message_error ="";
                $purchase_validation = 1;
            } else if ($errorcomercial != 0 && $errorgerente == 0) {
                $message_error = 'El pedido será bloqueado para autorización gerencia de planeación.';
                $purchase_validation = 3;
            } else if ($errorcomercial == 0 && $errorgerente != 0) {
                $message_error = 'El pedido será bloqueado para autorización de la gerencia.';
                $purchase_validation = 5;
            } else if ($errorcomercial != 0 && $errorgerente != 0) {
                $message_error = 'El pedido será bloqueado para autorización de la gerencia y comercial.';
                $purchase_validation = 7;
            }
        }
        if ($purchase_validation  == 2) //si hay problemas de credito
        {
            if ($errorgerente == 0 && $errorcomercial == 0) {
                $message_error = 'El pedido será bloqueado por crédito';
                $purchase_validation = 2;
            } else if ($errorcomercial != 0 && $errorgerente == 0) {
                $message_error = 'El pedido será bloqueado para autorización comercial y crédito.';
                $purchase_validation = 4;
            } else if ($errorcomercial == 0 && $errorgerente != 0) {
                $message_error = 'El pedido será bloqueado para autorización de la gerencia y crédito.';
                $purchase_validation = 6;
            } else if ($errorcomercial != 0 && $errorgerente != 0) {
                $message_error = 'El pedido será bloqueado para autorización de la gerencia, comercial y crédito.';
                $purchase_validation = 8;
            }
        }
        $data = [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'purchase_validation' => $purchase_validation,
            'message_error' => $message_error];

        return response()->json($data); 
    }

    public function generateOrder(Request $request){
        $id_seller = $request->id_seller;
        $customer = $request->customer;
        $VKORG = $request->VKORG;
        $VTWEG = $request->VTWEG;
        $VKBUR = $request->VKBUR;
        $purchase_order= $request->purchase_order;
        $destination_purchase = $request->destination_purchase;
        $shopping_cart = $request->shopping_cart;
        $purchase_validation = $request->purchase_validation;
        $type_of_payment = $request->type_of_payment;
        $payment_method = $request->payment_method;
        $cfdi_use = $request->cfdi_use;        
        $item_number=10;
        $productos="";
        $z13="";
        $recordatorio=""; 
        if ($type_of_payment == null) {
            $type_of_payment = "";
        }
        if ($payment_method == null) {
            $payment_method = "";
        }
        if ($cfdi_use == null) {
            $cfdi_use = "";
        }
        $rebilling = $request->rebilling;
        $documents = $request->documents;
        if ($documents == null) {
            $documents = "";
        }
        if ($rebilling == null) {
            $rebilling = "";
        }
        $scheduled_order = $request->scheduled_order;
        $reminder_date = $request->reminder_date;
        if ($scheduled_order == true) {
        $reminder_date = str_replace("-", ".",$reminder_date);
        }else{
        $reminder_date = date("m.d.Y");
        }
        $current_date= date("Y-m-d H:i:s");
        $shopping_cart_count=count($shopping_cart);
        for ($i=0; $i < $shopping_cart_count ; $i++) {
            $cart_item = $shopping_cart[$i];
            $material_code=$cart_item['codigo_material'];
            $units_confirm=$cart_item['u_confirm'];
            $units_record=$cart_item['recordatorios'];
            $total_units = $units_confirm + $units_record;
            //adaptar contador del material en la lista 
            $n2=strlen($item_number);
            $n2_aux=6-$n2;
            $pos="";
            for ($j1=0; $j1 <$n2_aux ; $j1++) { 
            $pos.="0";
            }
            $item_id=$pos.$item_number;
            //completar el código de material a 18 digitos
            $n1=strlen($material_code);
            $n1_aux=18-$n1;
            $mat="";
            for ($i=0; $i <$n1_aux ; $i++) { 
                $mat.="0";
            }
            $material_code=$mat.$material_code;
            try{
                $service1="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
                $parameters1=array();  
                $parameters1['VKBUR']="$VKBUR";
                $parameters1['MATNR']="$material_code";
                $parameters1['KUNNR']="$customer";
                $parameters1['VTWEG']="$VTWEG";
                $parameters1['VKORG']="$VKORG";
                $parameters1['CANT']="$total_units";
                $client1 = new SoapClient($service1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
                $result1 = $client1->Vb_Mater_Exist_Precios2($parameters1);
                $result1 = obj2array($result1);
                $result1 = $result1['Vb_Mater_Exist_Precios2Result']['MyResultData'];
                $material_info_1 = collect($result1);
                $material_info_1 = $material_info_1->first();
            }catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            }
            $stock=$material_info_2['LABST'];
            $stock_cdpt=$material_info_2['LABS1'];
            $packing=$material_info_2['BSTRF'];          
            $cdpt=$consultaMat['RUCDP'];
            $route=$consultaMat['WERKS'];
            $route_altern=$consultaMat['CECDP'];
            if ($packing == "1.111") {
                $packing = "1";
            }
            $stock1= floor($stock/$packing);
            $stock2= floor($stock_cdpt/$packing);
            $stock1=$stock1*$packing;
            $stock2=$stock2*$packing;
            $total_stock=$stock1+$stock2;
            if ($scheduled_order == true) {
            $total_stock = 0;
            }
            $cant=$total_units; 
            $toma2=0;
            if ($total_stock>=$packing) {
                //proceso cuando hay cantidades disponibles
                if ($stock1>=$total_units) {
                    //Proceso de CEDIS
                    $item_id=$pos.$item_number;  
                    $parte1="$item_id,$material_code,";
                    $z="$item_id,ZK13,0;";
                    $parte2="$total_units,$route,,,;";
                    $productos.=$parte1.$parte2;
                    $z13.=$z;
                    $total_units=0;
                }
                if ($stock1>=$packing && $stock1<$total_units) {
                    //Proceso COMBINADO CEDIS Y CDPT, se genera linea de CEDIS
                    $parte1="$item_id,$material_code,";
                    $z="$item_id,ZK13,0;";
                    $total_units=$total_units-$stock1;
                    $parte2="$stock1,$route,,,;";
                    $productos.=$parte1.$parte2;
                    $z13.=$z;
                    $toma2=1;            
                }
                if($total_units>0){
                    //se crea otra linea de material para CDPT
                    if ($toma2==1) {
                        //adaptar contador del material en la lista 
                        $item_number = $item_number+10;
                        $n2=strlen($item_number);
                        $n2_aux=6-$n2;
                        $pos="";
                        for ($j1=0; $j1 <$n2_aux ; $j1++) { 
                        $pos.="0";
                        }
                        $item_id=$pos.$item_number;
                        if ($stock2>=$packing) {
                            if ($total_units>0 && $total_units>$stock2) {
                                $total_units=$total_units-$stock2; 
                                $parte1="$item_id,$material_code,";
                                $parte2="$stock2,$route_altern,,$cdpt,;";
                                $z="$item_id,ZK13,0;";
                                $productos.=$parte1.$parte2;
                                $z13.=$z;
                                $recordatorio.="$customer,$material_code,$total_units,$reminder_date;";  
                            }else{  
                                $parte1="$item_id,$material_code,";
                                $z="$item_id,ZK13,0;";
                                $parte2="$total_units,$route_altern,,$cdpt,;";
                                $productos.=$parte1.$parte2;
                                $z13.=$z;
                            }
                        }else{
                            $total_units=$total_units-$stock2;
                            if ($total_units>0) {
                                $recordatorio.="$customer,$material_code,$total_units,$reminder_date;";   
                            }else{
                                $parte1="$item_id,$material_code,";
                                $z="$item_id,ZK13,0;";
                                $parte2="$total_units,$route_altern,,$cdpt,;";
                                $productos.=$parte1.$parte2;
                                $z13.=$z;
                            }
                        }
                    }else{
                        if ($stock2>=$packing) {
                            if ($total_units>0 && $total_units>$stock2) {
                                $total_units=$total_units-$stock2;
                                $item_id=$pos.$item_number;  
                                $parte1="$item_id,$material_code,";
                                $parte2="$stock2,$route_altern,,$cdpt,;";
                                $z="$item_id,ZK13,0;";
                                $productos.=$parte1.$parte2;
                                $z13.=$z;
                                $recordatorio.="$customer,$material_code,$total_units,$reminder_date;";  
                            }else{
                                $item_id=$pos.$item_number;  
                                $parte1="$item_id,$material_code,";
                                $z="$item_id,ZK13,0;";
                                $parte2="$total_units,$route_altern,,$cdpt,;";
                                $productos.=$parte1.$parte2;
                                $z13.=$z;
                            }
                        }else{
                            $total_units=$total_units-$stock2;
                            if ($cant>0) {
                                $reminder_date= date("m.d.Y");
                                $recordatorio.="$customer,$material_code,$total_units,$reminder_date;";
                            }else{
                                $item_id=$pos.$item_number; 
                                $parte1="$item_id,$material_code,";
                                $parte2="$total_units,$route_altern,,$cdpt,;";
                                $z="$item_id,ZK13,0;";
                                $productos.=$parte1.$parte2;
                                $z13.=$z;
                            }
                        }
                    }
                }
                $item_number=$item_number+10;
            }else{
                $recordatorio.="$customer,$material_code,$total_units,$reminder_date;";
            }         
        }//end for
        $date= date("Ymd");
        $productos = substr($productos, 0, -1);
        $z13 = substr($z13, 0, -1);
        $recordatorio = substr($recordatorio, 0, -1);
        try {
            $service2="http://172.16.176.25/WebServices/PGC360_Des_CrearPedido/CrearPedido.asmx?WSDL";
            //$service2="http://172.16.171.10/WebServices/PGC360_Pro_CrearPedido/CrearPedido.asmx?WSDL"; 
            $parameters2=array();
            $parameters2['ZTERM']="";
            $parameters2['Doc_Type']="PSIU";
            $parameters2['Sales_Org']=$VKORG;
            $parameters2['Distr_Chan']=$VTWEG;
            $parameters2['Division']="90";
            $parameters2['Folio']="";
            $parameters2['Purch_No_C']=$purchase_order; 
            $parameters2['Purch_No_S']="";
            $parameters2['Purch_Date']=$date; 
            $parameters2['Username']=$id_seller;
            $parameters2['Uv']=$id_seller;
            $parameters2['Partn_Rolea']="AG";
            $parameters2['Partn_Numba']=$customer; 
            $parameters2['Partn_Roleb']="WE";
            $parameters2['Partn_Numbb']=$destination_purchase; 
            $parameters2['Name_2']="";
            $parameters2['CreCo']=$purchase_validation; 
            $parameters2['ItemArray_Rec']=$recordatorio; 
            $parameters2['ItemArray_S']=$productos; 
            $parameters2['ItemArrayZK_S']=$z13; 
            $parameters2['IDORRDEM']="0"; 
            $parameters2['IDSE']="0";
            $parameters2['VTWEG']=$VTWEG;
            $parameters2['MPago']="$type_of_payment"; 
            $parameters2['FPago']="$payment_method";
            $parameters2['UCFDI']="$cfdi_use";
            $parameters2['DocRel']="$documents";
            $parameters2['TRCFDI']="$rebilling";
            return $parameters2;
            $client2 = new SoapClient($service2,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result2 = $client2->Vb_CrearPedido($parameters2);
            $result2 = obj2array($result2);
            $result2=$result2['Vb_CrearPedidoResult'];
            $result2 = collect($result2);
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
            $error=$result2['MYERR'];
            $message=$result2['FNMSG']; 
            if ($error == 0) {
                $data=['message' => $message,
                        'numero_pedido' => $result2['ORNUM'],
                        'numero_entrega' => $result2['ENTNUM'],
                        'numero_factura' => $result2['FACNUM'],
                    ];
            }else{
                $data=['message' => 'Error en servidor'.$message , 
                        'numero_pedido' => 'ERROR EN SERVIDOR',
                        'numero_entrega' => '',
                        'numero_factura' => '',                      
                    ];
            }
            return response()->json($data,200);
    }


}

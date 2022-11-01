<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;

class CustomerController extends Controller
{
    //
    public function getCustomerInformation(Request $request){
        $client_code = $request->client_code;
        $society = $request->society;
        $organization = $request->organization;
        $channel = $request->channel;
    	try {
			$service="http://172.16.176.25/webservices/IEL_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL";
			$parameters=array();
			$parameters['KKBER']=$society;
			$parameters['KUNNR1']=$client_code;
			$parameters['VKORG']=$organization;
			$parameters['VTWEG']=$channel;
			$client = new SoapClient($service,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
			$result = $client->Vb_Cliente_Datosgrales($parameters);
			$result = obj2array($result);
			$customer_data=$result['Vb_Cliente_DatosgralesResult']['RDCliente_Datosgrales'];
			$collection = collect($customer_data);
			return response()->json(
				$collection
			);
		} catch (Exception $e) {
		    trigger_error($e->getMessage(), E_USER_WARNING);
		}
    } 
}

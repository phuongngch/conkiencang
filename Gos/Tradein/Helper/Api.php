<?php

namespace Gos\Tradein\Helper;
use \SoapClient;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
	const BASE_API_URL = 'http://www.holden.com.au';

    protected $zendClientFactory;

    protected $objectManager;

    protected $logger;

    protected $base_api_url;


    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\HTTP\ZendClientFactory $zendClientFactory
    )
    {
        $this->zendClientFactory = $zendClientFactory;
        $this->objectManager = $objectManager;
        // Logger
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/tradein.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->logger = $logger;

        parent::__construct($context);

        $this->base_api_url = $this->getConfigValue('nevdis_settings','base_api_url', false) ? $this->getConfigValue('nevdis_settings','base_api_url', false) : self::BASE_API_URL;
    }

    public function isEnabled()
    {
        return (bool)$this->getConfigValue('nevdis_settings','enabled', false);
    }

    public function getConfigValue($group = null, $key = null, $defaultValue = null)
    {
        $value = $this->scopeConfig->getValue(
            'gostradein/' . $group .'/'. $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }


    public function curlSendPostRequest($url,$authentication,$request){
        //$authentication = base64_encode("username:password");

        $ch = curl_init($url);
        $options = array(
                CURLOPT_RETURNTRANSFER => true,         // return web page
                CURLOPT_HEADER         => false,        // don't return headers
                CURLOPT_FOLLOWLOCATION => false,         // follow redirects
               // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
                CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
                CURLOPT_TIMEOUT        => 20,          // timeout on response
                CURLOPT_POST            => 1,            // i am sending post data
                CURLOPT_POSTFIELDS     => $request,    // this are my post vars
                CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                CURLOPT_SSL_VERIFYPEER => false,        //
                CURLOPT_VERBOSE        => 1,
                CURLOPT_HTTPHEADER     => array(
                    "Authorization: $authentication",
                    "Content-Type: application/json"
                )

        );

        curl_setopt_array($ch,$options);
        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        //echo $curl_errno;
        //echo $curl_error;
        curl_close($ch);
        return $data;
    }

    public function getVehicleData($state,$plate)
    {
        // make request get vehicle data
        // Request API to NEVDIS to get Data

		$post = array();

		if ($state!='' && $plate!='') {

        $nevdis_authorization = $this->getConfigValue('nevdis_settings','nevdis_authorization', false);

        $nevdis_url = $this->getConfigValue('nevdis_settings','base_api_url', false);

        //$query = '{"query":"query {nevdisVINSearch(vin:\"WBABD32060PL85113\") {vin plate {number state} make model } }"}';
        $query = '{"query":"query {\n nevdisPlateSearch(plate:\"'.$plate.'\",state:'.$state.') {\n vin\n plate {\n number\n state\n }\n make\n model\n year_of_manufacture\n}\n}"}';

        $json_return = $this->curlSendPostRequest($nevdis_url,$nevdis_authorization,$query);

        /*
        $json_return = '{
            "nevdisVINSearch": {
            "vin": "XXXXXXXXXXXXXXXX",
            "plate": {
            "number": "ABC123",
            "state": "VIC"
            },
            "make": "HOLDEN",
            "model": "COMMOD"
            }
        }';
        */

        $obj = json_decode($json_return);

		if (is_object($obj)) {

        $post = array(
            'year' => $obj->data->nevdisPlateSearch->year_of_manufacture,
            'vin' => $obj->data->nevdisPlateSearch->vin, 
            'license' => $obj->data->nevdisPlateSearch->plate->number,
            'state' => $obj->data->nevdisPlateSearch->plate->state,
            'vehicle_make' => trim($obj->data->nevdisPlateSearch->make), 
            'vehicle_model' => trim($obj->data->nevdisPlateSearch->model), 
        );
        
		}
        

		}

        return $post;
    }

    public function getValuation($odometer,$derivativeCode)
    {
        // make request get Valuation

        $manheim_wsdl = $this->getConfigValue('manheim_settings','manheim_wsdl', false);

        $manheim_vendor = $this->getConfigValue('manheim_settings','manheim_vendor', false);
        $manheim_company = $this->getConfigValue('manheim_settings','manheim_company', false);
        $manheim_username = $this->getConfigValue('manheim_settings','manheim_username', false);
        $manheim_password = $this->getConfigValue('manheim_settings','manheim_password', false);

        if ($odometer > 0 && $derivativeCode !='' ) {

            $client = new SoapClient($manheim_wsdl);

            $params = array( 
                    "vendor" => $manheim_vendor,
                    "companyCode" => $manheim_company,
                    "user" => $manheim_username,
                    "password" => $manheim_password,
                    "odometer" => $odometer,
                    "derivativeCode" => $derivativeCode); 


            //$response = $client->GetBasicValuation($params);
            //return $response->GetBasicValuationResult->Amount;

			try {
 				$response = $client->GetBasicValuation($params);
                return $response->GetBasicValuationResult->Amount;
			} catch (SoapFault $E) {
 				$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/manheim-api.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('Error: '.$E->faultstring.'; derivativeCode: '.$derivativeCode);

			}


        }else{

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/manheim-api.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Odo: '.$odometer.'; derivativeCode: '.$derivativeCode);

            return 0;
        }
    }

    public function getNvic ($vinNumber,$vehicleYear) {

        $soapUrl = "https://vindicator.polk.com/WebService/VINMatching.asmx";

        $CompanyCode = $this->getConfigValue('polk_settings','polk_company_code', false);

        $polk_username = $this->getConfigValue('polk_settings','polk_username', false);

		if ($vinNumber != '' && $vehicleYear!='') {


		$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:vin="http://www.polk.com.au/VINMatching/"> <soapenv:Header> <vin:AuthHeader> <vin:UserName>PolkServiceUser</vin:UserName> <vin:Password>PolkServicePassword</vin:Password> <vin:errorMessage></vin:errorMessage> </vin:AuthHeader> </soapenv:Header> <soapenv:Body> <vin:VinMatch> <vin:requestObject> <vin:CompanyCode>'.$CompanyCode.'</vin:CompanyCode> <vin:UserName>'.$polk_username.'</vin:UserName> <vin:RetrievalType>DRDT_0002</vin:RetrievalType> <vin:Vin>'.$vinNumber.'</vin:Vin> <vin:VehicleYear>'.$vehicleYear.'</vin:VehicleYear> <vin:UserDefinedKey>Test</vin:UserDefinedKey> </vin:requestObject> </vin:VinMatch> </soapenv:Body> </soapenv:Envelope>';

        $headers = array(
        "POST /WebService/VINMatching.asmx HTTP/1.1",
        "Host: vindicator.polk.com",
        "Content-type: text/xml;charset=utf-8",
        "Content-Length: ".strlen($xml_post_string)
        ); 

        $url = $soapUrl;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch); 
        curl_close($ch);

        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        $parser = simplexml_load_string($response2);
		
		$NvicObj = $parser->VinMatchResponse->VinMatchResult->Output->Nvic;

        $Nvic =  (array) $NvicObj;

    		if (is_object($NvicObj)) {
    			return $Nvic[0];
    		}else{

                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/polk-api.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('vinNumber: '.$vinNumber.'; vehicleYear: '.$vehicleYear);

    			return null;
    		}

		}else{
			
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/polk-api.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('vinNumber: '.$vinNumber.'; vehicleYear: '.$vehicleYear);

                return null;

		}

    }
   
	protected function makeRequest($url, $method=\Zend_Http_Client::GET)
	{
		$client = $this->zendClientFactory->create();
		$client->setUri($this->base_api_url . $url);
		$client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
		// $client->setRawData(utf8_encode($request));
		return json_decode($client->request($method)->getBody());	
	}
}
<?php
namespace Libraries;

use Libraries\Helpers;
use \Exception;

class Curl {
    private $options;
    private $customRequest = "POST";
    private $headers = ['Content-Type: application/json; charset= UTF-8'];
    private $helpers;
    private $params = [];
    private $postFields = "";
    private $ssl = false;
    private $timeout = 2;
    private $url = "";
    private $port = 80;
    private $requestQueue = [];
    
    
    public function __construct($options = [])
    {
        (count($options) === 0) ?: $this->_setParams($options);
        
        $this->helpers = new Helpers();
    }
    
    /**
     * executa um request usando o curl (nativo do PHP), 
     * @param array $options - opções necessárias ao cUrl, caso não sejam informadas, utilizam valores padrão definidos na classe
     * Exemplo:
     * 
     * @return array - retorna um array com a resposta do curl, caso haja erro no request, retorna as mensagens e os códigos de erro também
     */
    public function request($options = [])
    {
        try {

            (count($options) === 0) ?: $this->_setParams($options);
            
            if($this->helpers->validUrl($this->url)){
                $curl = curl_init($this->url);

                $this->options = [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => '',    
                    CURLOPT_SSL_VERIFYPEER => $this->ssl,
                    CURLOPT_SSL_VERIFYHOST => $this->ssl,
                    CURLOPT_MAXREDIRS      => 1,
                    CURLOPT_CONNECTTIMEOUT => $this->timeout,
                    CURLOPT_TIMEOUT        => $this->timeout,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => strtoupper($this->customRequest),
                    CURLOPT_POSTFIELDS     => $this->postFields,
                    CURLOPT_HTTPHEADER     => $this->headers,
                ];

                if(!is_null($this->port)){
                    $this->options[CURLOPT_PORT] = $this->port;
                }

                curl_setopt_array($curl,$this->options);

                $arr = [
                    "response" => curl_exec($curl),
                    "httpcode" => curl_getinfo($curl,CURLINFO_HTTP_CODE),
                    "errorcode" => curl_errno($curl), 
                    "errormessage" => curl_error($curl),
                    "totalTime" => curl_getinfo($curl,CURLINFO_TOTAL_TIME)
                ];
                
                curl_close($curl);
                
                return $arr;
            }
            
            return [
                'response' => ['status' => false, 'message' => $this->helpers->convertSpecialCharsPT('URL inválida')],
                'httpcode' => 400,
                'errorcode' => 400,
                'errormessage' => $this->helpers->convertSpecialCharsPT('URL inválida')
            ];
        } catch (Exception $ex) {
            trigger_error($ex->getMessage(),$ex->getCode());
            return [
                'response' => ['status' => false, 'message' => 'Serviço indisponível, por favor tente mais tarde', 'error' => $ex->getMessage()],
                'httpcode' => $ex->getCode(),
                'errorcode' => 504,
                'errormessage' => 'Não houve resposta da tentativa de request por parte do serviço'
            ];
        }
    }

    public function appendRequest(...$requests)
    {
        if (isset($requests) && count($requests) > 0){
            $result = array_map(
                function ($item) {
                    return $this->request($item);
                },
                $requests
            );

            return $result;
        }

        return false;
    }
    
    /**
     * Seta os parâmetros necessários ao cUrl para request
     * @param array $params - array contendo os parâmetros, os valores serão setados nas propriedades da classe
     */
    private function _setParams($params)
    {
        $this->params = $params;
        
        $this->_setCustomRequest();
        $this->_setHeaders();
        $this->_setPostFields();
        $this->_setPort();
        $this->_setSsl();
        $this->_setTimeout();
        $this->_setUrl();
    }
    
    /**
     * Seta o http method de request (POST,GET,DELETE,PUT...)
     */
    private function _setCustomRequest()
    {
        $this->customRequest = (count($this->params) > 0 && $this->params["method"])
        ? $this->params["method"]
        : $this->customRequest;
    }
    
    /**
     * Seta os cabeçalhos do request
     */
    private function _setHeaders()
    {
        $headers = array_map(function($item){
            return $item;
        },$this->headers);
        
        $this->headers = null;
        
        $this->headers = (count($this->params) > 0 && $this->params["headers"])
        ? $this->params["headers"]
        : $headers;
    }
    
    /** 
     * seta os campos e valores a serem enviados no corpo da requisição
     */
    private function _setPostFields()
    {
        $this->postFields = (count($this->params) > 0 && isset($this->params["dataFields"]))
        ? $this->params["dataFields"]
        : $this->postFields;
    }
    
    /**
     * seta o parâmetro port
     */
    private function _setPort()
    {
        if($this->port !== false){
            if(isset($this->params['url']) && strstr($this->params['url'],"https") !== false){
                $this->port = 443;
            }
    
            if(count($this->params) > 0 && isset($this->params["port"]) && is_numeric($this->params["port"])){
                $this->port = $this->params["port"];
            }    
        }
    }

    /**
     * Seta se o request será realizado em modo ssl 
     */
    private function _setSsl()
    {
        $this->ssl = (isset($this->params["ssl"]) && count($this->params) > 0)
        ? $this->params["ssl"]
        : $this->ssl;
    }
    
    /**
     * Seta de quanto será o timeout
     */
    private function _setTimeout()
    {
        $this->timeout = (count($this->params) > 0 && $this->params["timeout"])
        ? $this->params["timeout"]
        : $this->timeout;
    }
    
    /**
     * seta a url do serviço para o qual a request será realizado
     */
    private function _setUrl()
    {
        $this->url = (count($this->params) > 0 && $this->params["url"])
        ? $this->params["url"]
        : $this->url;
    }
}

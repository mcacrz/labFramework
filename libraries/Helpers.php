<?php
namespace Libraries;

class Helpers {
    
    /**
     * Método mágico do PHP para obter o valor da propriedade da classe
     * @param string $name - nome da propriedade
     * @return type - retorna o valor da propriedade
     */
    public function __get($name) {
        return $name;
    }

    /**
     * Método para converter caracteres em português acentuados para caracteres especiais HTML (&atilde;,&eacute;, etc...), retornando o texto com o charset informado nos parâmetros
     * @param string $string - texto a ser convertido
     * @param string $charset - cojunto de caracteres para o qual o texto deve ser convertido no final
     * @return string - retorna a frase convertida
     */
    function convertSpecialCharsPT($string,$charset = 'UTF-8')
    {
       return str_replace("&amp;","&",htmlentities($string,ENT_COMPAT,$charset)); 
    }

    function formatCurrency($value)
    {
        if(is_numeric($value))
        {
            $valueWithNoSpecialChars = str_replace(",","",str_replace(".", "", $value));
            
            $currency = substr($value,0,(strlen($value) - 2));
            $cents = substr($value,(strlen($value) - 2),2);

            return floatval($currency.".".$cents);
        }
        
        return false;
    }
    /**
     * 
     * @param type $cnpj
     * @return boolean
     */
    function isCnpjValid($cnpj) {
        //Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
        $j = 0;
        for ($i = 0; $i < (strlen($cnpj)); $i++) {
            if (is_numeric($cnpj[$i])) {
                $num[$j] = $cnpj[$i];
                $j++;
            }
        }
        //Etapa 2: Conta os dígitos, um Cnpj válido possui 14 dígitos numéricos.
        if (count($num) != 14) {
            $isCnpjValid = false;
        }
        //Etapa 3: O número 00000000000 embora não seja um cnpj real resultaria um cnpj válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
        if ($num[0] == 0 && $num[1] == 0 && $num[2] == 0 && $num[3] == 0 && $num[4] == 0 && $num[5] == 0 && $num[6] == 0 && $num[7] == 0 && $num[8] == 0 && $num[9] == 0 && $num[10] == 0 && $num[11] == 0) {
            $isCnpjValid = false;
        }
        //Etapa 4: Calcula e compara o primeiro dígito verificador.
        else {
            $j = 5;
            for ($i = 0; $i < 4; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 4; $i < 12; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            if ($dg != $num[12]) {
                $isCnpjValid = false;
            }
        }
        //Etapa 5: Calcula e compara o segundo dígito verificador.
        if (!isset($isCnpjValid)) {
            $j = 6;
            for ($i = 0; $i < 5; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 5; $i < 13; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            if ($dg != $num[13]) {
                $isCnpjValid = false;
            } else {
                $isCnpjValid = true;
            }
        }
        //Etapa 6: Retorna o Resultado em um valor booleano.
        return $isCnpjValid;
    }
    
    /**
     * Verifica se o CPF é válido
     * @param string $value - cpf a ser validado
     * @return boolean - retorna se o cpf é válido ou não
     */
    function isCpfValid($value){
        $cpf = str_pad(preg_replace('/\D/', '', $value), 11, '0', STR_PAD_LEFT);

        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
            return false;
        } 
        else 
        {
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }

                $d = ((10 * $d) % 11) % 10;

                if ($cpf{$c} != $d) {
                    return false;
                }
            }
        }

        return true;        
    }
    
    /**
     * Junta dois arrays em um, usado para montar os arrays de values dos métodos de inserts das classes Models
     * @param array $arrFields - array com os campos que receberão os dados
     * @param array $arrValues - array com os valores que serão inseridos na tabela
     * @return array - retorna um array de key => value gerado a pertir dos arrays informados
     */
    function setArray($arrFields,$arrValues)
    {
        $arrResponse = array_map(function($key, $value){
          return (is_array($key))
          ? $key
          : [$key => $value];
        },$arrFields,$arrValues);

        return array_reduce($arrResponse,function($result,$item){
          $result[array_keys($item)[0]] = array_values($item)[0];
          return $result;
        },[]);
    }
    
    /**
     * Valida os campos passados em $fields com os campos passados em $paymentData, verifica se os campos possuem seus respectivos valores e, se os mesmos batem com os valores passados no parâmetro $fields
     * @param array $paymentData - array com os valores
     * @param array $fields - array com os campos
     * @return boolean - retorna se são válidos ou não
     */
    function verifyFieldsRequired($paymentData,$fields)
    {       
        $arrFields = array_keys($fields['requiredFields']);
        
        $arrDataTypes = ["string" => "string", "integer" => "number", "double" => "number", "boolean" => "boolean"];

        $arrValidateFunctions = [
            "string" => function($key,$value){
                $arrNameField = [
                    'documento' => function($value){
                        if (strlen($value) === 14) {
                            return $this->isCnpjValid($value);            
                        }
                        if (strlen($value) === 11) {
                            return $this->isCpfValid($value);
                        }
                    },
                    'default' => function(){ return true; }
                ];

                return $arrNameField[($key === 'documento') ? $key : 'default']($value);
            },
            "number" => function($key,$value){
                return is_numeric($value);
            },
            "bool" => function($key,$value){
                return true;
            }
        ];
        
        $arrValid = array_map(function($item) use ($paymentData,$fields,$arrDataTypes,$arrValidateFunctions){  
            $firstValidation = (array_key_exists($item,$paymentData) === true && !is_null($item) && gettype($paymentData[$item]) === $fields['requiredFields'][$item]['dataType'] && strlen($paymentData[$item]) > 0)
            ? ['status' => true]
            : ['status' => false, 'field' => $item];
            
            $secondValidation = (gettype($paymentData[$item]) !== 'NULL')
            ? ['status' => $arrValidateFunctions[$arrDataTypes[gettype($paymentData[$item])]]($item,$paymentData[$item])]
            : ['status' => false, 'field' => 'item'];
            
            if($fields['requiredFields'][$item]['required'] === true){
                return ($firstValidation['status'] === true && $secondValidation['status'] === true) ? ['status' => true] : ['status' => false, 'fields' => $item];
            }

            return ['status' => true];
            
        },$arrFields);

        return (count(array_filter($arrValid,function($item){ return $item['status'] === false;})) === 0)
        ? ['status' => true]
        : ['status' => false , 'fields' => array_filter($arrValid,function($item){ return $item['status'] === false;})];        
    }
    
    function validUrl($url)
    {
        $urlClean = (preg_replace('/:[0-9]{1,}/','',$url));
        
        if(filter_var($urlClean,FILTER_VALIDATE_URL) === false){
            return false;
        }
        
        if(strstr($urlClean,'https') !== false){
            stream_context_set_default( [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]); 
        }

        $headers = get_headers($urlClean);
        
        return (stripos($headers[0],"504") !== false || stripos($headers[0],"404") !== false) ? false : true;
    }
}


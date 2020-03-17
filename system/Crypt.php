<?php
namespace System;

class Crypt
{
    public function __contruct(){}

    /**
     * Descriptografa o que for passado no parâmetro data
     * @param string $data - informação a ser descriptografada
     * @param string $ivkey - string em base64 contendo o secret e o ivkey separados por :: 
     * @return string - retorna o dado descriptografado
     */
    public function decrypt($data, $ivkey)
    {
        list($encryption_key,$iv) = array_pad(explode('::',base64_decode($ivkey),2),2,null);
        $encrypted_data = base64_decode($data);
        return openssl_decrypt($encrypted_data, 'AES-256-CBC', $encryption_key, 0, $iv);
    }

    /**
     * Criptografa o que for passado no parâmetro data
     * @param string $data - informação a ser Criptografada
     * @param string $ivkey - string em base64 contendo o secret e o ivkey separados por :: 
     * @return string - retorna o dado Criptografado
     */    
    public function encrypt($data, $ivkey, $isBase64 = true)
    {
        list($encryption_key,$iv) = array_pad(explode('::',base64_decode($ivkey),2),2,null);
        $encrypted = openssl_encrypt($data, 'AES-128-CBC', $encryption_key, 0,$iv);
        return ($isBase64 === true) ? base64_encode($encrypted) : $encrypted;
    }
    
    /**
     * Gera um hash a partir do dado passado no parâmetro data
     * @param type $data - dado sobre o qual será gerado o hash
     * @return string - retorna o hash do dado informado
     */
    public function hashing($data)
    {
        return hash("sha256",$data);
    }
    
    /**
     * gera um ivkey e um secret randômico para criptografar e descriptografar os dados de autenticação
     * @param boolean $isBase64 - informa se é será necessário codificar o ivkey e o secret em base64 (caso seja true). Por padrão, ele é true
     * @return type
     */
    public function randomSecret($isBase64 = true)
    {
        return $this->_getRandomUniqId();
    }
    
    /**
     * gera um id único através da função uniqid e rand, ambas nativas do PHP
     * @return type
     */
    private function _getRandomUniqId()
    {
        $uniqid = hash("sha256",uniqid(rand()));
        return $uniqid;
    }      
}



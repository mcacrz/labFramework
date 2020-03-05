<?php
namespace Libraries;

use PDO;
use PDOException;

class DB {
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Retorna quantas linhas possui a tabela pesquisada
     * @param string $table - nome da tabela
     * @return int - retorna a quanidade de linhas da tabela
     */
    public function rowCount($table)
    {
        return $this->select($table,null,"COUNT(*)");
    }
    
    /**
     * Monta uma query de select e executa no database e devolve seu resultado
     * @param string $table - nome da tabela
     * @param array $data - valores a serem substituidos no momento da execução da query nos campos começados com : no where,ex: nome = :nome
     * @param array $fields - campos que serão retornados no select
     * @param array $where - condiçoes do select
     * @param array $joins - joins com outras tabelas
     * @param array $order - ordenação do select
     * @param array $limit - limitar a quantidade de registros a serem trazidos no resultado do select
     * @param array $allRows - informa se deverá trazer todas as linhas geradas pelo select
     * @return - um array com os resultados obtidos pelo select, ou false caso o select retorne vazio
     */
    public function select(...$params)
    {

        list($table,$fields,$data,$where,$joins,$order,$limit,$allRows) = $this->setSelectParams($params);
        
        if(isset($table) && !is_null($table) && !empty($table)){
            $arrQuery = [
                "joins" => (count($joins) > 0) ? implode(' ',array_map(function($item){return $item[0].$item[1];},$joins))." " : "",
                "where" => (count($where) > 0) ? "WHERE ".implode(' ',array_map(function($item){return $item[0].$item[1];},$where))." " : "",
                "order" => (strlen($order) > 0 ? "ORDER BY ".$order." " : ""),
                "limit" => ($limit > 0) ? "LIMIT ".$limit." " : ""
            ];
            
            $query = "SELECT "
                .$fields." "
                ."FROM "
                .$table." "
                .$arrQuery["joins"]
                .$arrQuery["where"]
                .$arrQuery["order"]
                .$arrQuery["limit"];

            return $query;
            
            $conn = $this->db->prepare($query);

            if($conn->execute($data)){
                $result = $conn->fetchAll(PDO::FETCH_ASSOC);
                if(count($result) > 0){
                    return ($allRows)
                    ? $result
                    : $result[0];                    
                }
            }
        }
        
        return false;
    }

    /**
     * Gera uma query de insert para execução no database
     * @param array $data - dados a serem inseridos no database
     * @param string $table - nome da tabela
     * @return boolean - retorna true se a query foi executada com sucesso, caso contrário retorna erro
     * @throws PDOException
     */
    public function insert($data,$table)
    {
        //array com os campos a serem salvos no banco (como eles são os campos que vão no Value, estão com os dois pontos antes)
        $arrValuesFields = array_keys($data);
        
        //array com os campos a serem salvos no banco (retirando os ":" de cada campo 
        $arrFields = array_map(function($item){
            return str_replace(":", "", $item);
        },$arrValuesFields);

        $query = " "
            . "INSERT INTO ".$table
            . " ("
            .implode(",",$arrFields)
            . ") "
            . "VALUES"
            . " ("
            .implode(",",$arrValuesFields)
            . ")";

        try {
            $rowsLengthBefore = $this->rowCount($table);
            
            if($this->db->inTransaction() === false){
                $this->db->beginTransaction();
            }
            
            $conn = $this->db->prepare($query);

            $conn->execute($data);

            if($conn->rowCount() > 0){    
                $this->db->commit();
                return true;
            }
            
            throw new PDOException();
        } catch (PDOException $pdoex) {
            $this->db->rollback();
            trigger_error($pdoex->getMessage(),E_USER_ERROR);
            return false;
        }
    }

    /**
     * Retorna o maior id da tabela
     * @param string $table - nome da tabela
     * @return int - retorna o maior id da tabela ou 0 caso a tabela esteja vazia
     */
    public function selectMaxId($table)
    {
        $query = "SELECT MAX(id) as id FROM ".$table;
        
        $conn = $this->db->prepare($query);
        
        if($conn->execute()){
            $result = (array) $conn->fetchObject();
            return $result['id'];
        }
        
        return 0;
    }
    
    public function update($table, $fields = [], $data = [], $where = [])
    {
        try{
            $arrAssertParams = [
                'table' => (isset($table) && !is_null($table) && !empty($table)) ? true : false,
                'data'  => (isset($data) && !is_null($data) && count($data) > 0) ? true : false,
                'fields' => (isset($fields) && !is_null($fields) && count($fields) > 0) ? true : false,
                'where' => (isset($where) && !is_null($where) && count($where) > 0) ? true : false,
            ];

            if($arrAssertParams['table'] === true && $arrAssertParams['data'] === true && $arrAssertParams['fields'] === true && $arrAssertParams['where'] === true){
                $arrQuery = [
                    "fields" => (count($fields) > 0) ? implode(',',array_map(function($item){return str_replace(":","",$item[0]).$item[1];},$fields))." " : "",
                    "where" => (count($where) > 0) ? "WHERE ".implode(' ',array_map(function($item){return $item[0].$item[1];},$where))." " : ""
                ];
            
                $query = "UPDATE ".$table
                ." SET"
                ." ".$arrQuery['fields']
                ." ".$arrQuery['where'];

                if($this->db->inTransaction() === false){
                    $this->db->beginTransaction();
                }

                $conn = $this->db->prepare($query);
                
                if($conn->execute($data)){
                    $this->db->commit();
                    return true;
                }

                throw new PDOException();
            }
        } catch (PDOException $pdoex) {
            $this->db->rollback();
            trigger_error($pdoex->getMessage(),E_USER_ERROR);
        }
        
    }

    private function setSelectParams($params){
        $paramsKeys = ['table','fields','data','where','joins','order','limit','allRows'];
        $paramsValues = $this->completeArrayValues(8,$params);
        $newParams = array_combine($paramsKeys,$paramsValues);
        $defaultValues = array_combine($paramsKeys,[null,'*',[],[],[],'',0,true]);
       
        $result = array_map(
            function($param) use ($defaultValues,$newParams){
                return ($newParams[$param] === null) ? $defaultValues[$param] : $newParams[$param];
            },
            array_keys($newParams)
        );

        return $result;
    }

    private function completeArrayValues($numberOfParams,$arr)
    {
        if (count($arr) < $numberOfParams) {
            $newArr = array_merge([],$arr);
            for($count = 0; $count < $numberOfParams - count($arr); $count++){
                array_push($newArr,null);
            }

            return $newArr;
        }

        return $arr;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: ahsuoy
 * Date: 5/9/2016
 * Time: 7:39 AM
 */

namespace acton;

use Unirest\Request;
use Unirest\Request\Body;
use DBE\DBE;

class initAccess
{

    private $actOnRequest;
    private $dbObj;
    private $listUrl = 'https://restapi.actonsoftware.com/api/1/list';
    private $listDownLoadUrl = 'https://restapi.actonsoftware.com/api/1/list/';
    private $msgListUrl = 'https://restapi.actonsoftware.com/api/1/message';
    private $recordUpdateUrl = 'https://restapi.actonsoftware.com/api/1/list/';
  
    private static $result = array();
    private static $counter = 0;

    private $listColumns = array(

        'listId'              => array('dataType'=>'INT', 'autoIncrement'=>true, 'null'=>0, 'dbString' => 'INT NOT NULL AUTO_INCREMENT, ', 'primaryKey'=>true),
        'id'                  => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'name'                => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'folderName'          => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'sourceId'            => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'baseId'              => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'description'         => array('dbString'=>'VARCHAR(500) NOT NULL, '),
        'sourceSize'          => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'sourceName'          => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'tsLastModified'      => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'tsLastCounted'       => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'tsSource'            => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'sourceTS'            => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSingleton'        => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsList'             => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSforce'           => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSforceReport '    => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSforceAvailable'  => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsMSDyn'            => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsMSDynAvailable'   => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSugar'            => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSugarAvailable'   => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSL'               => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsSLAvailable'      => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsNsuite'           => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'bIsNsuiteAvailable'  => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'updateTime'          => array('dbString'=>'VARCHAR(100) NOT NULL, ')

    );

    private $contactColumns = array(

        'contactId'              => array('dataType'=>'INT', 'autoIncrement'=>true, 'null'=>0, 'dbString' => 'INT NOT NULL AUTO_INCREMENT, ', 'primaryKey'=>true),
        'legacyId'               => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'listId'                 => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'updateTime'             => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'updateLevel'            => array('dbString'=>'VARCHAR(100) NOT NULL' )

    );

    private $contactMetaColumns = array(

        'metaId'              => array('dataType'=>'INT', 'autoIncrement'=>true, 'null'=>0, 'dbString' => 'INT NOT NULL AUTO_INCREMENT, ', 'primaryKey'=>true),
        'contactLegacyId'     => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'key'                 => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'value'               => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'updateTime'          => array('dbString'=>'VARCHAR(100) NOT NULL, '),
        'updateLevel'         => array('dbString'=>'VARCHAR(100) NOT NULL, ' )

    );

    private function isExist($where = '', $table = '') {

        $this->dbObj->simpleSelect('*', $table, $where);

        return ($this->dbObj->getNumRows() > 0) ? 1 : 0;

    }

    public function __construct()
    {

        $this->actOnRequest = new Request();
        $this->dbObj = new DBE();
        $this->dbObj->DBESetup(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $this->dbObj->connect();

    }

    public function actOnList($defaultHeaders = array(), $headers = array(), $params = array()){

        $this->actOnRequest->defaultHeaders($defaultHeaders);

        $response = $this->actOnRequest->get($this->listUrl, $headers, $params);

        $this->actOnRequest->clearDefaultHeaders();

        $response = json_decode(json_encode($response), true);

        return $response;

    }

    public function actOnMsgList($defaultHeaders = array(), $headers = array(), $params = array()) {

        $this->actOnRequest->defaultHeaders($defaultHeaders);

        $response = $this->actOnRequest->get($this->msgListUrl, $headers, $params);

        $this->actOnRequest->clearDefaultHeaders();

        $response = json_decode(json_encode($response), true);

        return $response;
    }

    public function actOnListDownLoadById($defaultHeaders = array(), $headers = array(), $params = array()){

        if($params['offset'] == 0) {

            self::$result = array();
            self::$counter = 0;
        }

        $this->actOnRequest->defaultHeaders($defaultHeaders);

        $response = $this->actOnRequest->get($this->listDownLoadUrl.$params['listid'], $headers, $params);

        $this->actOnRequest->clearDefaultHeaders();

        $response = json_decode(json_encode($response), true);

        self::$result[] = $response;

        if($response['body']['totalCount'] >  ($params['offset'] + 1000)) {

            $params['offset'] += 1000;

            return $this->actOnListDownLoadById($defaultHeaders, $headers, $params);

        }

        return self::$result;

    }


    public function actOnUpdateContactRecord($defaultHeaders = array(), $headers = array(), $listId = '', $recordId = '', $body = array()) {

        $this->actOnRequest->defaultHeaders($defaultHeaders);

        $newBody = new Body();
        $jsonBody = $newBody->Json($body);
        print_r($headers);
        $response = $this->actOnRequest->put($this->recordUpdateUrl.$listId.'/record/'.$recordId, $headers, $jsonBody);
        $this->actOnRequest->clearDefaultHeaders();
        return $response;
    }

    public function actOnPullContactRecord($defaultHeaders = array(), $headers = array(), $listId = -1, $recordId = -1, $params) {

        $this->actOnRequest->defaultHeaders($defaultHeaders);

        $response = $this->actOnRequest->get($this->listUrl.'/'.$listId.'/record/'.$recordId.'/',$headers, null);

        $response = json_decode(json_encode($response), true);

        $listHeaders = $this->actOnRequest->get($this->listDownLoadUrl.$params['listid'], $headers, $params);

        $listHeaders = json_decode(json_encode($listHeaders), true);

        $this->actOnRequest->clearDefaultHeaders();

        $return = array();

        foreach($listHeaders['body']['headers'] as $headerKey => $header) {

            $return[$header] = $response['body'][$headerKey];
        }


        return $return;
    }

    public function insertInto($params = array(), $table = '', $key = ''){

        $currentTable = $table;

        $where = $key . "='" . $params[$key] . "'";

        $check = $this->isExist($where, $table);

        if($check) {

            $this->dbObj->Update($currentTable, $params, $where);

        }else {

            $this->dbObj->Insert($currentTable, $params);

        }

    }

    public function insertIntoContactsMeta($params = array()) {



    }

    public function getTablePrimaryKey($tableName = '') {

        $params = array();
        if($tableName == 'listColumns') {

            $params = $this->getListColumns();

        }else if($tableName == 'contactColumns') {

            $params = $this->getContactColumns();

        }
        $str = "PRIMARY KEY ( ";
        foreach($params as $key => $val) {

            if(isset($val['primaryKey']) && $val['primaryKey']) {

                $str .= $key . " ));";

            }
        }

        return $str;
    }

    public function getListColumns()
    {
        return $this->listColumns;

    }

    public function getContactColumns() {

        return $this->contactColumns;

    }

    public function getContactMetaColumns()
    {

        return $this->contactMetaColumns;

    }

    public function createListsTable($table = '') {

        if(!$this->dbObj->is_table_exists($table)) {

            $this->dbObj->createTable($this->getListColumns(), $table, $this->getTablePrimaryKey('listColumns'));
        }

    }

    public function createContactsTable($table = '') {

        if(!$this->dbObj->is_table_exists($table)) {

            $this->dbObj->createTable($this->getContactColumns(), $table, $this->getTablePrimaryKey('contactColumns'));
        }
    }

    public function createContactMetaTable($table = '') {

        if(!$this->dbObj->is_table_exists($table)) {

            $this->dbObj->createTable($this->getContactMetaColumns(), $table, $this->getTablePrimaryKey('contactMetaColumns'));
        }
    }

    public function getValuesByKey($table = '', $key = '', $values = array()) {

        $results = array();

        foreach($values as $value) {

            $where = $key . "='" . $value . "'";
            $this->dbObj->simpleSelect('id', $table, $where);
            $numRows = $this->dbObj->getNumRows();

            if($numRows > 0) {

                while($id = $this->dbObj->fetchRow()) {

                    $results[$value][] = $id['id'];

                }

            }
        }

        return $results;
    }


}
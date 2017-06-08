<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0.0                                                |
  +----------------------------------------------------------------------+
  | Copyright (c) 2017 Issabel Foundation                                |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
*/
class IssabelF2BService {
    var $_DB;       // Reference to the active DB
    var $errMsg;    // Variable where the errors are stored

    /**
     * Constructor of the class, receives as a parameter the database, which is stored in the class variable $_DB
     *  .
     * @param string    $pDB     object of the class paloDB    
     */
    function IssabelF2BService(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /*HERE YOUR FUNCTIONS*/

    function creaTablaSiNoExiste() {
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name='jails'";
        $result = $this->_DB->fetchTable($query, true, array());
        if(count($result)==0) {
            // Debo crear la tabla
            $query = "CREATE TABLE jails (id integer primary key, name varchar(30) not null, maxretry integer, bantime integer, ignoreip text, enabled integer)";
            $result = $this->_DB->genQuery($query,array());

            if( $result == false ){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
            $jails = array('asterisk','sshd','postfix','apache','cyrus');
            foreach($jails as $jail) {
                $query = "INSERT INTO jails (name,maxretry,bantime,ignoreip,enabled) VALUES ('$jail','5','12','127.0.0.1',1)";
                $result = $this->_DB->genQuery($query,array());
            }
        }
    }

    /**
     * Function that returns the number of jails (data) in the database
     *  .
     * @return integer  0 in case of an error or the number of jails in the database
     */
    function obtainNumJails()
    {
        $query  = "SELECT COUNT(*) FROM jails";

        $result = $this->_DB->getFirstRowQuery($query,false,array());
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }

        return $result[0];
    }


     /**
     * Function that returns an array with all the jails available in the database
     *
     * @param integer    $limit             Value to limit the result of the query
     * @param integer    $offset            Value for the offset of the query
     *
     * @return array empty if an error occurs or the data with the jails
     */
    function obtainJails($limit=null, $offset=null)
    {
        $query   = "SELECT * FROM jails";

        $arrParm = array();
        if(isset($limit)) {
            $arrParm[] = $limit;
            $arrParm[] = $offset;
            $query .= " LIMIT ? OFFSET ? ";
        }

        $result = $this->_DB->fetchTable($query, true, $arrParm);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that updates the data of an existing jail
     *
     * @param string     $id                id of the jain to be updated
     * @param string     $maxretry          max retries for the jail
     * @param string     $bantime           ban time for the jail
     * @param string     $ignoreip         list of ignoreiped ip address
     * @param string     $enabled           enabled or not
     *
     * @return bool      false if an error occurs or true if the port is correctly updated
     */
    function updateJail($id, $maxretry, $bantime, $ignoreip, $enabled)
    {
        $query = "UPDATE jails SET maxretry=?, bantime=?, ignoreip=?, enabled=? ".
                 "WHERE id = ?";

        $arrParm = array($maxretry, $bantime, $ignoreip, $enabled, $id);   

        $result = $this->_DB->genQuery($query,$arrParm);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
    
        return true;
    }

    /**
     * Function that searches in the database an existing jail
     *
     * @param string     $id                id of the jail to be searched
     *
     * @return mixed     false if an error occurs or an array with all the data of the jail
     */
    function loadJail($id)
    {
        $query = "SELECT * FROM jails WHERE id = ?";
        $arrParm = array($id);
        $result = $this->_DB->fetchTable($query, true, $arrParm);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

        return $result[0];
    }

    function isActive() {
        exec('/usr/bin/elastix-helper fb_client reload', $output, $returncode);
        return $returncode;
    }

}
?>

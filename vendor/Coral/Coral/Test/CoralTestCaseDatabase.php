<?php
namespace Coral\Coral\Test;

use Zend\Db\Adapter\Adapter;

/**
 *
 */
class CoralTestCaseDatabase extends \PHPUnit_Extensions_Database_TestCase
{
    // DB関係　ここから
    protected $_conn = null;

    protected $_db = null;

    protected function setUp()
    {
        // DB接続
        $driver['driver']   = "Pdo_MySql";
        $driver['host']     = "127.0.0.1";
        $driver['database'] = "coraldb_test03";
        $driver['username'] = "root";
        $driver['password'] = "";
        $driver['charset']  = "utf8";
        $this->_db = new Adapter($driver);

        parent::setUp();
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Extensions_Database_TestCase::getConnection()
     */
    public function getConnection() {
        if (is_null($this->_conn)) {
            // 定数はphpunit.xmlの /phpunit/php/const 要素で定義できます(@nameが定数名、@valueが値)。
            $pdo = new \PDO("mysql:host=localhost; dbname=coraldb_test03; charset=utf8;", "root", "");
            $this->_conn = $this->createDefaultDBConnection($pdo);
        }
        return $this->_conn;
    }

}
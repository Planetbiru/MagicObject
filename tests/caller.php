<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";



$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml.txt", false, true, true);
$database = new PicoDatabase($databaseCredential->getDatabase());
$database->connect();

class Supervisor extends MagicObject
{
    /**
     * Caller method to find custom data based on parameters.
     *
     * This method serves as an interface to the native method in the parent class
     * which executes a database query based on the provided parameters.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function findCustom($supervisorId, $aktif)
    {
        // Memanggil metode parent untuk mengeksekusi query
        return parent::executeNativeQuery();
    }
}

$obj = new Supervisor(null, $database);
$results = $obj->findCustom(1, true); // Mengambil data berdasarkan kriteria yang diberikan

echo $results->nama;

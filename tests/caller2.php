<?php

use MagicObject\Database\PicoDatabase;

class ParentClass
{
    /**
     * Database connection instance.
     *
     * @var PicoDatabase
     */
    private $database;

    /**
     * Constructor to initialize database connection.
     *
     * @param PicoDatabase $database An instance of the PicoDatabase class for database operations.
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    /**
     * Executes a database query based on the parameters and annotations from the caller function.
     *
     * This method uses reflection to retrieve the query string from the caller's docblock,
     * binds the parameters, and executes the query against the database.
     *
     * @return array|null Returns an associative array of results on success or null on failure.
     */
    protected function executeNativeQuery()
    {
        // Mengambil informasi jejak pemanggil
        $trace = debug_backtrace();

        // Mengambil parameter dari fungsi pemanggil
        $callerParamValues = isset($trace[1]['args']) ? $trace[1]['args'] : [];
        
        // Mendapatkan nama fungsi dan kelas pemanggil
        $callerFunctionName = $trace[1]['function'];
        $callerClassName = $trace[1]['class'];

        // Menggunakan refleksi untuk mendapatkan anotasi dari fungsi pemanggil
        $reflection = new ReflectionMethod($callerClassName, $callerFunctionName);
        $docComment = $reflection->getDocComment();

        // Mengambil query dari anotasi @query
        preg_match('/@query\s*\("([^"]+)"\)/', $docComment, $matches);
        $queryString = $matches ? $matches[1] : '';

        // Mendapatkan informasi parameter dari fungsi pemanggil
        $callerParams = $reflection->getParameters();
        
        try {
            // Mendapatkan koneksi database
            $pdo = $this->database->getDatabaseConnection();
            $stmt = $pdo->prepare($queryString);

            // Melakukan binding otomatis untuk setiap parameter
            foreach ($callerParamValues as $index => $paramValue) {
                if (isset($callerParams[$index])) {
                    // Format nama parameter sesuai dengan yang ada di query
                    $paramName = ':' . $callerParams[$index]->getName();
                    $paramType = $this->mapToPdoParamType($paramValue);
                    $stmt->bindValue($paramName, $paramValue, $paramType);
                }
            }

            // Eksekusi query
            $stmt->execute();

            // Mengambil semua hasil sebagai array asosiatif
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Menangani kesalahan database dengan log
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Maps PHP types to PDO parameter types.
     *
     * @param mixed $value The value to determine the type for.
     * @return int The corresponding PDO parameter type.
     */
    private function mapToPdoParamType($value)
    {
        if (is_null($value)) {
            return PDO::PARAM_NULL;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_float($value)) {
            return PDO::PARAM_STR; // PDO does not have a specific PARAM_FLOAT
        } elseif (is_string($value)) {
            return PDO::PARAM_STR;
        } else {
            return PDO::PARAM_STR; // Default to string if type is unknown
        }
    }

}

class ChildClass extends ParentClass
{
    /**
     * Caller method to find custom data based on parameters.
     *
     * This method serves as an interface to the native method in the parent class
     * which executes a database query based on the provided parameters.
     *
     * @param int $tableId The ID of the table to search for.
     * @param string $customerName The name of the customer.
     * @param bool $active The active status to filter results.
     * @return array|null Returns an associative array of results on success or null on failure.
     * @query("
      SELECT table.* 
      FROM table 
      WHERE table_id.tableId = :tableId 
      AND table.name = :customerName 
      AND table.active = :active
     ")
     */
    public function findCustom($tableId, $customerName, $active)
    {
        // Memanggil metode parent untuk mengeksekusi query
        return parent::executeNativeQuery();
    }
}

// Contoh pemanggilan
$obj = new ChildClass(null, $database);
$results = $obj->findCustom(1, "budi", true); // Mengambil data berdasarkan kriteria yang diberikan
print_r($results); // Menampilkan hasil query

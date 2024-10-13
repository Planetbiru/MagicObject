<?php

class ParentClass
{
    protected function native()
    {
        $trace = debug_backtrace();

        // Mengambil parameter dari fungsi pemanggil
        if (isset($trace[1]['args'])) {
            $callerParams = $trace[1]['args'];
        }

        // Mendapatkan nama fungsi pemanggil
        $callerFunctionName = $trace[1]['function'];

        // Menggunakan refleksi untuk mendapatkan anotasi
        // Gunakan ReflectionMethod, bukan ReflectionFunction
        $reflection = new ReflectionMethod($trace[1]['class'], $callerFunctionName);
        $docComment = $reflection->getDocComment();

    }
}

class ChildClass extends ParentClass
{
    /**
     * Caller
     *
     * @param int $tableId
     * @param string $customerName
     * @param bool $active
     * @return void
     * @query(SELECT * FROM table WHERE table_id.tableId = :tableId AND table.name = :customerName AND table.active = :active)
     */
    public function caller($tableId, $customerName, $active)
    {
        return parent::native();
    }
}

$obj = new ChildClass();
$obj->caller(1, "budi", true);

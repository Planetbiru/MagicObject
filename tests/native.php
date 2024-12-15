<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseQueryTemplate;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @param PicoDatabaseQueryTemplate $template Query template.
     * @return PDOStatement The result of the executed query.
    */
    public function exportActive($aktif, $pageable, $sortable, $template)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

$explort = new SupervisorExport(null, $database);

$aktif = true;
$sortable = new PicoSortable();
$sortable->add(new PicoSort('name', PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(1, 1), $sortable);

$builder = new PicoDatabaseQueryBuilder($database);
$builder->newQuery()
    ->select("supervisor.*")
    ->from("supervisor")
    ->where("supervisor.aktif = :aktif");

$template = new PicoDatabaseQueryTemplate($builder);
$result = $explort->exportActive($aktif, $pageable, $sortable, $template);

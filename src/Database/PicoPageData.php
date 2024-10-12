<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\FindOptionException;
use MagicObject\MagicObject;
use PDO;
use PDOStatement;
use stdClass;

/**
 * Class representing paginated data.
 *
 * This class encapsulates the results of a database query along with pagination and execution details.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPageData
{
    const RESULT = 'result';
    const PAGEABLE = 'pageable';

    /**
     * Result data.
     *
     * @var MagicObject[]
     */
    private $result = array();

    /**
     * Pageable object.
     *
     * @var PicoPageable
     */
    private $pageable;

    /**
     * Total match count.
     *
     * @var int
     */
    private $totalResult = 0;

    /**
     * Total page count.
     *
     * @var int
     */
    private $totalPage = 0;

    /**
     * Page number.
     *
     * @var int
     */
    private $pageNumber = 1;

    /**
     * Page size.
     *
     * @var int
     */
    private $pageSize = 0;

    /**
     * Data offset for pagination.
     *
     * @var int
     */
    private $dataOffset = 0;

    /**
     * Start time of the query.
     *
     * @var float
     */
    private $startTime = 0.0;

    /**
     * End time of the query.
     *
     * @var float
     */
    private $endTime = 0.0;

    /**
     * Execution time of the query.
     *
     * @var float
     */
    private $executionTime = 0.0;

    /**
     * Pagination details.
     *
     * @var array
     */
    private $pagination = array();

    /**
     * PDO statement associated with the query.
     *
     * @var PDOStatement
     */
    private $stmt = null;

    /**
     * Class name of the entity.
     *
     * @var string
     */
    private $className;

    /**
     * Subquery mapping information.
     *
     * @var array
     */
    private $subqueryMap;

    /**
     * Flag indicating if count result is used.
     *
     * @var bool
     */
    private $byCountResult = false;

    /**
     * Entity associated with the results.
     *
     * @var MagicObject
     */
    private $entity;

    /**
     * Find option flags.
     *
     * @var int
     */
    private $findOption = 0;

    /**
     * Constructor for PicoPageData.
     *
     * Initializes a new instance of the PicoPageData class with the provided parameters.
     *
     * @param MagicObject[]|null $result Array of MagicObject or null.
     * @param float $startTime Timestamp when the query was sent.
     * @param int $totalResult Total result count.
     * @param PicoPageable|null $pageable Pageable object.
     * @param PDOStatement|null $stmt PDO statement.
     * @param MagicObject|null $entity Entity.
     * @param array|null $subqueryMap Subquery mapping.
     */
    public function __construct(
        $result = null,
        $startTime = null,
        $totalResult = 0,
        PicoPageable $pageable = null,
        PDOStatement $stmt = null,
        MagicObject $entity = null,
        $subqueryMap = null
    ) {
        if(isset($startTime))
        {
            $this->startTime = $startTime;
        }
        else
        {
            $this->startTime = time();
        }
        if ($result === null) {
            $this->result = [];
        } else {
            $this->result = $result;
        }

        if ($totalResult === 0) {
            $this->totalResult = $this->countData($this->result);
        } else {
            $this->totalResult = $totalResult;
        }

        $this->byCountResult = $totalResult === 0;

        if ($pageable instanceof PicoPageable) {
            $this->pageable = $pageable;
            $this->calculateContent();
        } else {
            $this->initializeDefaultPagination($this->totalResult);
        }

        $this->endTime = microtime(true);
        $this->executionTime = $this->endTime - $this->startTime;
        $this->stmt = $stmt;
        $this->entity = $entity;
        $this->className = ($entity !== null) ? get_class($entity) : null;
        $this->subqueryMap = ($subqueryMap !== null) ? $subqueryMap : [];
    }


    /**
     * Count the number of items in the result.
     *
     * @param array $result Result set.
     * @return int Count of items.
     */
    private function countData($result)
    {
        return is_array($result) ? count($result) : 0;
    }

    /**
     * Calculate pagination content based on pageable.
     *
     * @return self
     */
    public function calculateContent()
    {
        $this->pageNumber = $this->pageable->getPage()->getPageNumber();
        $this->pageSize = $this->pageable->getPage()->getPageSize();
        $this->totalPage = (int) ceil($this->totalResult / $this->pageSize);
        $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;
        $this->generatePagination(3);
        return $this;
    }

    /**
     * Initialize default pagination settings.
     *
     * @param int $countResult Count of results.
     */
    private function initializeDefaultPagination($countResult)
    {
        $this->pageNumber = 1;
        $this->totalPage = 1;
        $this->pageSize = $countResult;
        $this->dataOffset = 0;
    }

    /**
     * Generate pagination details.
     *
     * @param int $margin Number of pages to show before and after the current page.
     * @return self
     */
    public function generatePagination($margin = 3)
    {
        $margin = max(1, $margin);
        $curPage = $this->pageNumber;
        $totalPage = $this->totalPage;

        $minPage = max(1, $curPage - $margin);
        $maxPage = $this->byCountResult ? $totalPage : min($curPage + $margin, $totalPage);

        $this->pagination = array();
        for ($i = $minPage; $i <= $maxPage; $i++) {
            $this->pagination[] = ['page' => $i, 'selected' => $i === $curPage];
        }
        return $this;
    }

    /**
     * Get result data.
     *
     * @return MagicObject[]
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get page number.
     *
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Get total page count.
     *
     * @return int
     */
    public function getTotalPage()
    {
        return $this->totalPage;
    }

    /**
     * Get page size.
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Magic method to represent the object as a string.
     *
     * @return string
     */
    public function __toString()
    {
        $obj = new stdClass;
        $exposedProps = [
            "pageable",
            "totalResult",
            "totalPage",
            "pageNumber",
            "pageSize",
            "dataOffset",
            "startTime",
            "endTime",
            "executionTime",
            "pagination"
        ];
        
        foreach ($exposedProps as $key) {
            if (property_exists($this, $key)) {
                $obj->{$key} = $this->{$key};
            }
        }

        $obj->findOption = [
            "FIND_OPTION_NO_COUNT_DATA" => $this->findOption & MagicObject::FIND_OPTION_NO_COUNT_DATA,
            "FIND_OPTION_NO_FETCH_DATA" => $this->findOption & MagicObject::FIND_OPTION_NO_FETCH_DATA,
        ];

        return json_encode($obj);
    }

    /**
     * Get execution time of the query.
     *
     * @return float
     */
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * Get pagination details.
     *
     * @return array
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Get page control for pagination.
     *
     * @param string $parameterName Parameter name for the page.
     * @param string|null $path Link path.
     * @return PicoPageControl
     */
    public function getPageControl($parameterName = 'page', $path = null)
    {
        return new PicoPageControl($this, $parameterName, $path);
    }

    /**
     * Get total result count.
     *
     * @return int
     */
    public function getTotalResult()
    {
        return $this->totalResult;
    }

    /**
     * Get pageable object.
     *
     * @return PicoPageable|null
     */
    public function getPageable()
    {
        return $this->pageable;
    }

    /**
     * Get data offset.
     *
     * @return int
     */
    public function getDataOffset()
    {
        return $this->dataOffset;
    }

    /**
     * Get PDO statement.
     *
     * @return PDOStatement
     * @throws FindOptionException if statement is null.
     */
    public function getPDOStatement()
    {
        if ($this->stmt === null) {
            throw new FindOptionException("Statement is null. See MagicObject::FIND_OPTION_NO_FETCH_DATA option.");
        }
        return $this->stmt;
    }

    /**
     * Fetch the next row from the result set.
     *
     * @return MagicObject|mixed
     * @throws FindOptionException if statement is null.
     */
    public function fetch()
    {
        if ($this->stmt === null) {
            throw new FindOptionException("Statement is null. See MagicObject::FIND_OPTION_NO_FETCH_DATA option.");
        }
        
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
        return $result !== false ? $this->applySubqueryResult($result) : false;
    }

    /**
     * Apply subquery results to the row data.
     *
     * @param array $row Data row.
     * @return MagicObject
     */
    public function applySubqueryResult($row)
    {
        $data = $row;

        if (!empty($this->subqueryMap) && is_array($this->subqueryMap)) {
            foreach ($this->subqueryMap as $info) {
                $objectName = $info['objectName'];
                $objectNameSub = $info['objectName'];

                $data[$objectName] = isset($row[$objectNameSub])
                    ? (new MagicObject())->set($info['primaryKey'], $row[$info['columnName']])->set($info['propertyName'], $row[$objectNameSub])
                    : new MagicObject();
            }
        } else {
            $persist = new PicoDatabasePersistence($this->entity->currentDatabase(), $this->entity);
            $info = $this->entity->tableInfo();
            $data = $persist->fixDataType($row, $info);
            $data = $persist->join($data, $row, $info);
        }

        return new $this->className($data);
    }

    /**
     * Get find option flags.
     *
     * @return int
     */
    public function getFindOption()
    {
        return $this->findOption;
    }

    /**
     * Set find option flags.
     *
     * @param int $findOption Find option.
     * @return self
     */
    public function setFindOption($findOption)
    {
        $this->findOption = $findOption;
        return $this;
    }
}

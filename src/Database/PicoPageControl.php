<?php

namespace MagicObject\Database;
use MagicObject\Pagination\PicoPagination;

class PicoPageControl
{
    /**
     * Page data
     *
     * @var PicoPageData
     */
    private $pageData;

    /**
     * Parameter name
     *
     * @var string
     */
    private $parameterName;

    /**
     * Path
     *
     * @var string
     */
    private $path;

    /**
     * Prev
     *
     * @var string
     */
    private $prev;

    /**
     * Next
     *
     * @var string
     */
    private $next;

    /**
     * First
     *
     * @var string
     */
    private $first;

    /**
     * Last
     *
     * @var string
     */
    private $last;

    /**
     * Constructor
     *
     * @param PicoPageData $pagination
     * @param string $parameterName
     * @param string $path
     */
    public function __construct($pageData, $parameterName = 'page', $path = null)
    {
        $this->pageData = $pageData;
        if(isset($parameterName))
        {
            $this->parameterName = $parameterName;
        }
        if(isset($path))
        {
            $this->path = $path;
        }
    }

    /**
     * Set navigation
     *
     * @param string $prev
     * @param string $next
     * @param string $first
     * @param string $last
     * @return self
     */
    public function setNavigation($prev = null, $next = null, $first = null, $last = null)
    {
        $this->prev = $prev;
        $this->next = $next;
        $this->first = $first;
        $this->last = $last;
        return $this;
    }

    /**
     * To HTML
     *
     * @return string
     */
    public function toHTML()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        $lines = array();
        $format = '<span class="page-selector%s" data-page-number="%d"><a href="%s">%s</a></span>';
        $lastNavPg = 1;

        if(isset($this->first) && $this->pageData->getPageNumber() > 2)
        {
            // Old code $lines[] = '<span class="page-selector" data-page-number="1"><a href="'.PicoPagination::getPageUrl(1, $this->parameterName, $this->path).'">'.$this->first.'</a></span>';
            $lines[] = sprintf($format, '', 1, PicoPagination::getPageUrl(1, $this->parameterName, $this->path), $this->first);
        }

        if(isset($this->prev) && $this->pageData->getPageNumber() > 1)
        {
            $prevPg = $this->pageData->getPageNumber() - 1;
            // Old code $lines[] = '<span class="page-selector" data-page-number="$prevPg"><a href="'.PicoPagination::getPageUrl($prevPg, $this->parameterName, $this->path).'">'.$this->prev.'</a></span>';
            $lines[] = sprintf($format, '', $prevPg, PicoPagination::getPageUrl($prevPg, $this->parameterName, $this->path), $this->prev);
        }

        foreach($this->pageData->getPagination() as $pg)
        {
            $lastNavPg = $pg['page'];
            $selected = $pg['selected'] ? ' page-selected' : '';
            // old code $lines[] = '<span class="page-selector'.$selected.'" data-page-number="'.$pg['page'].'"><a href="'.PicoPagination::getPageUrl($pg['page'], $this->parameterName, $this->path).'">'.$pg['page'].'</a></span>';
            $lines[] = sprintf($format, $selected, $lastNavPg, PicoPagination::getPageUrl($lastNavPg, $this->parameterName, $this->path), $lastNavPg);
        }

        if(isset($this->next) && $this->pageData->getPageNumber() < ($this->pageData->getTotalPage() - 1))
        {
            $nextPg = $this->pageData->getPageNumber() + 1;
            // Old code $lines[] = '<span class="page-selector" data-page-number="$nextPg"><a href="'.PicoPagination::getPageUrl($nextPg, $this->parameterName, $this->path).'">'.$this->next.'</a></span>';
            $lines[] = sprintf($format, '', $nextPg, PicoPagination::getPageUrl($nextPg, $this->parameterName, $this->path), $this->next);
        }

        if(isset($this->last) && $lastNavPg < ($this->pageData->getTotalPage() - 2))
        {
            $lastPg = $this->pageData->getTotalPage();
            // Old code $lines[] = '<span class="page-selector" data-page-number="$lastPg"><a href="'.PicoPagination::getPageUrl($lastPg, $this->parameterName, $this->path).'">'.$this->last.'</a></span>';
            $lines[] = sprintf($format, '', $lastPg, PicoPagination::getPageUrl($lastPg, $this->parameterName, $this->path), $this->last);
        }

        return implode('', $lines);
    }
}
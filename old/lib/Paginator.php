<?php
require_once('function.php');

class Paginator
{
    protected $current_page;
    protected $items_count;
    protected $pager_number          = 5;
    protected $items_number_per_page = 10;

    public function __construct(int $items_count)
    {
        $this->setItemsNumber($items_count);
    }

    protected function setItemsNumber(int $items_count)
    {
        if ($items_count < 0) {
            throw new Exception('Argument must be greater than or equal to 0.');
        }

        $this->items_count = $items_count;
    }

    public function setCurrentPage(int $current_page)
    {
        if ($current_page > $this->getMaxPage()) {
            $current_page = $this->getMaxPage();
        } elseif($current_page < 1){
            $current_page = 1;
        }

        $this->current_page = $current_page;
    }

    public function getCurrentPage()
    {
        return $this->current_page;
    }

    public function getMaxPage()
    {
        $items_number_per_page = $this->items_number_per_page;

        if ($this->items_count > $items_number_per_page) {
            return (int) ceil($this->items_count / $items_number_per_page);
        }

        return 1;
    }

    public function setItemsNumberPerPage(int $items_number_per_page)
    {
        $this->items_number_per_page = $items_number_per_page;
    }

    public function getItemsNumberPerPage()
    {
        return $this->items_number_per_page;
    }

    public function getPageRange()
    {
        $pager_number = $this->pager_number;
        $max_page     = $this->getMaxPage();

        $middle = (int) ceil($pager_number / 2);
        if ($this->current_page <= $middle) {
            $start_page = 1;
            $end_page   = min($pager_number, $max_page);
        } else {
            $end_page   = min($this->current_page + $pager_number - $middle, $max_page);
            $start_page = $end_page - ($pager_number - 1);
        }

        return range($start_page, $end_page);
    }

    public function setPagerNumber(int $pager_number)
    {
        $this->pager_number = $pager_number;
    }

    public function getPagerNumber()
    {
        return $this->pager_number;
    }

    public function getOffset()
    {
        return ($this->current_page - 1) * $this->items_number_per_page;
    }

    public function getPaginatorItems(string $url)
    {
        $paginator_items = [];
        $pagers_number   = $this->getPageRange();
        $pages_number    = (int) ceil($this->items_count / $this->items_number_per_page);

        if ($this->current_page > 1) {
            $previous_page = $this->current_page - 1;

            $paginator_items[] = [
                'label'        => '<',
                'current_page' => $previous_page,
                'url'          => $this->getUrlPage($url, $previous_page),
            ];
        }

        foreach ($pagers_number as $pager_number) {
            $paginator_items[] = [
                'label'        => "{$pager_number}",
                'current_page' => $pager_number,
                'url'          => $this->getUrlPage($url, $pager_number),
            ];
        }

        if ($this->current_page < $pages_number) {
            $next_page = $this->current_page + 1;

            $paginator_items[] = [
                'label'        => '>',
                'current_page' => $next_page,
                'url'          => $this->getUrlPage($url, $next_page),
            ];
        }

        return $paginator_items;
    }

    public function getUrlPage(string $url,int $page)
    {
        return $url . (strpos($url, '?') !== false ? '&' : '?') . 'current_page=' . $page;
    }
}

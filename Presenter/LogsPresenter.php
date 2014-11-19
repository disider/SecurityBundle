<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\LogsPresenter as LogsPresenterInterface;

class LogsPresenter extends BasePresenter implements PaginatorPresenter, LogsPresenterInterface
{
    private $logs;
    private $total;

    public function getLogs()
    {
        return $this->logs;
    }

    public function setLogs(array $logs)
    {
        $this->logs = $logs;
    }

    public function setCount($count)
    {
        $this->total = $count;
    }

    public function count()
    {
        return $this->total;
    }

    public function getItems()
    {
        return $this->logs;
    }

    public function getTotalLogs()
    {
        return $this->total;
    }

    public function setTotalLogs($total)
    {
        $this->total = $total;
    }
}

<?php

namespace LoadAnalyser\Lib\Handlers;

/**
 * Class ExportHandler
 * @package LoadAnalyser\Lib\Handlers
 */
class ExportHandler
{
    /**
     * @var LoadAnalyserHandler
     */
    protected $loadAnalyser;
    protected $returnItem;
    protected $points;
    protected $config;

    /**
     * ExportHandler constructor.
     * @param LoadAnalyserHandler $loadAnalyser
     */
    public function __construct(LoadAnalyserHandler $loadAnalyser)
    {
        $this->loadAnalyser = $loadAnalyser;
    }

    /**
     *
     */
    protected function checkIfAllIsSet()
    {
        if( $this->returnItem )
            return;

        $this->config();
        $this->points();

        $this->returnItem = [];
        $this->returnItem['config'] = $this->config;
        $this->returnItem['points'] = $this->points;
    }

    /**
     *
     */
    protected function resetItems()
    {
        $this->points = null;
        $this->config = null;
        $this->returnItem = null;
    }

    /**
     * @return $this
     */
    public function points()
    {
        $this->points = $this->loadAnalyser->getPoints();
        $this->returnItem = $this->points;
        return $this;

    }

    /**
     * @return $this
     */
    public function config()
    {
        $this->config = $this->loadAnalyser->config;
        $this->returnItem = $this->config;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $this->checkIfAllIsSet();
        $return = $this->returnItem;
        $this->resetItems();
        return $return;
    }

    /**
     * @param $file
     * @return bool|int
     */
    public function toFile($file)
    {
        return file_put_contents($file, $this->toJson());
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        $return = [];
        $multiExport = false;

        // Set items
        $this->checkIfAllIsSet();

        // Check if it is one or many to export
        if($this->config and $this->points)
            $multiExport = true;

        // Config
        if($this->config)
        {
            if($multiExport)
                $return['config'] = $this->config->export();
            else
                $return = $this->config->export();
        }

        // Points
        if($this->points)
        {
            $points = [];
            foreach ($this->points as $point)
            {
                $points[] = $point->export();
            }

            if($multiExport)
                $return['points'] = $points;
            else
                $return = $points;
        }

        // Reset
        $this->resetItems();

        // Return
        return json_encode($return);
    }
}

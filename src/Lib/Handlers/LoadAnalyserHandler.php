<?php

namespace LoadAnalyser\Lib\Handlers;

use LoadAnalyser\Lib\Holders\QueryLogHolder;
use LoadAnalyser\Lib\Point;
use LoadAnalyser\Lib\Presenters\ConsolePresenter;
use LoadAnalyser\Lib\Presenters\Presenter;
use LoadAnalyser\Lib\Presenters\WebPresenter;

/**
 * Class LoadAnalyserHandler
 * @package LoadAnalyser\Lib\Handlers
 */
class LoadAnalyserHandler
{
    /**
     * Version
     */
    const VERSION = '2.5.0';

    /**
     * Store current point
     */
    protected $currentPoint;

    /**
     * Hold point stack
     */
    protected $pointStack = [];

    /**
     * Hold sub point stack
     */
    protected $multiPointStack = [];

    /**
     *  Hold presenter
     */
    protected $presenter;

    /**
     * Hold the query log items
     */
    public $queryLogStack = [];

    /**
     * Hold the config class
     * @var ConfigHandler $config
     */
    public $config;

    /**
     *
     */
    protected $messageToLabel = null;

    /**
     * LoadAnalyserHandler constructor.
     */
    public function __construct()
    {
        // Set config
        $this->config = new ConfigHandler();
    }

    /**
     * @throws \Exception
     */
    public function bootstrap()
    {
        $this->setConfigQueryLogState();

        // Set display
        $this->bootstrapDisplay();

        // Preload class point
        $this->preload();
    }

    /**
     * Set measuring point X
     *
     * @param string|null   $label
     * @param string|null   $isMultiplePoint
     * @return void
     */
    public function point($label = null, $isMultiplePoint = false)
    {
        // Check if point already exists
        if( ! $isMultiplePoint)
            $this->finishLastPoint();

        // Check sub point
        $this->checkIfPointLabelExists($label, $isMultiplePoint);

        // Set label
        if(is_null($label))
            $label = 'Task ' . (count($this->pointStack) - 1);

        // Create point
        $point = new Point($this->config, $label, $isMultiplePoint);

        // Create and add point to stack
        if($isMultiplePoint)
        {
            $this->multiPointStack[$label] = $point;
            $this->message('Start multiple point ' . $label);
        }
        else
            $this->currentPoint = $point;

        // Start point
        $point->start();
    }

    /**
     * Set message
     *
     * @param string|null   $message
     * @param boolean|null   $newLine
     * @return void
     */
    public function message($message, $newLine = true)
    {
        $point = $this->currentPoint;

        // Skip
        if( ! $point or ! $point->isActive())
            return;

        if($newLine)
            $point->addNewLineMessage($message);
        else
            $this->messageToLabel .= $message;
    }

    /**
     * Finish measuring point X
     *
     * @param string|null   $multiplePointLabel
     * @return void
     */
    public function finish($multiplePointLabel = null)
    {
        $this->finishLastPoint();

        if($multiplePointLabel)
        {
            if( ! isset($this->multiPointStack[$multiplePointLabel]))
            	throw new \InvalidArgumentException("Can't finish multiple point '" . $multiplePointLabel . "'.");

            $point = $this->multiPointStack[$multiplePointLabel];
            unset($this->multiPointStack[$multiplePointLabel]);

            if($point->isActive()) {
                // Finish point
                $point->finish();

                // Trigger presenter listener
                $this->presenter->finishPointTrigger($point);
            }

            //
            $this->pointStack[] = $point;

        }
    }

    /**
     * Return test results
     *
     * @return LoadAnalyser\Lib\Handlers\ExportHandler
     */
    public function results()
    {
        // Finish all
        $this->finishLastPoint();

        // Finish all multiple points
        $this->finishAllMultiplePoints();

        // Add results to presenter
        $this->presenter->displayResultsTrigger($this->pointStack);

        // Return export
        return $this->export();
    }

    /**
     * @return ExportHandler
     */
    public function export()
    {
        return new ExportHandler($this);
    }

    /**
     * @return array
     */
    public function getPoints()
    {
        return $this->pointStack;
    }

//
// PRIVATE
//

    /**
     * @throws \Exception
     */
    protected function bootstrapDisplay()
    {
        if($this->config->getPresenter() == Presenter::PRESENTER_CONSOLE)
            $this->presenter = new ConsolePresenter($this->config);
        elseif($this->config->getPresenter() == Presenter::PRESENTER_WEB)
            $this->presenter = new WebPresenter($this->config);
        else
        	throw new \Exception("Unknown presenter '" . $this->config->getPresenter() ."'");
    }

    /**
     * Finish all point in the stack
     *
     * @return void
     */
    protected function finishLastPoint()
    {
        // Measurements are more accurate
        $stopTime = microtime(true);

        if($this->currentPoint)
        {
            // Get point
            $point = $this->currentPoint;

            if($point->isActive())
            {
                // Set query log items
                $this->setQueryLogItemsToPoint($point);

                // Check if message in label text
                $this->checkAndSetMessageInToLabel($point);

                // Finish point
                $point->setStopTime($stopTime);
                $point->finish();

                $this->pointStack[] = $point;

                // Trigger presenter listener
                $this->presenter->finishPointTrigger($point);
            }
        }
    }

    /**
     *
     */
    protected function finishAllMultiplePoints()
    {
        // Measurements are more accurate
        $stopTime = microtime(true);

        if(count($this->multiPointStack))
        {
            foreach ($this->multiPointStack as $point)
            {
                $point->setStopTime($stopTime);
                $point->finish();
                $this->pointStack[] = $point;

                // Trigger presenter listener
                $this->presenter->finishPointTrigger($point);
            }
        }
    }

    /**
     * Check if label already exists
     */
    protected function checkIfPointLabelExists($label, $isMultiPoint)
    {
        $labelExists = false;
        $stack = ($isMultiPoint) ? $this->multiPointStack : $this->pointStack;
        foreach ($stack as $point)
        {
            if($point->getLabel() == $label)
            {
                $labelExists = true;
                break;
            }
        }

        if($labelExists)
        	throw new \InvalidArgumentException("label '" . $label . "' already exists, choose new point label.");
    }

    /**
     * Preload wil setup te point class
     */
    protected function preload()
    {
        $this->point( Point::POINT_PRELOAD );
        $this->point( Point::POINT_MULTIPLE_PRELOAD, true );
        $this->finish(POINT::POINT_MULTIPLE_PRELOAD); // Needs!
        $this->point( Point::POINT_CALIBRATE );
    }

    /**
     * Check if query log is possible
     */
    protected function setConfigQueryLogState()
    {
        // Check if state is set
        if( ! is_null($this->config->queryLogState))
            return;

        // Set check query log state
        if($this->config->isQueryLog())
        {
            $this->config->queryLogState = false;

            // Check if DB class exists
            if( ! class_exists('\Illuminate\Support\Facades\DB'))
                return;

            // Resister listener
            try
            {
                \Illuminate\Support\Facades\DB::listen(function ($sql) {$this->queryLogStack[] = new QueryLogHolder($sql);});
                $this->config->queryLogState = true;
            }
            catch (\RuntimeException $e)
            {
                try
                {
                    \Illuminate\Database\Capsule\Manager::listen(function ($sql) {$this->queryLogStack[] = new QueryLogHolder($sql);});
                    $this->config->queryLogState = true;

                }
                catch (\RuntimeException $e)
                {
                    $this->config->queryLogState = false;
                }
            }
        }
    }

    /**
     * Move query log items to point
     */
    protected function setQueryLogItemsToPoint(Point $point)
    {
        // Skip if query log is disabled
        if($this->config->queryLogState !== true)
            return;

        $point->setQueryLog($this->queryLogStack);
        $this->queryLogStack = [];
    }

    /**
     * Update point label with message
     */
    protected function checkAndSetMessageInToLabel(Point $point)
    {
        if( ! $this->messageToLabel)
            return;

        // Update label
        $point->setLabel( $point->getLabel() . " - " . $this->messageToLabel);

        // Reset
        $this->messageToLabel = '';
    }
}

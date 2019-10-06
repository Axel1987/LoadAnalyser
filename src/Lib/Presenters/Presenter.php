<?php

namespace LoadAnalyser\Lib\Presenters;

use LoadAnalyser\Lib\Handlers\ConfigHandler;
use LoadAnalyser\Lib\Holders\InformationHolder;
use LoadAnalyser\Lib\Point;

/**
 * Class Presenter
 * @package LoadAnalyser\Lib\Presenters
 */
abstract class Presenter {

    // Set print format
    const PRESENTER_CONSOLE = 1;
    const PRESENTER_WEB = 2;

    // Config
    protected $config;
    protected $formatter;
    protected $calculate;
    protected $pointStack;
    protected $information;

    public function __construct(ConfigHandler $config)
    {
        $this->config = $config;
        $this->formatter = new Formatter($config);
        $this->calculate = new Calculate();
        $this->information = new InformationHolder($config);

        // Choose display format
        $this->bootstrap();
    }

    /**
     * Bootstrap sub class
     */
    abstract public function bootstrap();

    /**
     * Passed trigger to results to display
     */
    abstract public function displayResultsTrigger($pointStack);

    /**
     * Passed trigger finish point to display
     */
    abstract public function finishPointTrigger(Point $point);
}

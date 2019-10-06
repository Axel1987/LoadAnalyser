<?php

namespace LoadAnalyser;

use LoadAnalyser\Lib\Handlers\LoadAnalyserHandler;

/**
 * Class LoadAnalyser
 * @package LoadAnalyser
 */
class LoadAnalyser
{
    /**
     * Create a LoadAnalyser instance
     */
    protected static $LoadAnalyser;
    protected static $bootstrap = false;

    public static function instance()
    {
        if( ! static::$LoadAnalyser)
            static::$LoadAnalyser = new LoadAnalyserHandler();
        return static::$LoadAnalyser;
    }

    /**
     * @return bool
     */
    protected static function enableTool()
    {
        $LoadAnalyser = static::instance();

        // Check DISABLE_TOOL
        if( ! $LoadAnalyser->config->isEnableTool())
            return false;

        // Check bootstrap
        if( ! static::$bootstrap)
        {
            $LoadAnalyser->bootstrap();
            static::$bootstrap = true;
        }

        return true;
    }

    /**
     * Set measuring point X
     *
     * @param string|null   $label
     * @param string|null   $isMultiplePoint
     * @return void
     */
    public static function point($label = null, $isMultiplePoint = false)
    {
        if( ! static::enableTool() )
            return;

        // Run
        static::$LoadAnalyser->point($label, $isMultiplePoint);
    }

    /**
     * Set a message associated with the point
     *
     * @param string|null   $message
     * @param boolean|null  $newLine
     * @return void
     */
    public static function message($message = null, $newLine = true)
    {
        if( ! static::enableTool() or ! $message)
            return;

        // Run
        static::$LoadAnalyser->message($message, $newLine);
    }


    /**
     * Finish measuring point X
     *
     * @param string|null   $multiplePointLabel
     * @return void
     */
    public static function finish($multiplePointLabel = null)
    {
        if( ! static::enableTool() )
            return;

        // Run
        static::$LoadAnalyser->finish($multiplePointLabel);
    }

    /**
     * Export helper
     *
     * @return LoadAnalyser\Lib\Handlers\ExportHandler
     */
    public static function export()
    {
        if( ! static::enableTool() )
            return;

        // Run
        return static::$LoadAnalyser->export();
    }

    /**
     * Return test results
     *
     * @return LoadAnalyser\Lib\Handlers\ExportHandler
     */
    public static function results()
    {
        if( ! static::enableTool() )
            return;

        // Run
        return static::$LoadAnalyser->results();
    }

    /**
     * Reset
     */
    public static function instanceReset()
    {
        // Run
        Config::instanceReset();
        static::$LoadAnalyser = null;
        static::$bootstrap = false;
    }
}

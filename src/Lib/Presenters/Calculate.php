<?php

namespace LoadAnalyser\Lib\Presenters;

use LoadAnalyser\Lib\Holders\CalculateTotalHolder;

/**
 * Class Calculate
 * @package LoadAnalyser\Lib\Presenters
 */
class Calculate
{
    /**
     * Calculate total memory
     *
     * return LoadAnalyser\Lib\Holders\CalculateTotalHolder;
     */
    public function totalTimeAndMemory($pointStack)
    {
        $max_time = 0;
        $max_memory = 0;

        foreach (array_slice($pointStack, 2) as $point)
        {
            $max_time += $point->getDifferenceTime();
            $max_memory += $point->getDifferenceMemory();
        }

        return new CalculateTotalHolder($max_time, $max_memory, memory_get_peak_usage(true));
    }

    /**
     * Calculate percentage
     */
    public function calculatePercentage($pointDifference, $total)
    {
        $upCount = 1000000;

        if($pointDifference > 0 and $total > 0)
            return round((100 * $pointDifference * $upCount ) / ($total * $upCount)) ;
        else
            return '0';
    }
}

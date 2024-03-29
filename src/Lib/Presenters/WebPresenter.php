<?php

namespace LoadAnalyser\Lib\Presenters;

use LoadAnalyser\Lib\Handlers\LoadAnalyserHandler;
use LoadAnalyser\Lib\Point;

/**
 * Class WebPresenter
 * @package LoadAnalyser\Lib\Presenters
 */
class WebPresenter extends Presenter
{
    public function bootstrap(){}

    public function finishPointTrigger(Point $point){}

    public function displayResultsTrigger($pointStack)
    {
        $this->pointStack = $pointStack;

        $this->displayForWebAsHtml();
        $this->displayForWebAsConsole();
    }

    // Private
    protected function displayForWebAsConsole()
    {
        $data = [];
        $data['config'] = $this->config->export();
        foreach ($this->pointStack as $point)
        {
            $data[] = [
                'label' => $point->getLabel(),
                'time' => $this->formatter->timeToHuman( $point->getDifferenceTime() ),
                'memory_usage' => $this->formatter->memoryToHuman( $point->getDifferenceMemory() ),
                'memory_peak' => $this->formatter->memoryToHuman( $point->getStopMemoryUsage() ),
                'raw' => $point->export()
            ];
        }

        echo "<script>console.log(" . json_encode($data) .")</script>";
    }

    protected function displayForWebAsHtml()
    {
        // Set total time
        echo '<style>';
        include_once 'DisplayHtml.css';
        echo '</style>';

        echo '<div id="LoadAnalyser-tool">';
            echo '<a id="LoadAnalyser-btn-close" href="#" onclick="LoadAnalyserDisplayToggle(null)">&#9660;</a>';
            echo '<div id="hiddenContent">';
                if(count($this->pointStack) > 2) {
                    $textExecutionTime = (ini_get('max_execution_time') > 1) ? ini_get('max_execution_time') . ' sec' : 'unlimited';
                    $calculateTotalHolder = $this->calculate->totalTimeAndMemory($this->pointStack);

                    echo'<table class="table-title">
                        <tr>
                            <td width="50%">' . $this->formatter->memoryToHuman($calculateTotalHolder->totalMemory) . '<br><span>Max memory ' . ini_get("memory_limit") . '</span></td>
                            <td width="50%">' . $this->formatter->timeToHuman($calculateTotalHolder->totalTime) . '<br><span>Max time ' . $textExecutionTime . ' on PHP ' . phpversion() . '</span></td>
                        </tr>
                    </table>
                    
                    <div class="overflow">
                    
                        <table class="table-point">
                        <tr>
                            <th>Label</th>
                            <th width="5%">%</th>
                            <th width="17%">Memory</th>
                            <th width="5%">%</th>
                            <th width="17%">Time</th>
                        </tr>';

                        foreach (array_slice($this->pointStack, 1) as $point)
                        {

                            // For real calibrate results fake printing
                            if( $point->getLabel() === Point::POINT_PRELOAD or $point->getLabel() === Point::POINT_MULTIPLE_PRELOAD)
                                continue;

                            echo '<tr>'
                                . '<td class="t-l">' . $point->getLabel() . '</td>'
                                . '<td>' . $this->calculate->calculatePercentage($point->getDifferenceMemory(), $calculateTotalHolder->totalMemory) . '</td>'
                                . '<td>' . $this->formatter->memoryToHuman($point->getDifferenceMemory()) . '</td>'
                                . '<td>' . $this->calculate->calculatePercentage($point->getDifferenceTime(), $calculateTotalHolder->totalTime) . '</td>'
                                . '<td>' . $this->formatter->timeToHuman($point->getDifferenceTime()) . '</td>'
                                . '</tr>';

	                        foreach($this->formatter->createPointQueryLogLineList($point) as $queryLineHolder){
		                        echo '<tr>';
		                        echo '<td class="new-line-message" colspan="3"> - ' . $this->formatter->formatStringWidth($queryLineHolder->getLine(), 70) . '</td>';
		                        echo '<td class="new-line-message" style="text-align: right;" colspan="2">' . $queryLineHolder->getTime() . ' ms</td>';
		                        echo '</tr>';
	                        }
                            foreach ($point->getNewLineMessage() as $message)
                            {
                                echo '<tr><td class="new-line-message" colspan="5">' . $message . '</td></tr>';
                            }
                        }
                        echo '</table>';

                        $calibratePoint = $this->pointStack[1];

                        echo '<div class="table-more-info">LoadAnalyser v' . LoadAnalyserHandler::VERSION
                            . ' PHP v' . phpversion() . ' on ' . date('Y-m-d H:i:s')
                            . '<br>Calibrate point: ' . $this->formatter->timeToHuman($calibratePoint->getDifferenceTime());

                        if($this->config->isRunInformation())
                            echo "<br> Run by user " . $this->information->getCurrentUser() . " on process id " . $this->information->getCurrentProcessId();

                        echo '</div>';
                    echo '</div>';
                }
                else
                    echo '<div class="table-more-info"> <h2>There are not point set</h2>Set your first point with <var>point</var> command:<br><code>LoadAnalyser::point()</code>Final you can view the results with the <var>results</var> command:<br><code>LoadAnalyser::results()</code></div>';
            echo '</div>';

        echo '<script>';
        include_once 'DisplayScript.js';
        echo '</script>';

        echo '</div>';
    }
}

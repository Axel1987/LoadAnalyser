<?php

namespace LoadAnalyser\Lib\Presenters;

use LoadAnalyser\Lib\Handlers\ConfigHandler;
use LoadAnalyser\Lib\Holders\QueryLineHolder;
use LoadAnalyser\Lib\Point;

/**
 * Class Formatter
 * @package LoadAnalyser\Lib\Presenters
 */
class Formatter {

	protected $config;
    public function __construct(ConfigHandler $config){
    	$this->config = $config;
    }

    public function timeToHuman($microTime, $unit = 'auto', $decimals = 2)
    {
        if($unit == "auto")
        {
            if ($microTime > 1)
                $unit = 's';
            elseif($microTime > 0.001)
                $unit = 'ms';
            else
                $unit = 'μs';
        }

        switch ($unit)
        {
            case 'μs':
                return round($microTime * 1000000, $decimals) . ' ' . $unit;
                break;
            case 'ms':
                return round($microTime * 1000, $decimals) . ' ' . $unit;
                break;
            case 's':
                return round($microTime * 1, $decimals) . '  ' . $unit;
                break;
            default:
                new ErrorMessage($this, 'LoadAnalyser format ' . $unit . ' not exist');
        }
    }

    // Creatis to cam-gists/memoryuse.php !!
    public function memoryToHuman($bytes, $unit = "", $decimals = 2)
    {
        if($bytes <= 0)
            return '0.00 KB';

        $units = [
            'B' => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8
        ];

        $value = 0;
        if ($bytes > 0)
        {
            // Generate automatic prefix by bytes
            // If wrong prefix given
            if ( ! array_key_exists($unit, $units))
            {
                $pow = floor(log($bytes) / log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes / pow(1024, floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0
        if ( ! is_numeric($decimals) || $decimals < 0)
            $decimals = 2;

        // Format output
        return sprintf('%.' . $decimals . 'f ' . $unit, $value);
    }

    /**
     * Fix problem 'μs'
     */
    public function stringPad($input, $pad_length, $pad_string = ' ')
    {
        $count = strlen($input);

        // Fix μ issues
        if(strpos($input, 'μ'))
            $count--;

        $space = $pad_length - $count;

        return str_repeat($pad_string, $space) . $input;
    }

	public function createPointQueryLogLineList(Point $point)
	{
		$lineArray = [];
		if(!$point->getQueryLog())
		{
			return $lineArray;
		}

		if($this->config->getQueryLogView() == 'resume')
		{
			$buildLineList = [];
			foreach ($point->getQueryLog() as $queryLogHolder) {
				$type = $queryLogHolder->queryType;
				if (isset($buildLineList[$type]))
				{
					$buildLineList[$type]['count']++;
					$buildLineList[$type]['time'] = $buildLineList[$type]['time'] + $queryLogHolder->time;
				}
				else
				{
					$buildLineList[$type]['count'] = 1;
					$buildLineList[$type]['time'] = $queryLogHolder->time;
				}
			}
			ksort($buildLineList);
			foreach ($buildLineList as $key => $item) {
				$queryLineHolder = new QueryLineHolder();
				$queryLineHolder->setLine('Database query ' . $key . ' ' . $item['count'] . 'x');
				$queryLineHolder->setTime($item['time']);
				$lineArray[] = $queryLineHolder;
			}
		}

		// View type full
		if($this->config->getQueryLogView() == 'full')
		{
			foreach ($point->getQueryLog() as $queryLogHolder) {
				$queryLineHolder = new QueryLineHolder();
				$queryLineHolder->setLine($queryLogHolder->query);
				$queryLineHolder->setTime($queryLogHolder->time);
				$lineArray[] = $queryLineHolder;
			}
		}

		return $lineArray;
	}

	public function formatStringWidth($string, $width)
	{
		return ((strlen($string) > $width) ? substr($string,0, $width - 3).'...' : $string);
	}
}

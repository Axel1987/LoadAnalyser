<?php

namespace LoadAnalyser\Lib\Holders;

use LoadAnalyser\Lib\Handlers\ConfigHandler;

/**
 * Class InformationHolder
 * @package LoadAnalyser\Lib\Holders
 */
class InformationHolder
{
    // Config
    protected $config;

    // Run information holder
    protected $currentUser;
    protected $currentProcessId;

    public function __construct(ConfigHandler $config)
    {
        $this->config = $config;

        // Set information
        $this->activateConfig();
    }

    /**
     * @return mixed
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * @return mixed
     */
    public function getCurrentProcessId()
    {
        return $this->currentProcessId;
    }

//
// Private
//

    protected function activateConfig()
    {
        if($this->config->isRunInformation())
            $this->setRunInformation();
    }

    protected function setRunInformation()
    {
        // Set unknown
        $this->currentUser = '?';
        $this->currentProcessId = '?';

        // Set current user
        try{
            $this->currentUser = get_current_user();
        }catch (\ErrorException $exception) {}

        // Set current user
        try{
            $this->currentProcessId = getmypid();
        }catch (\ErrorException $exception) {}
    }
}

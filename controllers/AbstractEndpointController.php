<?php

abstract class AbstractEndpointController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->processGetRequest();
                break;
            case 'POST':
                $this->processPostRequest();
                break;
            default:
                
        }
    }

    abstract protected function processGetRequest();
    abstract protected function processPostRequest();
}

?>
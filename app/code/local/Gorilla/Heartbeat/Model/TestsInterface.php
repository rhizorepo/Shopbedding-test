<?php
interface Gorilla_Heartbeat_Model_TestsInterface
{
    /**
     * Main test process
     * @return void
     */
    public function process();
    
    /**
     * Must return result of the test
     * @return mixed
     */
    public function getResult();

    /**
     * Must return recommendations how to fix issues
     * @return string
     */
    public function getRecommendations();
}
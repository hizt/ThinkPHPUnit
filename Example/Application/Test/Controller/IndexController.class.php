<?php
namespace Test\Controller;
use Think\UnitTest;

class IndexController extends UnitTest {
    function index(){
        $this->setController( array(__CLASS__) );
        $this->run();
    }

    function testHome_IndexController_getArray(){
        $indexController = new \Home\Controller\IndexController();
        $this->assertNotEmpty( $indexController->getUnEmptyArray() );
        $this->assertEmpty( $indexController->getEmptyArray() );
    }
}
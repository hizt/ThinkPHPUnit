<?php
namespace Test\Controller;

use T\UnitTest;

class IndexController extends UnitTest {
    function index(){
        //通过自动遍历测试类的方式执行测试
        $this->run(true);
    }

    function index2(){
        //通过设置测试类的方式执行测试
        $this->setController( array(__CLASS__) );
        $this->run();
    }

    function testAssert(){
        //设置一些测试变量
        $empty = 0;
        $notEmpty = 1;
        $emptyArray = array();
        $array = array(1 , 'a'=>'a' );
        $indexController = new IndexController();
        $jsonString = '{"obj":"object"}';
        $regex = '/^\{.+\}$/';

        //测试断言方法
        $this->assertArrayHasKey( 'a' , $array , 'Some Message!');
        $this->assertNotArrayHasKey('b' ,  $array , 'Some Message!');

        $this->assertCount(2 , $array);
        $this->assertNotCount(3 , $array);

        $this->assertEmpty($empty);
        $this->assertEmpty($emptyArray);
        $this->assertNotEmpty($array);
        $this->assertNotEmpty($notEmpty);

        $this->assertEquals($empty , false);
        $this->assertNotEquals($emptyArray , $array);

        $this->assertSame( $empty , $empty);
        $this->assertNotSame($empty , '0');

        $this->assertTrue(true);
        $this->assertFalse(false);

        $this->assertFileExists(__FILE__);
        $this->assertNotFileExists(__FILE__ . 'XXX');

        $this->assertGreater( 0 , $notEmpty);
        $this->assertGreaterOrEquals( 0 , $empty);
        $this->assertLess(  1 , $empty);
        $this->assertLessOrEquals( 1 , $notEmpty );

        $this->assertInstanceOf(__CLASS__ , $indexController);
        $this->assertNotInstanceOf(__CLASS__.'XXX' , $indexController);

        $this->assertJson($jsonString );
        $this->assertNotJson($jsonString."XXX");

        $this->assertRegex( $regex , $jsonString);
        $this->assertNotRegex($regex , $jsonString."XXX" );

    }

    function testHome_IndexController(){
        $indexController = new \Home\Controller\IndexController();
        $this->assertTrue( $indexController->getTrue() );
        $this->assertFalse( $indexController->getFalse() );
        $this->assertNotEmpty( $indexController->getUnEmptyArray() );
        $this->assertEmpty( $indexController->getEmptyArray() );
    }
}
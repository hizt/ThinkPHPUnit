<?php
namespace Test\Controller;
use T\UnitTest;

class OtherController extends UnitTest
{
    function testSum(){
        $testArray = array(
            array('key'=>'1'),
            array('key'=>'2'),
            array('key'=>'3')
        );
        $controller = new \Home\Controller\OtherController();
        $this->assertSame( 6 , $controller->sum( $testArray , 'key' ) );
        $this->assertSame( 0 , $controller->sum( array() , 'key' ) );
    }

    function testF(){
        $key = 'test';
        $val = 'val';

        $this->assertFalse( F($key) );       //读取$key
        $this->assertNull( F($key , $val) );//设置$key
        $this->assertSame( $val , F($key) );       //读取$key
        $this->assertTrue( F($key , null) );//删除$key
        $this->assertFalse( F($key) );       //读取$key
    }
}

# ThinkPHPUnit
一个为ThinkPHP打造的简单易用的UnitTest  <br />
<br />


##如何使用
* 复制 `UnitTest.class.php` 至ThinkPHP路径  `/ThinkPHP/Library/Think/` 目录下。
* 在`Application`目录下创建`Test/Controller`，编写测试Controller。
* 以编写 IndexController为例:
```PHP
namespace \Test\Controller;
class IndexController extends \Think\Controller{
    function index(){
        $this->setController( array(__CLASS__) );  //设置将要执行的测试类
        $this->run();                              //执行测试代码
    }

    function testExample1(){                    //该方法将自动被测试
        $this->assertTrue(true);
        $this->assertFalse(false);
    }

    function testExample2(){                    //该方法将自动被测试
            $this->assertEmpty(null);
            $this->assertNotEmpty(true);
    }
}
```
* 到浏览器运行 `http://localhost/PROJECT/index.php?m=Test&a=index&a=index
* 运行结果
![IMG](https://raw.githubusercontent.com/hizt/ThinkPHPUnit/master/result.png)
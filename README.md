# ThinkPHPUnit v0.9
一个为ThinkPHP打造的的UnitTest，简单易用、高效便捷。  <br />

<br />


##How To Use
* 复制 `UnitTest.class.php` 至ThinkPHP路径  `/ThinkPHP/Library/Think/` 目录下。
* 在`Application`目录下创建`Test/Controller`，编写测试Controller。
* 以编写 IndexController为例:
```PHP
namespace \Test\Controller;
class IndexController extends \Think\Controller{
    function index(){
        $this->setController( array(__CLASS__ ,'\OtherClassName') );  //设置将要执行的测试类
        $this->run();                               //执行测试代码
        $this->outputAsHtml();                      //以html形式展现结果
        //$this->outputAsCsv();                     //也可以csv文件的形式下载结果
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
* 到浏览器运行 `http://localhost/PROJECT_PATH/index.php?m=Test`
* 运行结果
![IMG](https://raw.githubusercontent.com/hizt/ThinkPHPUnit/master/result-screenshot.png)


<br />


##Bug && Contact Author
* bug提交：[https://github.com/hizt/ThinkPHPUnit/issues](https://github.com/hizt/ThinkPHPUnit/issues) 
* 作者：[zthi@qq.com](mailto:zthi@qq.com)
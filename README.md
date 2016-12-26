# ThinkPHPUnit v0.95
一个为ThinkPHP打造的的UnitTest，简单易用、高效便捷。  <br />

<br />


##How To Use
### 详细使用介绍查看项目`example`,下面列出一个简单的使用介绍
* 在`Application`目录下创建`Test/Controller`，编写测试Controller。
* 以编写 IndexController为例:
```PHP
namespace \Test\Controller;
class IndexController extends UnitTest{
    
    /**
     *   调用方法：
     *   1. http://localhost/PROJECT_NAME/index.php?m=Test  自动执行全部测试文件
     *   2. http://localhost/PROJECT_NAME/index.php?m=Test&controller=XXX  自动执行参数crontroller指定的文件
     */
    function index(){
        $this->run(true); //测试方式1 ： 通过自动遍历测试文件的方式执行测试
        
    }

    function index2(){
        $this->setController( array(__CLASS__ ,'\OtherClassName') );  //测试方式2 ：设置将要执行的测试类
        $this->run();                               //执行测试代码

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
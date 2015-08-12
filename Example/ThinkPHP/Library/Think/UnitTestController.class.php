<?php
/**
 * 所有测试的基础类
 */
namespace Test\Controller;
use Think\Controller;
class TestBaseController extends Controller {
    private $testControllers = null; //待测试的类名数组
    protected function setController(array $controllers){
        $controllers = array_unique( $controllers );
        $controllers = array_map( function($temp){  //确认类名的正确性
            return '\\' . trim($temp , '\\');
        } , $controllers );
        $this->testControllers = $controllers;
    }

    /**
     * 开始执行测试，遍历所有 testConrollers，找到所有测试方法，执行测试。
     */
    protected function run(){
        foreach($this->testControllers as $controllerName){
            $controllerObj = new $controllerName();
            $controllerMethods = get_class_methods($controllerName); //获取测试类的所有方法
            foreach($controllerMethods as $k=>$method){    //遍历测试类的所有方法，判断方法是否以test开始
                if(strpos($method , 'test') === 0){
                    $controllerObj->$method();   //以test开始的方法则是测试方法
                }
            }
        }
        $this->outputAsHtml();
    }

    /**
     * 输出测试结果为html页面
     */
    private function outputAsHtml(){
        echo <<<EOF
            <style>
                table {width:100%}
                td{border:solid 1px #ddd;}

            </style>
EOF;
        echo '<table>';
        foreach($this->getTestResult() as $v){
            echo "<tr>";
            foreach($v as $v2)
                echo "<td>{$v2}</td>";
            echo "</tr>";
        }
        echo '</table>';
        exit;
    }


    /**
     * 获取测试结果的存储数组
     * @return array
     */
    protected function getTestResult(){
        return $GLOBALS['__testResults'];
    }

    /**
     * 将某个测试结果存入集合中
     * @param $result
     */
    protected function pushTestResult($result){
        $info = debug_backtrace();
        $data['method'] = $info[2]['function'];
        $data['file'] =   $info[2]['file'] . "(Line： {$info[1]['line']})";
        $data['result'] = $result ? "<span style=\"color:greenyellow\">成功</span>" :  "<span style=\"color:darkred\">失败</span>" ;;
        $data['runtime'] = microtime(true);
        $GLOBALS['__testResults'][] = $data;  //将测试结果存入
    }


    /**
     * 断言数据是否 “不为空”
     * @param $data mixed 待测试的数据
     */
    protected function assertNotEmpty($data){
        $this->pushTestResult( !empty($data)  );
    }

    /**
     * 断言数据是否 “为空”
     * @param $data mixed 待测试的数据
     */
    protected function assertEmpty($data){
        $this->pushTestResult( empty($data)  );
    }

    /**
     * 断言数据是否相等 “===”
     * @param $data mixed 待测试的数据
     */
    protected function assertEquals($data , $condition){
        $this->pushTestResult( $data === $condition  );
    }

    /**
     * 断言数据是否匹配正则表达式
     * @param $data mixed 待测试的数据
     * @param $regex string 正则表达式字符串
     */
    protected function assertRegex($data ,$regex){
        $this->pushTestResult( preg_match($regex , $data)  );
    }





}
<?php
/**
 * 所有测试的基础类
 * 请将本文件防止在 ??/ThinkPHP/Library/Think 目录下
 *
 * @author  T(zthi@qq.com)
 *
 * @version 0.95
 * Date : 2015/8/20
 * 新增了一个自动遍历测试文件的机制 ( $this->run(true); )
 * 新增了一个通过URL参数传递测试方法的机制
 *
 *
 * @version 0.9
 * Date: 2015/8/13
 */

namespace T;
use Think\Controller;

defined('ROOT_ABS_PATH') || define('ROOT_ABS_PATH' , dirname( __FILE__) ); //项目绝对路径
defined('APP_ABS_PATH') || define('APP_ABS_PATH' ,ROOT_ABS_PATH .DIRECTORY_SEPARATOR .  'Application'); //项目Application绝对路径
defined('TEST_CONTROLLER_FILE_EXT') || define('TEST_CONTROLLER_FILE_EXT' , '.class.php');   //测试文件的后缀
defined('TEST_CONTROLLER_NAME') || define('TEST_CONTROLLER_NAME' , 'Controller');           //测试文件的控制器名称

class UnitTest extends Controller {
    function __construct(){
        parent::__construct();
        header("Content-type:text/html;charset=utf-8");
    }

    // 断言错误提示语句
    const ERROR_PARAM_INTEGER = "断言参数错误，需要integer";
    const ERROR_PARAM_STRING_OR_INTEGER = "断言参数错误，需要integer或string";
    const ERROR_PARAM_NUMERIC = "断言参数错误，需要numeric";
    const ERROR_PARAM_ARRAY = "断言参数错误，需要array";
    const ERROR_PARAM_STRING = "断言参数错误，需要string";
    const ERROR_PARAM_OBJECT = "断言参数错误，需要object";
    const ERROR_PARAM_BOOL = "断言参数错误，需要true 或 false";

    const ASSERT_STATUS_FAILED = 0; //断言失败状态
    const ASSERT_STATUS_SUCCESS = 1; //断言成功状态
    const ASSERT_STATUS_ERROR = 2; //断言错误状态

    // 测试状态对应的颜色 array( background-color , font-color )
    private $assertColors = array(
        self::ASSERT_STATUS_FAILED => array('#FFA38C', 'black'), //背景浅红色 ， 字体白色
        self::ASSERT_STATUS_SUCCESS => array('#B5FF64', 'black'), //背景， 字体白色
        self::ASSERT_STATUS_ERROR => array('#AF7942' , 'black'), //背景黄色， 字体白色
    );

    // 测试状态提示语句
    private $assertStatusMessage = array(
        self::ASSERT_STATUS_FAILED => '失败',
        self::ASSERT_STATUS_SUCCESS => '成功',
        self::ASSERT_STATUS_ERROR => '参数错误'
    );

    // 测试结果输出的字段设置，可任意注释相关字段
    private $outPutField = array(
        //'status' => '状态',
        'statusMessage' => '结果',
        'data'=> '测试数据',
        'message'=>'备注',
        'class' => '测试类',
        'method' => '测试方法' ,
        'assertMethod'=>'断言方法',
        'fileLine' => '所在文件（行）',
        'runtime' => '运行时间'
    );

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
     * @param $autoGetTestControllers boolean 是否自动获取测试类 , default : false
     */
    protected function run($autoGetTestControllers = false){
        if($autoGetTestControllers){
            $debugBacktrace = debug_backtrace();
            $testControllers = $this->getControllersByDir(dirname($debugBacktrace[0]['file']));
        }
        else
            $testControllers = $this->testControllers;

        //从URL读取要执行的测试类,如果为空，则读取全部测试类
        $allowControllers = null;
        if(!empty($_GET['controller'])){
            $allowControllers = explode(',', $_GET['controller']);
            if(!empty($allowControllers))
            {
                foreach($testControllers as $k=>$temp){
                    $allow = false;
                    foreach($allowControllers as $tempController) {  //过滤testControllers
                        if (strstr($temp . TEST_CONTROLLER_FILE_EXT , $tempController . TEST_CONTROLLER_NAME .  TEST_CONTROLLER_FILE_EXT)) {
                            $allow = true;
                            break;
                        }
                    }
                    if(!$allow)
                        unset($testControllers[$k]);
                }
            }
        }

        foreach($testControllers as $controllerName){
            $controllerName = str_replace(DIRECTORY_SEPARATOR , '\\' , $controllerName);
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
     * 获取目录下所有的controller
     * @param $dir
     * @return array
     */
    private function getControllersByDir($dir){
        $files = glob($dir . DIRECTORY_SEPARATOR . "*" . TEST_CONTROLLER_NAME . TEST_CONTROLLER_FILE_EXT);
        foreach($files as $k=>$v){
            $temp = trim(  str_replace( array( TEST_CONTROLLER_FILE_EXT) , '' , $v) , '\\'  );
            $temp  = substr($temp , strrpos($temp , DIRECTORY_SEPARATOR) + 1) ;
            $files[$k] =  '\\' . MODULE_NAME .'\Controller\\' .  $temp;
        }

        return $files;
    }

    /**
     * 输出测试结果为html页面
     */
    protected function outputAsHtml(){

        $colors = $this->assertColors;
        $assertMessages = $this->assertStatusMessage;
        $results = $this->getTestResult();

        echo <<<EOF
            <style>
                table {width:100% ; border-collapse: collapse}
                table td,table th{border:solid 1px #ccc;padding:5px}
                .num{font-size:1.4em;font-weight:bold}
            </style>
EOF;

        echo   '<h2 style="text-align:center;">';
        foreach($this->getTestResultTotal() as $status=>$count){
            $color = $colors[$status];
            $assertMessage = $assertMessages[$status];
            echo "<div style=\"display:inline-block;padding:5px;margin-right:10px;background:{$color[0]} ; color:{$color[1]}\">{$assertMessage}(<span class=\"num\">{$count}</span>)</div>";
        }
        echo   '</h2>';


        echo '<table>';
        echo '<tr>';
        foreach($this->outPutField as $fieldKey => $fieldName)
            echo "<th>{$fieldName}</th>";
        echo '</tr>';

        foreach($results as $result){
            $color = $colors[$result['status']];
            echo "<tr style=\"background:{$color[0]};color:{$color[1]} \">";
            foreach($this->outPutField as $fieldKey => $fieldName)
                echo "<td>{$result[$fieldKey]}</td>";
            echo "</tr>";
        }
        echo '</table>';
        exit;
    }

    /**
     * 汇总测试结果
     * @return array
     */
    private function getTestResultTotal(){
        $total = array();
        foreach($this->getTestResult() as $result){
            $total[$result['status']] ++ ;
        }
        return $total;
    }

    /**
     * 将某个测试结果存入集合中
     * @param $result boolean|int 断言结果
     * @param $testData mixed 测试时传入的数据
     * @param $message mixed 提示信息
     */
    protected function pushTestResult($result , $message = '' , $testData = null){
        if(is_null($testData))
            $testData = 'NULL';
        else if(is_bool($testData))
            $testData = $testData ? 'true' : 'false';
        else if(is_array($testData))
            $testData = 'Array('.count($testData).')：' . json_encode($testData , JSON_UNESCAPED_UNICODE) ;
        else if(is_object($testData)){
            $testData = 'Object：' . json_encode((Array)$testData ,  JSON_UNESCAPED_UNICODE) ;
        }

        if(strlen($testData) > 100)
            $testData = substr($testData , 0 , 100) . '...';

        if(is_bool($result)){
            $data['status'] = $result ? self::ASSERT_STATUS_SUCCESS : self::ASSERT_STATUS_FAILED ; //断言状态 ：1：成功 , 0:失败
            $data['message'] = $message;
            $debugIndex = 2;
        }
        else{
            $data['status'] = self::ASSERT_STATUS_ERROR ;  //断言状态： 2：断言方法参数错误
            $data['message'] = $result;
            $debugIndex = 3;
        }
        $assertMessage = $this->assertStatusMessage;
        $data['statusMessage'] = $assertMessage[$data['status']] ;
        $info = debug_backtrace();
        $data['class'] = $info[$debugIndex]['class'];
        $data['data'] = is_array( $testData ) ? json_encode($testData) : $testData;
        $data['file'] =  str_replace(ROOT_ABS_PATH , '' , $info[$debugIndex-1]['file']);
        $data['method'] = $info[$debugIndex]['function'];
        $data['assertMethod'] = $info[$debugIndex-1]['function'];
        $data['fileLine'] =   $data['file']  . "( Line： {$info[$debugIndex-1]['line']} )";
        $data['runtime'] = self::getRuntime(true) ;
        $GLOBALS['__testResults'][] = $data;  //将测试结果存入
    }


    /**
     * 获取测试结果的存储数组
     * @return array
     */
    protected function getTestResult(){
        return $GLOBALS['__testResults'];
    }



    /**
     * 断言数组 包含某个key
     * @param $key
     * @param $array
     * @param string $message
     */
    function assertArrayHasKey($key , $array ,  $message = '')
    {
        if($this->isArray($array) && $this->isStringOrInteger($key) )
            $this->pushTestResult( !(!is_array($array) || !isset($array[$key])) , $message , $array);
    }

    /**
     * 断言数组  不包含某个key
     * @param $key
     * @param $array
     * @param string $message
     */
    function assertNotArrayHasKey( $key , $array , $message = '')
    {
        if($this->isArray($array) && $this->isStringOrInteger($key))
            $this->pushTestResult( (!is_array($array) || !isset($array[$key])) , $message , $array);
    }

    /**
     * 断言数组的元素个数等于某值
     * @param integer $expectedCount  断言预期值
     * @param array  $array  数组
     * @param string  $message
     */
    public function assertCount( $expectedCount , $array ,  $message = '')
    {
        if($this->isArray($array) && $this->isInteger($expectedCount) )
            $this->pushTestResult( count($array) === $expectedCount , $message , $array);
    }

    /**
     * 断言数组的元素个数不等于某值
     * @param integer $expectedCount  断言预期值
     * @param array  $array  数组
     * @param string  $message
     */
    public function assertNotCount( $expectedCount,  $array ,  $message = '')
    {
        if($this->isArray($array) && $this->isInteger($expectedCount) )
            $this->pushTestResult( count($array) !== $expectedCount , $message , $array );
    }

    /**
     * 断言数据相等 “==”
     * @param $expected mixed 与data比较的值
     * @param $actual mixed 实际值
     * @param $message string 提示语句
     */
    protected function assertEquals( $expected ,  $actual ,$message = ''){
        $this->pushTestResult( $actual == $expected , $message , $actual );
    }


    /**
     * 断言数据不相等 “!=”
     * @param $expected mixed 与data比较的值
     * @param $actual mixed 实际值
     * @param $message string 提示语句
     */
    protected function assertNotEquals($expected , $actual , $message = ''){
        $this->pushTestResult( $actual != $expected , $message  , $actual );
    }


    /**
     * 断言数据相等，且类型一致 “===”
     * @param $expected mixed 与data比较的值
     * @param $actual mixed 实际值
     * @param $message string 提示语句
     */
    protected function assertSame( $expected ,  $actual ,$message = ''){
        $this->pushTestResult( $actual === $expected , $message  , $actual );
    }


    /**
     * 断言数据不相等 或 类型不一致 “!==”
     * @param $expected mixed 与data比较的值
     * @param $actual mixed 实际值
     * @param $message string 提示语句
     */
    protected function assertNotSame($expected , $actual , $message = ''){
        $this->pushTestResult( $actual !== $expected , $message  , $actual );
    }

    /**
     * 断言数据 “不为空的数组”
     * @param $data array 待测试的数组
     * @param $message string 提示语句
     */
    protected function assertNotEmptyArray($data , $message = ''){
        if($this->isArray($data))
            $this->pushTestResult(!empty($data), $message ,$data);
    }

    /**
     * 断言数据 “为空的数组”
     * @param $data array 待测试的数组
     * @param $message string 提示语句
     */
    protected function assertEmptyArray($data , $message = ''){
        if($this->isArray($data))
            $this->pushTestResult(empty($data), $message ,$data);
    }

    /**
     * 断言数据 “不为空”
     * @param $data mixed 待测试的数据
     * @param $message string 提示语句
     */
    protected function assertNotEmpty($data , $message = ''){
        $this->pushTestResult( !empty($data) , $message ,$data);
    }

    /**
     * 断言数据 “为空”
     * @param $data mixed 待测试的数据
     * @param $message string 提示语句
     */
    protected function assertEmpty($data , $message = ''){
        $this->pushTestResult( empty($data) ,  $message ,$data );
    }

    /**
     * 断言大于
     * @param $expected mixed 预期值
     * @param $actual mixed  实际值
     * @param string $message
     */
    public function assertGreater( $expected , $actual, $message = '')
    {
        $this->pushTestResult( $actual > $expected , $message ,  $actual);
    }

    /**
     * 断言大于等于
     * @param $expected mixed 预期值
     * @param $actual mixed  实际值
     * @param string $message
     */
    public function assertGreaterOrEquals( $expected , $actual, $message = '')
    {
        $this->pushTestResult( $actual >= $expected , $message ,  $actual);
    }

    /**
     * 断言小于
     * @param $expected mixed 预期值
     * @param $actual mixed  实际值
     * @param string $message
     */
    public function assertLess( $expected , $actual, $message = '')
    {
        $this->pushTestResult( $actual < $expected , $message  , $actual);
    }

    /**
     * 断言大于等于
     * @param $expected mixed 预期值
     * @param $actual mixed  实际值
     * @param string $message
     */
    public function assertLessOrEquals( $expected , $actual, $message = '')
    {
        $this->pushTestResult( $actual <= $expected , $message  , $actual);
    }

    /**
     * 断言 真
     * @param $bool boolean
     * @param string $message
     */
    public function assertTrue( $bool , $message = '')
    {
        if($this->isBoolean($bool))
            $this->pushTestResult( $bool === true , $message , $bool);
    }

    /**
     * 断言 假
     * @param $bool boolean
     * @param string $message
     */
    public function assertFalse( $bool , $message = '')
    {
        if($this->isBoolean($bool))
            $this->pushTestResult( $bool === false , $message , $bool);
    }


    /**
     * 断言 NULL
     * @param $data mixed
     * @param string $message
     */
    public function assertNull( $data , $message = '')
    {
        $this->pushTestResult( $data === null, $message , $data);
    }

    /**
     * 断言 非NULL
     * @param $data mixed
     * @param string $message
     */
    public function assertNotNull( $data , $message = '')
    {
        $this->pushTestResult( $data !== null, $message , $data) ;
    }



    /**
     * 断言数据匹配正则表达式
     * @param $regex string 正则表达式字符串
     * @param $string mixed 待测试的数据
     * @param $message string 提示语句
     */
    protected function assertRegex($regex ,$string , $message = ''){
        if($this->isString($string) && $this->isString($regex))
            $this->pushTestResult( preg_match( $regex , $string) ? true : false  , $message , $string);
    }

    /**
     * 断言数据 不匹配正则表达式
     * @param $regex string 正则表达式字符串
     * @param $string mixed 待测试的数据
     * @param $message string 提示语句
     */
    protected function assertNotRegex($regex , $string, $message = ''){
        $this->pushTestResult( preg_match($regex , $string) ? false : true , $message ,$string);
    }


    /**
     * 断言对象具有属性
     * @param string $attributeName 属性
     * @param object $object    对象
     * @param string $message
     */
    public function assertObjectHasAttribute($attributeName, $object, $message = '')
    {
        if($this->isString($attributeName) && $this->isObject($object) )
            $this->pushTestResult( $object->hasProperty($attributeName) , $message ,$object);
    }

    /**
     * 断言对象 不具有属性
     * @param string $attributeName 属性
     * @param object $object  对象
     * @param string $message
     */
    public function assertNotObjectHasAttribute($attributeName, $object, $message = '')
    {
        if($this->isString($attributeName) && $this->isObject($object) )
            $this->pushTestResult( !$object->hasProperty($attributeName) , $message ,$object);
    }


    /**
     * 断言对象是某个类的实例
     * @param string $expectedClssName 类名
     * @param object $object  对象
     * @param string $message
     */
    public function assertInstanceOf($expectedClssName, $object, $message = '')
    {
        if($this->isString($expectedClssName) && $this->isObject($object))
            $this->pushTestResult( $object instanceof $expectedClssName , $message , $object );
    }

    /**
     * 断言对象 不是某个类的实例
     * @param string $expectedClssName 类名
     * @param object $object  对象
     * @param string $message
     */
    public function assertNotInstanceOf($expectedClssName, $object, $message = '')
    {
        if($this->isString($expectedClssName) && $this->isObject($object))
            $this->pushTestResult( !$object instanceof $expectedClssName , $message , $object );
    }


    /**
     * 断言对象 是合法json字符串
     * @param string $json json字符串
     * @param string $message
     */
    public function assertJson($json, $message = '')
    {
        if ($this->isString($json))
            $this->pushTestResult( !is_null(json_decode($json))  ,  $message , $json);
    }

    /**
     * 断言对象 是非法 json字符串
     * @param string $json json字符串
     * @param string $message
     */
    public function assertNotJson($json, $message = '')
    {
        if ($this->isString($json))
            $this->pushTestResult( is_null(json_decode($json))  ,  $message , $json);
    }

    /**
     * 断言文件夹或文件存在
     * @param string $file json字符串
     * @param string $message
     */
    public function assertFileExists($file, $message = '')
    {
        if ($this->isString($file))
            $this->pushTestResult( file_exists($file)  ,  $message);
    }

    /**
     * 断言文件夹或文件 不存在
     * @param string $file json字符串
     * @param string $message
     */
    public function assertNotFileExists($file, $message = '')
    {
        if ($this->isString($file))
            $this->pushTestResult( !file_exists($file)  ,  $message);
    }


    /**
     * 判断数据类型是integer。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $integer integer
     * @return boolean
     */
    private function isInteger($integer){
        if(!is_int($integer))
        {
            $this->pushTestResult(self::ERROR_PARAM_INTEGER);
            return false;
        }
        return true;
    }

    /**
     * 判断数据类型是数字（numeric）。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $numeric mixed
     * @return boolean
     */
    private function isNumeric($numeric){
        if(!is_numeric($numeric))
        {
            $this->pushTestResult(self::ERROR_PARAM_NUMERIC);
            return false;
        }
        return true;
    }





    /**
     * 判断数据类型是数组。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $array mixed
     * @return boolean
     */
    private function isArray($array){
        if(!is_array($array))
        {
            $this->pushTestResult(self::ERROR_PARAM_ARRAY);
            return false;
        }
        return true;
    }

    /**
     * 判断数据类型是对象。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $obj mixed
     * @return boolean
     */
    private function isObject($obj){
        if(!is_object($obj))
        {
            $this->pushTestResult(self::ERROR_PARAM_OBJECT);
            return false;
        }
        return true;
    }

    /**
     * 判断数据类型是字符串。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $string mixed
     * @return boolean
     */
    private function isString($string){
        if(!is_string($string))
        {
            $this->pushTestResult(self::ERROR_PARAM_STRING);
            return false;
        }
        return true;
    }


    /**
     * 判断数据类型是字符串或数字。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $data mixed
     * @return boolean
     */
    private function isStringOrInteger($data){
        if(!is_string($data) && !is_int($data))
        {
            $this->pushTestResult(self::ERROR_PARAM_STRING_OR_INTEGER);
            return false;
        }
        return true;
    }

    /**
     * 判断数据类型是布尔值。
     * 成功返回true
     * 失败返回false，并将错误加入 testResult
     * @param $bool mixed
     * @return boolean
     */
    private function isBoolean($bool){
        if(!is_bool($bool))
        {
            $this->pushTestResult(self::ERROR_PARAM_BOOL);
            return false;
        }
        return true;
    }


    /**
     * @param float|boolean $startTime 开始时间
     *      一般是float类型  通过microtime（true）得到的结果
     *      如果传递true，则将上次调用该方法的时间作为 startTime
     * @param int $type  type=1 返回毫秒  ，其他返回秒
     * @return string
     */
    public static function getRuntime($startTime = null , $type = 1) //
    {
        if ($startTime === true)
            $startTime = isset($GLOBALS['lastRequestTime']) ? $GLOBALS['lastRequestTime'] : $_SERVER['REQUEST_TIME_FLOAT'];
        else if (empty($startTime))
            $startTime = $_SERVER['REQUEST_TIME_FLOAT'];

        $now = microtime(true);
        $runtime = $now - $startTime;
        $GLOBALS['lastRequestTime'] = $now;

        if ($type === 1)
            return round(($runtime * 1000) , 2) . "毫秒";
        return round($runtime , 2)."秒";
    }

}
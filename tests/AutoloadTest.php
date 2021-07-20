<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用オートローダー
 * Start Date  : 2021/07/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \PHPUnit\Framework\TestCase;
use \mikisan\core\util\Autoload;
use \mikisan\core\exception\ClassNotFoundException;
use \mikisan\core\exception\DirectoryNotFoundException;

$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/core/exceptions/ClassNotFoundException.php";
require_once "{$project_root}/core/exceptions/DirectoryNotFoundException.php";

require_once __DIR__  . "/TestCaseExtend.php";
require_once __DIR__  . "/../src/Autoload.php";

class AutoloadTest extends TestCaseExtend
{
    private $class_name = "mikisan\core\util\Autoload";
    
    public function setUp(): void
    {
        Autoload::init();
    }
    
    public function test_register_single()
    {
        $dir    = realpath(__DIR__ . "/folder");
        Autoload::register($dir);
        
        $registerd  = Autoload::registerd();
        $this->assertCount(1,       $registerd);
        $this->assertEquals($dir,   $registerd[0]);
    }
    
    public function test_register_file()
    {
        $dir    = realpath(__DIR__ . "/folder/Test.php");
        
        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage("引数で渡されたディレクトリは存在しません。[{$dir}]");
        
        Autoload::register($dir);
    }
    
    public function test_register_fictional_dir()
    {
        $dir    = __DIR__ . "/folder_notexixts";
        
        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage("引数で渡されたディレクトリは存在しません。[{$dir}]");
        
        Autoload::register($dir);
    }
    
    public function test_register_multiple()
    {
        $dir    = realpath(__DIR__ . "/folder");
        Autoload::register($dir, true);
        
        $registerd  = Autoload::registerd();
        $this->assertCount(3,       $registerd);
        //
        $this->assertEquals($dir,   $registerd[0]);
        $this->assertEquals($dir . "/subfolder",   $registerd[1]);
        $this->assertEquals($dir . "/subfolder/magofolder",   $registerd[2]);
    }
    
    public function test_autoload_class_not_found()
    {
        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("指定されたクラスが読み込まれていません。[mikisan\core\another\Test]");
        
        $dir    = realpath(__DIR__ . "/folder");
        Autoload::register($dir);
        
        $result = mikisan\core\another\Test::hello();
    }
    
    public function test_autoload()
    {
        $dir    = realpath(__DIR__ . "/folder");
        Autoload::register($dir);
        
        $result = mikisan\core\util\Test::hello();
        $this->assertEquals("Hello World.", $result);
    }
    
    public function test_autoload_register_multiple()
    {
        $dir    = realpath(__DIR__ . "/folder");
        Autoload::register($dir, true);
        //
        $result = mikisan\core\another\Test::hello();
        $this->assertEquals("Hello New World.", $result);
        //
        $result = mikisan\core\util\Test::hello();
        $this->assertEquals("Hello World.", $result);
    }
    
}

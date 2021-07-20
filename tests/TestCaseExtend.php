<?php

/**
 * Project Name: mikisan-ware
 * Description : ルーター
 * Start Date  : 2021/07/18
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class TestCaseExtend extends TestCase
{

    protected function callMethod(string $class_name, string $method, array $parameters = [])
    {
        try
        {
            $reflection = new \ReflectionClass($class_name);
        }
        catch(\ReflectionException $e)
        {
            throw new \Exception($e->getMessage());
        }

        $method         = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs(new $class_name(), $parameters);
    }
    
    public function test_test()
    {
        $this->assertTrue(true);
    }
    
}

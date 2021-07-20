<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用オートローダー
 * Start Date  : 2021/07/20
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\util;

use mikisan\core\exception\ClassNotFoundException;
use mikisan\core\exception\DirectoryNotFoundException;

class Autoload
{

    private static $instance;
    private $dirs     = [];       // クラス探索ディレクトリリスト
    private $required = [];       // require済みクラスリスト

    public function __construct()
    {
        // __autoload()の実装として、Autolod::autoLoad()を登録する
        spl_autoload_register([$this, "autoLoad"]);
    }
    
    public function init(): void
    {
        if(self::$instance === null)    { return; }
        self::$instance->dirs   = [];
    }
    
    public function registerd(): array
    {
        return self::$instance->dirs;
    }
    
    public function required(): array
    {
        return self::$instance->required;
    }
    
    /**
     * クラス探索ディレクトリをリストに登録する
     * 
     * @param   string  $directory_path         クラス探索ディレクトリリストに登録するディレクトリパス
     * @param   bool    $should_include_subdir  サブディレクトリを再帰的にクラス探索ディレクトリに含めるか？
     */
    public static function register(string $directory_path, bool $should_include_subdir = false): void
    {
        if(self::$instance === null)
        {
            self::$instance = new self;
        }

        // 連続する / を単一の / に置き換える
        $normalized_path = preg_replace("|/+|u", "/", $directory_path);

        // 末尾に /  がある場合は取り除く
        $target = preg_replace("|/$|u", "", $normalized_path);
        
        self::register_recuasive($target, $should_include_subdir);
    }

    /**
     * autoload対象ディレクトリの検査と登録
     * 
     * @param   string      $target_directory
     * @param   bool        $should_include_subdir
     * @return  void
     * @throws  \pine\PineException
     */
    private function register_recuasive(string $target_directory, bool $should_include_subdir): void
    {
        if(!is_dir($target_directory))
        {
            throw new DirectoryNotFoundException($target_directory);
        }
        if(in_array($target_directory, self::$instance->dirs))  { return; }

        self::$instance->dirs[] = $target_directory;
        
        // サブディレクトリを再帰的にクラス探索ディレクトリに含めない場合は return
        if(!$should_include_subdir)         { return; }

        // 再帰処理
        $dir = new \DirectoryIterator($target_directory);
        foreach($dir as $path)
        {
            if($path->isDot())              { continue; }
            if(!$path->isDir())             { continue; }
            if((string)$path === "trunk")   { continue; }

            // 再帰的に子ディレクトリの追加
            self::register_recuasive("{$target_directory}/{$path}", $should_include_subdir);
        }
    }

    public function autoLoad($classname)
    {
        if(in_array($classname, $this->required))  { return; }

        // ネームスペースを考慮し、純粋なクラス名だけを抽出する
        $parts          = explode("\\", $classname);
        $targetclass    = array_pop($parts);
        
        foreach($this->dirs as $dir)
        {
            $filepath       = "{$dir}/{$targetclass}.php";
            if(!is_readable($filepath)) { continue; }
            if(!$this->check_namespace($filepath, implode("\\", $parts)))  { continue; }
            
            require_once    $filepath;
            $this->required[] = $classname;
            return;
        }
        throw new ClassNotFoundException($classname);
    }
    
    private function check_namespace(string $filepath, string $namespace): bool
    {
        if(false === ($file = fopen($filepath, "r")))   { return false; }
        $matched    = false;
        while($line = fgets($file))
        {
            if(preg_match("|^\s*class\s|ui", $line))       { break; }
            if(!preg_match("|^\s*namespace\s|ui", $line))   { continue; }
            
            $fixed      = preg_replace("|\\\\|u", "\\\\\\\\", $namespace);
            $pattern    = "|\s{$fixed};|u";
            $matched    = (bool)preg_match($pattern, $line);
            break;
        }
        fclose($file);
        return $matched;
    }
    
}

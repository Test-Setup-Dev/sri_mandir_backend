<?php
namespace App\Helpers;

class Logger
{
    public static $logType;
    public static $className;
    public static $methodName;
    public static $lineNo;
    public static $tag;
    public static $message;
    public static $extra;

    public static function print()
    {
        // For testing, simple print to log
        \Log::info(json_encode([
            'type' => self::$logType,
            'class' => self::$className,
            'method' => self::$methodName,
            'line' => self::$lineNo,
            'tag' => self::$tag,
            'message' => self::$message,
            'extra' => self::$extra,
        ]));
    }
}

<?php
namespace App\Message;

class Message
{
    public const MSG_SUCCESS = 0;
    public const MSG_ERROR  = 1;
    public const MSG_INFO    = 2;
    public const MSG_WARNING = 3;
    private const MSG_TYPE_STRING = [
        'success',
        'error',
        'info',
        'warning'
    ];

    public static function add(int $type, string $message) : void
    {
        $_SESSION['messages'][] = [
            "type" => self::MSG_TYPE_STRING[$type],
            "message" => $message
        ];
    }

    public static function show(bool $clear = true) : array
    {
        $messages = $_SESSION['messages'] ?? [];
        if ($clear) {
            $_SESSION['messages'] = [];
        }
        return $messages;
    }
}
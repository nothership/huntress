<?php

/*
 * Copyright (c) 2018 Keira Aro <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

namespace Huntress\Plugin;

/**
 * Simple builtin to show user information
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class Management implements \Huntress\PluginInterface
{
    use \Huntress\PluginHelperTrait;

    public static function register(\Huntress\Bot $bot)
    {
        $bot->client->on(self::PLUGINEVENT_COMMAND_PREFIX . "update", [self::class, "update"]);
        $bot->client->on(self::PLUGINEVENT_COMMAND_PREFIX . "restart", [self::class, "restart"]);
    }

    public static function update(\Huntress\Bot $bot, \CharlotteDunois\Yasmin\Models\Message $message): \React\Promise\ExtendedPromiseInterface
    {
        if (!in_array($message->author->id, $bot->config['evalUsers'])) {
            return self::unauthorized($message);
        } else {
            try {
                return self::send($message->channel, "```" . PHP_EOL . self::gitPull() . "```", ['split' => ['before' => '```' . PHP_EOL, 'after' => '```']]);
            } catch (\Throwable $e) {
                return self::exceptionHandler($message, $e, true);
            }
        }
    }

    public static function restart(\Huntress\Bot $bot, \CharlotteDunois\Yasmin\Models\Message $message): \React\Promise\ExtendedPromiseInterface
    {
        if (!in_array($message->author->id, $bot->config['evalUsers'])) {
            return self::unauthorized($message);
        } else {
            die();
        }
    }

    private static function gitPull(): string
    {
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        $pipes          = [];
        if (php_uname('s') == "Windows NT") {
            $process = proc_open('sh -x update 2>&1', $descriptorspec, $pipes);
        } else {
            $process = proc_open('./update 2>&1', $descriptorspec, $pipes);
        }
        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout = trim(stream_get_contents($pipes[1]));
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return $stdout;
        } else {
            throw new \Exception("Could not init script");
        }
    }
}
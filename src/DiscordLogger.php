<?php

namespace dyanakiev\LoggerDiscordChannel;

use Monolog\Logger;

class DiscordLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $log = new Logger('discord');

        if (app()->environment($config['environment'] ?? 'production')) {
            $log->pushHandler(new DiscordHandler($config));
        }

        return $log;
    }
}


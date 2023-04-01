<?php

namespace dyanakiev\LoggerDiscordChannel;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Monolog\Formatter\LineFormatter;
use \Monolog\Logger;
use \Monolog\Handler\AbstractProcessingHandler;
use Psr\Log\LogLevel;

class DiscordHandler extends AbstractProcessingHandler
{
    private $guzzle;
    private $suffix;
    private $webhook;
    private $message;
    private $context;

    /**
     * MonologDiscordHandler constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->suffix = $config['suffix'] ?? '';
        $this->guzzle = new \GuzzleHttp\Client();
        $this->webhook = $config['webhook'] ?? false;
        $this->message = $config['message'] ?? false;
        $this->context = $config['context'] ?? false;
        parent::__construct($config['level'] ?? 'debug', $this->bubble);

    }

    /**
     * @param array $record
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function write($record): void
    {
        $message = new LineFormatter('%message%', null, true, true);
        $message = $message->format($record);

        if($this->context) {
            $stacktrace = new LineFormatter('%context% %extra%', null, true, true);
            $stacktrace->includeStacktraces();
            $stacktrace = $stacktrace->format($record);
        }

        // Add emoji based on the error level
        switch ($record->level->toPsrLogLevel()) {
            case LogLevel::NOTICE:
                $emoji = ':helicopter:';
                break;
            case LogLevel::WARNING:
                $emoji = ':warning:';
                break;
            case LogLevel::INFO:
                $emoji = ':information_source:';
                break;
            case LogLevel::DEBUG:
                $emoji = ':zap:';
                break;
            default:
                $emoji = ':boom:';
                break;
        }

        // Add fields
        $fields = [];

        $request = request();

        // Add the request url if any
        $request_url = $request?->fullUrl() ?? false;
        if ($request_url && !app()->runningInConsole()) {
            $fields[] = [
                'name' => 'Visited URL',
                'value' => $request?->fullUrl()
            ];
        }

        // Add the logged in user id if any
        $user_id = $record->context['userId'] ?? false;
        if ($user_id) {
            $fields[] = [
                'name' => 'User ID',
                'value' => $user_id
            ];
        }

        // Add the file path if exception
        if(isset($record->context['exception'])) {
            $file_path = $record->context['exception']->getFile() ?? false;
            $file_line = $record->context['exception']->getLine() ?? 'n/a';

            if ($file_path) {
                $fields[] = [
                    'name' => 'File path',
                    'value' => '`' . str($file_path)->replace(base_path(), '') . '` at line **' . $file_line . '**'
                ];
            }
        }

        // Set embeds
        $log['embeds'][] = [
            'title' => '**[' . now()->format('d.m.Y H:i:s') . ']** '.str($record->level->getName())->lower()->ucfirst().' ' . $emoji.' '.$this->suffix,
            'description' => "```css\n" . str($message)->limit('4000') . '```',
            'color' => 0xE74C3C,
            'fields' => $fields
        ];

        // Add full context
        if($this->context === true && $stacktrace) {
            $log['embeds'][] = [
                'title' => 'Full context',
                'description' => "```css\n" . str($stacktrace)->limit('4000') . '```',
                'color' => 0xE74C3C,
            ];
        }

        // Add custom message
        if ($this->message) $log['content'] = $this->message;

        if ($this->webhook) {
            try {
                // Send it to discord
                $this->guzzle->request('POST', $this->webhook, [
                    RequestOptions::JSON => $log,
                ]);
            } catch (\Exception $e) {
                //silently fail better than killing the whole app
            }
        }
    }
}

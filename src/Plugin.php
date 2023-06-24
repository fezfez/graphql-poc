<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

use function file_put_contents;
use function json_encode;
use function microtime;

use const JSON_PRETTY_PRINT;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @uses onPostAutoloadDump
     *
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return ['post-autoload-dump' => 'onPostAutoloadDump'];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function onPostAutoloadDump(Event $event): void
    {
        $composer = $event->getComposer();
        $config   = Config::from($composer);
        $io       = $event->getIO();

        $start = microtime(true);
        $io->write('<info>Generating graphql cache file</info>');
        self::dump();
        $io->write('<info>Generated graphql file</info>');
    }

    public static function dump(): void
    {
        $parser = new Parser();
        file_put_contents('graphql.json', json_encode([
            'type' => $parser->getType(),
            'query' => $parser->getQuery(),
        ], JSON_PRETTY_PRINT));
    }
}

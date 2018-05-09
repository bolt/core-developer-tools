<?php

namespace Bolt\Extension\CoreDeveloper\Command;

use Bolt\Bootstrap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Functional test database schema updater.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class TestDbSchemaUpdate extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('core:test-db-schema-update')
            ->setDescription('Update Sqlite database schema used in functional tests')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();
        $output = new ConsoleOutput();
        $fs = new Filesystem();
        if ($fs->exists('.git') === false) {
            $output->writeln("<error>\n\nNo .git found.\n</error>");
            exit();
        }
        if ($fs->exists('tests/phpunit/unit/resources/db/') === false) {
            $output->writeln("<error>\n\nNo tests/phpunit/unit/resources/db/ found.\n</error>");
            exit();
        }

        // Built temporary web root
        if ($fs->exists('tests/phpunit/web-root') === false) {
            $fs->mkdir('tests/phpunit/web-root/config');
        }

        $yaml = <<<EOF
paths:
    site: '../../../'
    config: './config'
    database: '../unit/resources/db'
EOF;
        $fs->dumpFile($cwd . '/tests/phpunit/web-root/.bolt.yml', $yaml);

        /** @var \Silex\Application $app */
        $app = Bootstrap::run($cwd . '/tests/phpunit/web-root');

        $request = Request::createFromGlobals();
        $app['request_stack']->push($request);
        $app['request'] = $request;
        $app->boot();

        $nut = $app['nut'];
        $nut->setAutoExit(false);

        // Output detailed check with SQL
        $args = 'database:check -s';
        $nut->run(new StringInput($args));

        // Do update
        $args = 'database:update';
        $nut->run(new StringInput($args));

        // Remove temporary web root
        $fs->remove('tests/phpunit/web-root');
    }
}

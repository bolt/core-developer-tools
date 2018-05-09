<?php

namespace Bolt\Extension\CoreDeveloper\Command;

use Bolt\Collection\MutableBag;
use Bolt\Filesystem\Exception\ParseException;
use Bolt\Filesystem\Finder;
use Bolt\Filesystem\Handler\YamlFile;
use Bolt\Filesystem\Manager;
use Bolt\Nut\BaseCommand;
use Bolt\Translation\TranslationFile;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Locale translation file updater.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class LocaleUpdate extends BaseCommand
{
    /** @var MutableBag */
    private $failed;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('core:locale-update')
            ->setDescription('Update locale file(s) to match keys in PHP & Twig')
            ->addArgument('locale', InputArgument::OPTIONAL, 'Locale to update, specifying nothing updates all locales')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getArgument('locale');
        $this->failed = MutableBag::of();

        $title = $locale ? 'Updating ' . $locale . ' locale' : 'Updating all locales';
        $this->io->title($title);

        /** @var Manager $fs */
        $fs = $this->app['filesystem'];
        $finder = $fs->find()
            ->files()
            ->in([
                "bolt://app/resources/translations/{$locale}",
            ])
            ->name('messages.*.yml')
            ->sortByName()
        ;

        $bar = new ProgressBar($this->io, $finder->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $bar->start();

        $this->doUpdateFiles($finder, $bar);

        $bar->finish();
        $this->io->writeln('');

        if (!$this->failed->isEmpty()) {
            $this->failed->prepend('Files that failed YAML validation:');
            $this->io->error($this->failed->toArray());

            return 1;
        }

        $this->io->success('Processing completed');

        return 0;
    }

    /**
     *
     */
    private function doUpdateFiles(Finder $finder, ProgressBar $bar)
    {
        foreach ($finder as $file) {
            /** @var YamlFile $file */
            list($domain, $locale) = explode('.', $file->getFilename('.yml'));
            $bar->setMessage("$locale ($domain)");
            $bar->advance();

            try {
                $file->parse();
            } catch (ParseException $e) {
                $this->io->writeln("\n[ERROR] Locale '$locale' failed to load: " . $e->getMessage());
                $this->failed->add($file->getRealPath());

                continue;
            }

            $this->update($file, $domain, $locale);
        }
    }

    /**
     *
     */
    private function update(YamlFile $file, string $domain, string $locale)
    {
        $translation = new TranslationFile($this->app, $domain, $locale);
        $yaml = $translation->content();
        if (!$yaml) {
            return;
        }

        $yaml = preg_replace('/[ \t]*$/','', $yaml);
        $file->put($yaml);
    }
}

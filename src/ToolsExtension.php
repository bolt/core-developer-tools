<?php

namespace Bolt\Extension\CoreDeveloper;

use Bolt\Extension\CoreDeveloper\Command;
use Bolt\Extension\SimpleExtension;
use Pimple as Container;

/**
 * An extension for helping core-developers.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ToolsExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerNutCommands(Container $container)
    {
        return [
            new Command\LocaleUpdate(),
        ];
    }
}

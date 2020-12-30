<?php

declare(strict_types=1);

namespace Chiron\Flash\Config;

use Chiron\Config\AbstractInjectableConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Flash\FlashBag;

final class FlashConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'flash';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'level' => Expect::int()->default(FlashBag::INFO),
            'tags' => Expect::list()->default([
                FlashBag::DEBUG   => 'debug',
                FlashBag::INFO    => 'info',
                FlashBag::SUCCESS => 'success',
                FlashBag::WARNING => 'warning',
                FlashBag::ERROR   => 'error',
            ]),
        ]);
    }

    public function getLevel(): int
    {
        return $this->get('level');
    }

    public function getTags(): array
    {
        return $this->get('tags');
    }
}

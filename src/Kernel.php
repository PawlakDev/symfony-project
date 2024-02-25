<?php

namespace App;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Console\Application;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureConsole(Application $console, LoggerInterface $logger): void
    {
        $console->add(new \App\Command\ICalCommand($logger));
    }
}
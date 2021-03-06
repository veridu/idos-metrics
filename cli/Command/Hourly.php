<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace Cli\Command;

use Cli\Handler;
use Cli\Utils\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command definition for hourly metrics cron.
 */
class Hourly extends AbstractCommand {
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure() {
        $this
            ->setName('cron:hourly')
            ->setDescription('idOS Metrics - Hourly')
            ->addOption(
                'logFile',
                'l',
                InputOption::VALUE_REQUIRED,
                'Path to log file'
            )
            ->addArgument(
                'endpoint',
                InputArgument::OPTIONAL,
                'The endpoint name that we want to separate the metrics'
            );
    }

    /**
     * Command execution.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $outpput
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $logFile = $input->getOption('logFile') ?? 'php://stdout';
        $logger  = new Logger('Hourly');
        $logger
            ->pushProcessor(new ProcessIdProcessor())
            ->pushProcessor(new UidProcessor())
            ->pushHandler(new StreamHandler($logFile, Logger::DEBUG));

        $logger->debug('Initializing idOS Metrics Hourly');
        $endpoint = $input->getArgument('endpoint');

        $handler = new Handler\Metrics($this->getDbConnection(), $this->getSaltConfig());

        if ($endpoint === null) {
            $endpoints = [
                'profile:source'
            ];
            foreach ($endpoints as $endpoint) {
                $handler->handleHourlyMetrics($endpoint);
            }
        } else {
            $handler->handleHourlyMetrics($endpoint);
        }

        $logger->debug('Runner completed');
    }
}

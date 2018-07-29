<?php

namespace uuf6429\DockerEtl\Console;

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    public function __construct(Application $application)
    {
        $this->setApplication($application);

        parent::__construct();
    }

    protected function configure()
    {
        $appName = $this->getApplication()->getName();

        $this
            ->setName('self-update')
            ->setAliases(['selfupdate'])
            ->addOption('rollback', 'r', InputOption::VALUE_NONE, "Revert to an older installation of {$appName}")
            ->setDescription("Updates {$appName} to the latest version");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appName = $this->getApplication()->getName();
        $updater = new Updater();
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $strategy = $updater->getStrategy();

        if ($strategy instanceof GithubStrategy) {
            $strategy->setPackageName("uuf6429/{$appName}");
            $strategy->setPharName("{$appName}.phar");
            $strategy->setCurrentLocalVersion($this->getApplication()->getVersion());
        }

        if ($input->getOption('rollback')) {
            $this->rollback($updater, $output);
        } else {
            $this->upgrade($updater, $output);
        }
    }

    private function upgrade(Updater $updater, OutputInterface $output)
    {
        if ($updater->update()) {
            $output->writeln([
                "Updated to <info>{$updater->getNewVersion()}</info>.",
                sprintf(
                    'Use <info>%s self-update --rollback</info> to return to version <comment>%s</comment>.',
                    $this->getApplication()->getName(),
                    $updater->getOldVersion()
                )
            ]);
        } else {
            $output->writeln(
                "<info>You are already using the latest version ({$this->getApplication()->getVersion()}).</info>"
            );
        }
    }

    private function rollback(Updater $updater, OutputInterface $output)
    {
        if ($updater->rollback()) {
            $output->writeln('Rolled back to previous version.');
        } else {
            $output->writeln('<comment>There was nothing to roll back to.</comment>');
        }
    }
}

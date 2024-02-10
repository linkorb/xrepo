<?php

namespace XRepo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use XRepo\XRepo;
use XRepo\Exporter\ArrayExporter;
use RuntimeException;

class ShowCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('show')
            ->setDescription('Show (filtered) repos')
            ->addOption(
                'repo',
                'r',
                InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED,
                'Repo name(s)'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED,
                'Limit repos to matches'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $xRepo = XRepo::fromEnv();
        $repos = $xRepo->getRepos();
        $repos = $xRepo->filterByInput($repos, $input);

        foreach ($repos as $repo) {
            $output->writeLn('<info>' . $repo->getName() . '</info>:');
            $output->writeLn('  attributes:');
            foreach ($repo->getAttributes() as $k=>$v) {
                $output->writeLn('    ' . $k . ': ' . $v . '');
            }
        }

        return 0;
    }
}

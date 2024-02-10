<?php

namespace XRepo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use XRepo\XRepo;
use XRepo\Exporter\ArrayExporter;
use Symfony\Component\Process\Process;
use RuntimeException;

class BackupCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('backup')
            ->setDescription('Make backup of selected repos')
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

        $total = count($repos);
        $i = 0;

        foreach ($repos as $repo) {
            // echo $repo->getName() . PHP_EOL;
 
            $backupPath = $xRepo->getBackupPath();

            $outputFilename = $backupPath . '/' . $repo->getName() . '.tgz';
            $outputDir = dirname($outputFilename);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $cmd = '';
            $cmd .= 'tar -I "gzip -v9 -n"  --exclude="./vendor" --exclude="./node_modules" --warning=no-file-changed ';
            $cmd .= '-cvf ' . $outputFilename . ' .';

            $perc = round(100 / $total * $i, 0);
            $output->writeLn('Archiving ' . $i .'/' . $total . ' (' . $perc . '%): ' . $repo->getPath() . ' to ' . $outputFilename);

            $process = Process::fromShellCommandline($cmd);
            $process->setTimeout(60 * 10); // 10 minutes
            $process->setWorkingDirectory($repo->getPath());
            $process->mustRun();
            //$output->writeLn($process->getOutput());
            //$output->writeLn($process->getErrorOutput());
            $i++;

        }
        return 0;
        
        // $output->writeLn($json);
    }
}

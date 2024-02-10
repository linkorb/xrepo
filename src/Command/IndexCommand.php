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

class IndexCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('index')
            ->setDescription('Scans your XREPO_CODE_PATH for git repos and caches the results in index.json')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $xRepo = XRepo::fromEnv();
        $repos = $xRepo->scan();

        $exporter = new ArrayExporter();
        $data = $exporter->export($repos);
        $json = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        file_put_contents($xRepo->getDataPath() . '/index.json', $json);

        $output->writeLn("Indexing completed. Repos indexed: " . count($repos));
        return 0;
    }
}

<?php

namespace XRepo;

use XRepo\Model\Repo;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use RuntimeException;

class XRepo
{
    protected $codePath;
    protected $dataPath;
    protected $backupPath;

    protected $repos;

    private function __construct()
    {
    }


    public function getBackupPath(): string
    {
        return $this->backupPath;
    }

    public function getCodePath(): string
    {
        return $this->codePath;
    }

    public function getDataPath(): string
    {
        return $this->dataPath;
    }

    public static function fromEnv()
    {
        $self = new self();
        $self->codePath = getenv('XREPO_CODE_PATH');
        $self->dataPath = getenv('XREPO_DATA_PATH');
        $self->backupPath = getenv('XREPO_BACKUP_PATH');

        if (!$self->codePath || !file_exists($self->codePath)) {
            throw new RuntimeException("XREPO_CODE_PATH configured incorrectly (check your .env): " . $self->codePath);
        }
        if (!$self->dataPath || !file_exists($self->dataPath)) {
            throw new RuntimeException("XREPO_DATA_PATH configured incorrectly (check your .env)");
        }
        if (!$self->backupPath || !file_exists($self->backupPath)) {
            throw new RuntimeException("XREPO_BACKUP_PATH configured incorrectly (check your .env)");
        }

        if ($self->dataPath==$self->codePath) {
            throw new RuntimeException("XREPO_CODE_PATH and XREPO_DATA_PATH should not be the same");
        }

        if ($self->backupPath==$self->codePath) {
            throw new RuntimeException("XREPO_CODE_PATH and XREPO_BACKUP_PATH should not be the same");
        }

        return $self;
    }

    public function getRepos(): array
    {
        if (!file_exists($this->dataPath . '/index.json')) {
            throw new RuntimeException("index.json not found, please run `scan` to generate it");
        }
        $json = file_get_contents($this->dataPath . '/index.json');
        $rows = json_decode($json, true);
        $repos = [];
        foreach ($rows as $row) {
            $repo = new Repo();
            $repo->setName($row['name']);
            $repo->setPath($row['path']);
            $repo->setStatus($row['status'] ?? null);
            $repo->setAttributes($row['attributes'] ?? []);
            $repos[$row['name']] = $repo;
        }
        return $repos;
    }


    public function filterByInput(array $repos, $input): array
    {
        $res = $repos;
        if (count($input->getOption('repo'))>0) {
            $res = [];
            $repoNames = $input->getOption('repo');
            foreach ($repoNames as $repoName) {
                $matches = false;
                foreach ($repos as $repo) {
                    if (fnmatch($repoName, $repo->getName())) {
                        $res[$repo->getName()] = $repo;
                        $matches = true;
                    }
                }
                if (!$matches) {
                    throw new RuntimeException("No matches for repo name: " . $repoName);
                }
            }
            $repos = $res;
        }

        if (count($input->getOption('limit'))>0) {
            $res = [];
            $limits = $input->getOption('limit');
            foreach ($limits as $limit) {
                $limit = trim($limit);
                $limit = str_replace('=', ':', $limit);
                $part = explode(':', $limit);
                if (count($part)>2) {
                    throw new RuntimeException("Invalid limit: " . $limit);
                }
                $key = $part[0];
                $value = $part[1] ?? null;
                
                foreach ($repos as $repo) {
                    if ($value) {
                        if ($repo->getAttribute($key)==$value) {
                            $res[$repo->getName()] = $repo;
                            $matches = true;
                        }
                    } else {
                        if ($repo->hasAttribute($key)) {
                            $res[$repo->getName()] = $repo;
                            $matches = true;
                        }
                    }
                }
                if (!$matches) {
                    throw new RuntimeException("No matches for limit: " . $limit);
                }
            }
        }

        return $res;
    }


    public function scan(): array
    {
        $dirs = array();
        $this->scanRecursive($this->codePath, $dirs);
        
        $projects= array();
        foreach ($dirs as $dir) {
            $repo = new Repo();
            $repo->setName(basename(dirname($dir)) . '/' . basename($dir));
            $head = trim(file_get_contents($dir . '/.git/HEAD'));


            $repo->setHead($head);
            
            $repo->setPath($dir);
            echo $dir . PHP_EOL;

            $configFilename = $dir . '/repo.yaml';
            if (file_exists($configFilename)) {
                $yaml = file_get_contents($configFilename);
                $config = Yaml::parse($yaml);
                if (!$config) {
                    throw new RuntimeException("Failed to parse repo.yaml for " . $repo->getName());
                }
                
                foreach ($config['attributes']??[] as $k=>$v) {
                    $repo->setAttribute($k, $v);
                }
            }
            /*
            $process = new Process(['git', 'status', '--porcelain']);
            $process->setWorkingDirectory($dir);
            $process->run();
            $repo->setStatus($process->getOutput());
            */

            $repos[] = $repo;
        }
        return $repos;
    }
    
    private function scanRecursive($path, &$dirs)
    {
        $files = scandir($path);
        foreach ($files as $filename) {
            $skip = false;
            switch ($filename) {
                case '.':
                case '..':
                case '.git':
                case 'vendor':
                case 'node_modules':
                    $skip = true;
                    break;
            }
            
            if (!$skip) {
                if (is_dir($path . '/' . $filename)) {
                    if (file_exists($path . '/' . $filename . '/.git/HEAD')) {
                        // Found a .git repository, add it to the dirs list
                        $dirs[] = $path . '/' . $filename;
                    } else {
                        $this->scanRecursive($path . '/' . $filename, $dirs);
                    }
                }
            }
        }
    }

}

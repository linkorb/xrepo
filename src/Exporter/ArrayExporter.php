<?php

namespace XRepo\Exporter;

class ArrayExporter
{
    public function export(array $repos)
    {
        $data = [];
        foreach ($repos as $repo) {
            $data[$repo->getName()] = [
                'name' => $repo->getName(),
                'path' => $repo->getPath(),
                'head' => $repo->getHead(),
                'status' => $repo->getStatus(),
                'attributes' => $repo->getAttributes(),
            ];
        }
        return $data;
    }
}

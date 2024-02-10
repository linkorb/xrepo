<?php

namespace XRepo\Model;

class Repo
{
    private $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    private $path;
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    private $head;
    
    public function getHead()
    {
        return $this->head;
    }
    
    public function setHead($head)
    {
        $this->head = $head;
        return $this;
    }

    private $status;
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function isDirty()
    {
        if (trim($this->status)=='') {
            return false;
        }
        return true;
    }

    private $attributes = [];

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function setAttribute(string $key, ?string $value = null): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key): ?string
    {
        return $this->attributes[$key] ?? null;
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
}

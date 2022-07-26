<?php

namespace Rsa97\NicRu;

class RR_SRV extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('srv', type: 'sub')]
    private string $srv;
    #[XMLParse('priority', required: true)]
    private int $priority;
    #[XMLParse('weight', required: true)]
    private int $weight;
    #[XMLParse('port', required: true)]
    private int $port;
    #[XMLParse('target', type: 'method', method: 'parseName', required: true)]
    private string $target;

    public function __construct(string $name, int $priority, int $weight, int $port, string $target, ?int $ttl = null)
    {
        $this->type = ResourceRecordType::SRV;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->priority = $priority;
        $this->weight = $weight;
        $this->port = $port;
        $this->target = $target === '.' ? '.' : idn_to_ascii($target);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getPort(): int
    {
        return $this->port;
    }
    public function getTarget(): string
    {
        return $this->target === '.' ? '.' : idn_to_utf8($this->target);
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $mx = $rr->addChild('srv');
        $mx->addChild('priority', "{$this->priority}");
        $mx->addChild('weight', "{$this->weight}");
        $mx->addChild('port', "{$this->port}");
        $mx->addChild('target')->addChild('name', $this->target);
    }
}

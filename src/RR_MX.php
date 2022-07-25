<?php

namespace Rsa97\NicRu;

class RR_MX extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('mx', type: 'sub')]
    private string $mx;
    #[XMLParse('preference', required: true)]
    private int $priority;
    #[XMLParse('exchange', type: 'method', method: 'parseName', required: true)]
    private string $exchange;

    public function __construct(string $name, int $priority, string $exchange, ?int $ttl = null)
    {
        $this->type = ResourceRecordType::MX;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->priority = $priority;
        $this->exchange = idn_to_ascii($exchange);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getExchange(): string
    {
        return idn_to_utf8($this->exchange);
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $mx = $rr->addChild('mx');
        $mx->addChild('preference', "{$this->priority}");
        $mx->addChild('exchange')->addChild('name', $this->exchange);
    }
}

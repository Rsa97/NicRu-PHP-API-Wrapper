<?php

namespace Rsa97\NicRu;

class RR_NS extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('ns', type: 'method', method: 'parseName', required: true)]
    private string $nameServer;

    public function __construct(string $name, string $nameServer, ?int $ttl = null)
    {
        $this->type = ResourceRecordType::NS;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->nameServer = idn_to_ascii($nameServer);
    }

    public function getNameServer(): string
    {
        return idn_to_utf8($this->nameServer);
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rr->addChild('ns')->addChild('name', $this->nameServer);
    }
}

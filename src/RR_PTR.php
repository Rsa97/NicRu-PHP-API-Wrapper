<?php

namespace Rsa97\NicRu;

class RR_PTR extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('ptr', type: 'method', method: 'parseName', required: true)]
    private string $host;

    public function __construct(string $name, string $host, int $ttl = null)
    {
        $this->type = ResourceRecordType::PTR;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->host = idn_to_ascii($host);
    }

    public function getPtr(): string
    {
        return idn_to_utf8($this->ptr);
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rr->addChild('ptr')->addChild('name', $this->host);
    }
}

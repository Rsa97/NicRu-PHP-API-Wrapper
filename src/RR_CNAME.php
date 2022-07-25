<?php

namespace Rsa97\NicRu;

class RR_CNAME extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('cname', type: 'method', method: 'parseName', required: true)]
    private string $canonicalName;

    public function __construct(string $name, string $canonicalName, int $ttl = null)
    {
        $this->type = ResourceRecordType::CNAME;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->canonicalName = idn_to_ascii($canonicalName);
    }

    public function getCanonicalName(): string
    {
        return idn_to_utf8($this->canonicalName);
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rr->addChild('cname')->addChild('name', $this->canonicalName);
    }
}

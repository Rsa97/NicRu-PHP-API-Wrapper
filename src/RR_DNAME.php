<?php

namespace Rsa97\NicRu;

class RR_DNAME extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('dname', type: 'method', method: 'parseName', required: true)]
    private string $alias;

    public function __construct(string $name, string $alias, int $ttl = null)
    {
        $this->type = ResourceRecordType::DNAME;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->alias = idn_to_ascii($alias);
    }

    public function getAlias(): string
    {
        return idn_to_utf8($this->alias);
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rr->addChild('dname')->addChild('name', $this->alias);
    }
}

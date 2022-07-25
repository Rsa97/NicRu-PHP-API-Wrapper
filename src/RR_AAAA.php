<?php

namespace Rsa97\NicRu;

class RR_AAAA extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('aaaa', required: true)]
    private string $ipv6;

    public function __construct(string $name, string $ipv6, ?int $ttl = null)
    {
        $this->type = ResourceRecordType::AAAA;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->ipv6 = $ipv6;
    }

    public function getIPv6(): string
    {
        return $this->ipv6;
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rr->addChild('aaaa', $this->ipv6);
    }
}

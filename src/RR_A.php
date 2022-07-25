<?php

namespace Rsa97\NicRu;

class RR_A extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('a', required: true)]
    private string $ip;

    public function __construct(string $name, string $ip, ?int $ttl = null)
    {
        $this->type = ResourceRecordType::A;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->ip = $ip;
    }

    public function getIP(): string
    {
        return $this->ip;
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rr->addChild('a', $this->ip);
    }
}

<?php

namespace Rsa97\NicRu;

class RR_HINFO extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('hinfo', type: 'sub')]
    private string $hinfo;
    #[XMLParse('hardware', required: true)]
    private string $hardware;
    #[XMLParse('os', required: true)]
    private string $os;

    public function __construct(string $name, string $hardware, string $os, ?int $ttl = null)
    {
        $this->type = ResourceRecordType::HINFO;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->hardware = $hardware;
        $this->os = $os;
    }

    public function getHardware(): int
    {
        return $this->hardware;
    }

    public function getOS(): string
    {
        return $this->os;
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $hinfo = $rr->addChild('hinfo');
        $hinfo->addChild('hardware', $this->hardware);
        $hinfo->addChild('os', $this->os);
    }
}

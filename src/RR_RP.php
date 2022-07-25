<?php

namespace Rsa97\NicRu;

class RR_RP extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('rp', type: 'sub')]
    private string $rp;
    #[XMLParse('mbox-dname', type: 'method', method: 'parseName', required: true)]
    private string $mailName;
    #[XMLParse('txt-dname', type: 'method', method: 'parseName', required: true)]
    private string $txtName;

    public function __construct(string $name, string $mailName, string $txtName, int $ttl = null)
    {
        $this->type = ResourceRecordType::RP;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->mailName = idn_to_ascii($mailName);
        $this->txtName = idn_to_ascii($txtName);
    }

    public function getMailName(): string
    {
        return idn_to_utf8($this->mailName);
    }
    
    public function getTxtName(): string
    {
        return idn_to_utf8($this->txtName);
    }

    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $rp = $rr->addChild('rp');
        $rp->addChild('mbox-dname')->addChild('name', $this->mailName);
        $rp->addChild('txt-dname')->addChild('name', $this->txtName);
    }
}
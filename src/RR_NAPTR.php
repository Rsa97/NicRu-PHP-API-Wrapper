<?php

namespace Rsa97\NicRu;

class RR_NAPTR extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('naptr', type: 'sub')]
    private string $naptr;
    #[XMLParse('order', required: true)]
    private int $order;
    #[XMLParse('preference', required: true)]
    private int $preference;
    #[XMLParse('flags', required: true)]
    private string $flags;
    #[XMLParse('service', required: true)]
    private string $service;
    #[XMLParse('regexp', required: true)]
    private string $regexp;
    #[XMLParse('replacement', type: 'method', method: 'parseName', required: true)]
    private string $replacement;

    public function __construct(string $name, int $order, int $preference, string $flags, string $service, string $regexp, string $replacement, int $ttl = null)
    {
        $this->type = ResourceRecordType::NAPTR;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->order = $order;
        $this->preference = $preference;
        $this->flags = $flags;
        $this->service = $service;
        $this->regexp = $regexp;
        $this->replacement = $replacement === '.' ? '.' : idn_to_ascii($replacement);
    }

    public function getOrder(): int
    {
        return $this->order;
    }
    
    public function getPreference(): int
    {
        return $this->preference;
    }

    public function getflags(): string
    {
        return $this->flags;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getRegexp(): string
    {
        return $this->regexp;
    }

    public function getReplacement(): string
    {
        return $this->replacement === '.' ? '.' : idn_to_utf8($this->rteplacement);
    }

    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $naptr = $rr->addChild('naptr');
        $naptr->addChild('order', "{$this->order}");
        $naptr->addChild('preference', "{$this->preference}");
        $naptr->addChild('flags', $this->flags);
        $naptr->addChild('service', $this->service);
        $naptr->addChild('regexp', $this->regexp);
        $naptr->addChild('replacement')->addChild('name', $this->replacement);
    }
}
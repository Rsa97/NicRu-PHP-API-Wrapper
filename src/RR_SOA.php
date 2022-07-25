<?php

namespace Rsa97\NicRu;

class RR_SOA extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('soa', type: 'sub')]
    private string $soa;
    #[XMLParse('mname', type: 'method', method: 'parseName', required: true)]
    private string $nsName;
    #[XMLParse('rname', type: 'method', method: 'parseName', required: true)]
    private string $mailName;
    #[XMLParse('serial', required: true)]
    private int $serial;
    #[XMLParse('refresh', required: true)]
    private int $refresh;
    #[XMLParse('retry', required: true)]
    private int $retry;
    #[XMLParse('expire', required: true)]
    private int $expire;
    #[XMLParse('minimum', required: true)]
    private int $minimum;

    public function __construct(string $name, string $nsName, string $mailName, int $serial, int $refresh, int $retry, int $expire, int $minimum, int $ttl = null)
    {
        $this->type = ResourceRecordType::SOA;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->nsName = idn_to_ascii($nsName);
        $this->mailName = idn_to_ascii($mailName);
        $this->serial = $serial;
        $this->refresh = $refresh;
        $this->retry = $retry;
        $this->expire = $expire;
        $this->minimum = $minimum;
    }

    public function getNSName(): string
    {
        return idn_to_utf8($this->mName);
    }
    
    public function getMailName(): string
    {
        return idn_to_utf8($this->rName);
    }

    public function getSerial(): int
    {
        return $this->serial;
    }

    public function getRefresh(): int
    {
        return $this->refresh;
    }

    public function getRetry(): int
    {
        return $this->retry;
    }

    public function getExpire(): int
    {
        return $this->expire;
    }

    public function getMinimum(): int
    {
        return $this->minimum;
    }

    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $soa = $rr->addChild('soa');
        $soa->addChild('mname')->addChild('name', $this->nsName);
        $soa->addChild('rname')->addChild('name', $this->mailName);
        $soa->addChild('serial', "{$this->serial}");
        $soa->addChild('refresh', "{$this->refresh}");
        $soa->addChild('retry', "{$this->retry}");
        $soa->addChild('expire', "{$this->expire}");
        $soa->addChild('minimum', "{$this->minimum}");
    }
}
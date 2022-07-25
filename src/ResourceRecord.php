<?php

namespace Rsa97\NicRu;

class ResourceRecord
{
    use XMLParse {
        from as protected fromXML;
    }

    #[XMLParse('@id')]
    protected int $id;
    #[XMLParse('name', required: true)]
    protected string $name;
    #[XMLParse('ttl', required: true)]
    protected ?int $ttl;
    #[XMLParse('type', type: 'method', method: 'parseType')]
    protected ResourceRecordType $type;

    protected static function parseType(string $type): ResourceRecordType
    {
        return ResourceRecordType::from($type);
    }

    protected static function parseName(\SimpleXMLElement $name): string
    {
        return "{$name->name}";
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function  getName(): string
    {
        return idn_to_utf8($this->name);
    }

    public function getTTL(): int
    {
        return $this->ttl;
    }

    public function getType(): ResourceRecordType
    {
        return $this->type;
    }

    public static function from(\SimpleXMLElement $source): mixed
    {
        switch (ResourceRecordType::from($source->type)) {
            case ResourceRecordType::SOA:
                return RR_SOA::fromXML($source);
            case ResourceRecordType::A:
                return RR_A::fromXML($source);
            case ResourceRecordType::AAAA:
                return RR_AAAA::fromXML($source);
            case ResourceRecordType::CNAME:
                return RR_CNAME::fromXML($source);
            case ResourceRecordType::NS:
                return RR_NS::fromXML($source);
            case ResourceRecordType::MX:
                return RR_MX::fromXML($source);
            case ResourceRecordType::SRV:
                return RR_SRV::fromXML($source);
            case ResourceRecordType::PTR:
                return RR_PTR::fromXML($source);
            case ResourceRecordType::TXT:
                return RR_TXT::fromXML($source);
            case ResourceRecordType::DNAME:
                return RR_DNAME::fromXML($source);
            case ResourceRecordType::HINFO:
                return RR_HINFO::fromXML($source);
            case ResourceRecordType::NAPTR:
                return RR_NAPTR::fromXML($source);
            case ResourceRecordType::RP:
                return RR_RP::fromXML($source);
            default:
                return self::fromXML($source);
        }
    }
}

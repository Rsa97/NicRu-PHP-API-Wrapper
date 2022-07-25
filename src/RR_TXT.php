<?php

namespace Rsa97\NicRu;

class RR_TXT extends ResourceRecord
{
    use XMLParse;

    #[XMLParse('txt', type: 'method', method: 'parseStrings', required: true)]
    private array $strings;

    private static function parseStrings(\SimpleXMLElement $source): array
    {
        $strings = [];
        foreach ($source->string as $string) {
            $strings[] = $string;
        }
        return $strings;
    }

    public function __construct(string $name, array $strings, int $ttl = null)
    {
        $this->type = ResourceRecordType::TXT;
        $this->name = idn_to_ascii($name);
        $this->ttl = $ttl;
        $this->strings = $strings;
    }

    public function getStrings(): array
    {
        return $this->strings;
    }
    
    public function toXML(\SimpleXMLElement $xml): void
    {
        $rr = $xml->addChild('rr');
        $rr->addChild('name', $this->name);
        if ($this->ttl !== null) {
            $rr->addChild('ttl', "{$this->ttl}");
        }
        $rr->addChild('type', $this->type->value);
        $txt = $rr->addChild('txt');
        foreach($this->strings as $string) {
            $txt->addChild('string', $string);
        }
    }
}

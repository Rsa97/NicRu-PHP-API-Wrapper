<?php

namespace Rsa97\NicRu;

class Zone
{
    use XMLParse {
        from as private fromXML;
    }

    #[XMLParse('@admin')]
    private string $contract;
    #[XMLParse('@id')]
    private int $id;
    #[XMLParse('@enable')]
    private bool $enable;
    #[XMLParse('@name', type: 'method', method: 'parseName')]
    private string $name;
    #[XMLParse('@service')]
    private string $serviceName;
    #[XMLParse('@has-changes')]
    private bool $hasChanges;
    #[XMLParse('@has-primary', type: 'method', method: 'parseServiceType')]
    private ServiceType $serviceType;
    private client $client;

    private static array $zones;

    private static function parseServiceType(string $val): ServiceType
    {
        return $val === 'true'
            ? ServiceType::DNS_MASTER
            : ServiceType::SECONDARY;
    }

    private static function parseName(string $name): string
    {
        return idn_to_ascii($name);
    }

    private function updateInfo(): void
    {
        $this->client->getServices();
        $this->client->getZones($this->serviceName);
    }

    public static function from(\SimpleXMLElement $zoneXML, Client $client) {
        $name = "{$zoneXML['service']}:{$zoneXML['name']}";
        if (!isset(static::$zones[$name])) {
            static::$zones[$name] = new static();
        }
        static::$zones[$name]->fill($zoneXML);
        static::$zones[$name]->client = $client;
        return static::$zones[$name];
    }

    public function getAdmin(): string
    {
        return $this->admin;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enable;
    }

    public function getName(): string
    {
        return idn_to_utf8($this->name);
    }

    public function getServiceName(): string
    {
        return $this->service;
    }

    public function hasChanges(): bool
    {
        return $this->hasChanges;
    }

    public function getServiceType(): ServiceType
    {
        return $this->serviceType;
    }

    public function move(string $newServiceName): void
    {
        $this->client->getProtocol()->moveZone($this->serviceName, $this->name, $newServiceName);
        $this->client->getServices();
        $this->serviceName = $newServiceName;
        $name = "{$this->serviceName}:{$this->zoneName}";
        unset(static::$zones[$name]);
        $this->updateInfo();
    }

    public function delete(): void
    {
        $this->client->getProtocol()->deleteZone($this->serviceName, $this->name);
        $this->client->getService($this->serviceName);
        $name = "{$this->serviceName}:{$this->zoneName}";
        unset(static::$zones[$name]);
    }

    public function getXferAllowList(): array
    {
        $result = $this->client->getProtocol()->getZoneXferAllowList($this->serviceName, idn_to_ascii($this->name));
        $list = [];
        foreach ($result->data->address as $address) {
            $list[] = "{$address}";
        }
        return $list;
    }

    public function setXferAllowList(array $addressList): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><request></request>');
        foreach ($addressList as $address) {
            $xml->addChild('address', $address);
        }
        $this->client->getProtocol()->setZoneXferAllowList($this->serviceName, $this->name, $xml);
        $this->updateInfo();
    }

    public function getFile(?int $revision = null): string
    {
        return $this->client->getProtocol()->getZoneFile($this->serviceName, $this->name, $revision);
    }

    public function setFile(string $zoneFile): void
    {
        $this->client->getProtocol()->setZoneFile($this->serviceName, $this->name, $zoneFile);
        $this->updateInfo();
    }

    public function rollback(): void
    {
        $this->client->getProtocol()->rollbackZone($this->serviceName, $this->name);
        $this->updateInfo();
    }

    public function commit(): void
    {
        $this->client->getProtocol()->commitZone($this->serviceName, $this->name);
        $this->updateInfo();
    }

    public function getRevisions(): array
    {
        $result = $this->client->getProtocol()->getZoneRevisions($this->serviceName, $this->name);
        $revisions = [];
        foreach ($result->data->revision as $revision) {
            $revisions[] = RevisionInfo::from($revision);
        }
        return $revisions;
    }

    public function setRevision(int $revision): void
    {
        $this->client->getProtocol()->setZoneRevision($this->serviceName, $this->name, $revision);
        $this->updateInfo();
    }

    public function getTTL(): int
    {
        $result = $this->client->getProtocol()->getZoneTTL($this->serviceName, $this->name);
        return intval("{$result->data->{'default-ttl'}}");
    }

    public function setTTL(int $ttl): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><request></request>');
        $xml->addChild('default-ttl', "{$ttl}");
        $this->client->getProtocol()->setZoneTTL($this->serviceName, $this->name, $xml);
        $this->updateInfo();
    }

    public function getResourceRecords(?ResourceRecordType $type = null, ?string $name = null): array
    {
        $result = $this->client->getProtocol()->getZoneResourceRecords($this->serviceName, $this->name);
        $records = [];
        if ($name !== null) {
            $idnName = idn_to_ascii($name);
        }
        foreach ($result->data->zone->rr as $rr) {
            if (
                ($type === null || $type->value === "{$rr->type}") &&
                ($name === null || $idnName === idn_to_ascii("{$rr->name}")) 
            ) {
                $records[] = ResourceRecord::from($rr);
            }
        }
        return $records;
    }

    public function addResourceRecords(array $resourceRecords): array
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><request></request>');
        $list = $xml->addChild('rr-list');
        foreach ($resourceRecords as $rr) {
            $rr->toXML($list);
        }
        $result = $this->client->getProtocol()->addZoneResourceRecords($this->serviceName, $this->name, $xml);
        $records = [];
        foreach ($result->data->zone->rr as $rr) {
            $records[] = ResourceRecord::from($rr);
        }
        $this->updateInfo();
        return $records;
    }

    public function deleteResourceRecord(int $resourceRecordId): void
    {
        $this->client->getProtocol()->deleteZoneResourceRecord($this->serviceName, $this->name, $resourceRecordId);
    }

    public function deleteResourceRecords(?ResourceRecordType $type, string $name): void
    {
        foreach ($this->getResourceRecords($type, $name) as $rr) {
            $this->deleteResourceRecord($rr->getId());
        }
    }

    public function getMasters(): array
    {
        $result = $this->client->getProtocol()->getZoneMasters($this->serviceName, $this->name);
        $list = [];
        if ($result->data->address) {
            foreach($result->data->address as $address) {
                $list[] = "{$address}";
            }
        }
        return $list;
    }

    public function setMasters(array $masters): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><request></request>');
        foreach ($masters as $master) {
            $xml->addChild('address', $master);
        }
        $this->client->getProtocol()->setZoneMasters($this->serviceName, $this->name, $xml);
    }
}

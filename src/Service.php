<?php

namespace Rsa97\NicRu;

class Service
{
    use XMLParse {
        from as protected fromXML;
    }

    #[XMLParse('@admin')]
    private string $admin;
    #[XMLParse('@name')]
    private string $name;
    #[XMLParse('@payer')]
    private string $payer;
    #[XMLParse('@enable')]
    private bool $enable;
    #[XMLParse('@domains-limit')]
    private int $domainsLimit;
    #[XMLParse('@domains-num')]
    private int $domainsNum;
    #[XMLParse('@rr-limit')]
    private int $rrLimit;
    #[XMLParse('@rr-num')]
    private int $rrNum;
    #[XMLParse('@tariff')]
    private string $tariff;
    #[XMLParse('@has-primary', type: 'method', method: 'parseServiceType')]
    private ServiceType $type;
    private Client $client;

    private static array $services;

    private static function parseServiceType(string $val): ServiceType
    {
        return $val === 'true'
            ? ServiceType::DNS_MASTER
            : ServiceType::SECONDARY;
    }

    private function updateInfo(): void
    {
        $this->client->getServices();
    }

    public static function from(\SimpleXMLElement $serviceXML, Client $client) {
        $name = "{$serviceXML['name']}";
        if (!isset(static::$services[$name])) {
            static::$services[$name] = new static();
        }
        static::$services[$name]->fill($serviceXML);
        static::$services[$name]->client = $client;
        return static::$services[$name];
    }

    public function getAdmin(): string
    {
        return $this->admin;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPayer(): string
    {
        return $this->payer;
    }

    public function isEnabled(): bool
    {
        return $this->enable;
    }

    public function getDomainsLimit(): int
    {
        return $this->domainsLimit;
    }

    public function getDomainsNumber(): int
    {
        return $this->domainsNum;
    }

    public function getRrLimit(): int
    {
        return $this->rrLimit;
    }

    public function getRrNumber(): int
    {
        return $this->rrNum;
    }

    public function getTariff(): string
    {
        return $this->tariff;
    }

    public function getType(): ServiceType
    {
        return $this->type;
    }

    public function getZones(): array
    {
        $result = $this->client->getProtocol()->getZones($this->name);
        $zones = [];
        foreach ($result->data->zone as $zone) {
            $zones[] = Zone::from($zone, $this->client);
        }
        return $zones;
    }

    public function getZone(string $zoneName): Zone
    {
        $list = $this->getZones();
        $name = idn_to_ascii($zoneName);
        $zone = array_values(array_filter(
            $list,
            fn($z) => $z->getName() === $name
        ));
        if (count($zone) === 0) {
            throw new \Exception('Invalid zone name');
        }
        return $zone[0];
    }

    public function createZone(string $zoneName): Zone
    {
        $result = $this->client->getProtocol()->createZone($this->name, $zoneName);
        $this->updateInfo();
        return Zone::from($result->data->zone, $this->client);
    }

    public function moveZone(string $zoneName, string $newServiceName): void
    {
        $this->getZone($zoneName)->move($newServiceName);
    }

    public function deleteZone($zoneName): void
    {
        $this->getZone($zoneName)->delete();
    }

    public function getZoneXferAllowList(string $zoneName): array
    {
        return $this->getZone($zoneName)->getXferAllowList();
    }

    public function setZoneXferAllowList(string $zoneName, array $addressList): void
    {
        $this->getZone($zoneName)->setXferAllowList($addressList);
    }

    public function getZoneFile(string $zoneName, ?int $revision = null): string
    {
        return $this->getZone($zoneName)->getFile($revision);
    }

    public function setZoneFile(string $zoneName, string $zoneFile): void
    {
        $this->getZone($zoneName)->setFile($zoneFile);
    }

    public function rollbackZone(string $zoneName): void
    {
        $this->getZone($zoneName)->rollback();
    }

    public function commitZone(string $zoneName): void
    {
        $this->getZone($zoneName)->commit();
    }

    public function getZoneRevisions(string $zoneName): array
    {
        return $this->getZone($zoneName)->getRevisions();
    }

    public function setZoneRevision(string $zoneName, int $revision): void
    {
        $this->getZone($zoneName)->setRevision($revision);
    }

    public function getZoneTTL(string $zoneName): int
    {
        return $this->getZone($zoneName)->getTTL();
    }

    public function setZoneTTL(string $zoneName, int $ttl): void
    {
        $this->getZone($zoneName)->setTTL($ttl);
    }

    public function getZoneResourceRecords(string $zoneName): array
    {
        return $this->getZone($zoneName)->getResourceRecords();
    }

    public function addZoneResourceRecords(string $zoneName, array $resourceRecords): array
    {
        return $this->getZone($zoneName)->addResourceRecords($resourceRecords);
    }

    public function deleteZoneResourceRecord(string $zoneName, int $resourceRecordId): void
    {
        $this->getZone($zoneName)->deleteResourceRecord($resourceRecordId);
    }

    public function getZoneMasters(string $zoneName): array
    {
        return $this->getZone($zoneName)->getMasters();
    }

    public function setZoneMasters(string $zoneName, array $masters): void
    {
        $this->getZone($zoneName)->setMasters($masters);
    }
}
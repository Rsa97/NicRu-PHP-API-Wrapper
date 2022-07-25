<?php

namespace Rsa97\NicRu;

use SimpleXMLElement;

class Client
{
    private Protocol $protocol;

    public function __construct(string $nicLogin, string $nicPassword, string $apiLogin, string $apiPassword, array $scopes = [])
    {
        assert(
            array_reduce(
                $scopes,
                fn($acc, $cur) => $acc && $cur instanceof Scope,
                true
            ),
            new \Exception('Incorrect scope, must be instance of Scope')
        );
        if (count($scopes) === 0) {
            $scopes = [
                new Scope()
            ];
        }
        $textScopes = implode(
            ' ',
            array_map(
                fn($s) => $s->asString(),
                $scopes
            )
        );
        $this->protocol = new Protocol();
        $this->protocol->login($nicLogin, $nicPassword, $apiLogin, $apiPassword, $textScopes);
    }

    public function getProtocol(): protocol
    {
        return $this->protocol;
    }

    public function getServices(): array
    {
        $result = $this->protocol->getServices();
        $services = [];
        foreach ($result->data->service as $service) {
            $services[] = Service::from($service, $this);
        }
        return $services;
    }

    public function getService(string $serviceName): Service
    {
        $list = $this->getServices();
        $service = array_values(array_filter(
            $list,
            fn($s) => $s->getName() === $serviceName
        ));
        if (count($service) === 0) {
            throw new \Exception('Invalid zone name');
        }
        return $service[0];
    }

    public function getZones(): array
    {
        $result = $this->protocol->getZones(null);
        $zones = [];
        foreach ($result->data->zone as $zone) {
            $zones[] = Zone::from($zone, $this);
        }
        return $zones;
    }

    public function getZone(string $zoneName): array
    {
        $list = $this->getZones();
        $name = idn_to_ascii($zoneName);
        return array_values(array_filter(
            $list,
            fn($z) => $z->getName() === $name
        ));
    }

    public function createZone(ServiceType $serviceType, string $zoneName): Zone
    {
        $result = $this->protocol->createZone($serviceType, idn_to_ascii($zoneName));
        $zone = Zone::from($result->data->zone, $this);
        $this->getService($zone->getServiceName());
        return $zone;
    }

    public function moveZone(string $oldServiceName, string $zoneName, string $newServiceName): void
    {
        $this->getService($oldServiceName)->moveZone($zoneName, $newServiceName);
    }

    public function deleteZone(string $serviceName, string $zoneName): void
    {
        $this->getService($serviceName)->deleteZone($zoneName);
    }

    public function getZoneXferAllowList(string $serviceName, string $zoneName): array
    {
        return $this->getService($serviceName)->getZoneXferAllowList($zoneName);
    }

    public function setZoneXferAllowList(string $serviceName, string $zoneName, array $addressList): void
    {
        $this->getService($serviceName)->setZoneXferAllowList($zoneName, $addressList);
    }

    public function getZoneFile(string $serviceName, string $zoneName, ?int $revision = null): string
    {
        return $this->getService($serviceName)->getZoneFile($zoneName, $revision);
    }

    public function setZoneFile(string $serviceName, string $zoneName, string $zoneFile): void
    {
        $this->getService($serviceName)->setZoneFile($zoneName, $zoneFile);
    }

    public function rollbackZone(string $serviceName, string $zoneName): void
    {
        $this->getService($serviceName)->rollbackZone($zoneName);
    }

    public function commitZone(string $serviceName, string $zoneName): void
    {
        $this->getService($serviceName)->commitZone($zoneName);
    }

    public function getZoneRevisions(string $serviceName, string $zoneName): array
    {
        return $this->getService($serviceName)->getZoneRevisions($zoneName);
    }
    
    public function setZoneRevision(string $serviceName, string $zoneName, int $revision): void
    {
        $this->getService($serviceName)->setZoneRevision($zoneName, $revision);
    }

    public function getZoneTTL(string $serviceName, string $zoneName): int
    {
        return $this->getService($serviceName)->getZoneTTL($zoneName);
    }

    public function setZoneTTL(string $serviceName, string $zoneName, int $ttl): void
    {
        $this->getService($serviceName)->setZoneTTL($zoneName, $ttl);
    }

    public function getZoneResourceRecords(string $serviceName, string $zoneName): array
    {
        return $this->getService($serviceName)->getZoneResourceRecords($zoneName);
    }

    public function addZoneResourceRecords(string $serviceName, string $zoneName, array $resourceRecords): array
    {
        return $this->getService($serviceName)->addZoneResourceRecords($zoneName, $resourceRecords);
    }

    public function deleteResourceRecords(string $serviceName, string $zoneName, int $resourceRecordId): void
    {
        $this->getService($serviceName)->deleteZoneResourceRecord($zoneName, $resourceRecordId);
    }

    public function getZoneMasters(string $serviceName, string $zoneName): array
    {
        return $this->getService($serviceName)->getZoneMasters($zoneName);
    }

    public function setZoneMasters(string $serviceName, string $zoneName, array $masters): void
    {
        $this->getService($serviceName)->setZoneMasters($zoneName, $masters);
    }
}

<?php

namespace Rsa97\NicRu;

use \Rsa97\NicRu\Exceptions\CurlErrorException;
use \Rsa97\NicRu\Exceptions\APIErrorException;

class Protocol
{
    private const URL = 'https://api.nic.ru';
    private string $token;

    private function request(ScopeMethod $method, string $url, array|string $data = [], array $headers = []): string
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl, [
                CURLOPT_URL => static::URL . $url,
                CURLOPT_CUSTOMREQUEST => $method->name,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
            ]
        );
        switch ($method) {
            case ScopeMethod::GET:
                break;
            case ScopeMethod::POST:
            case ScopeMethod::PUT:
            case ScopeMethod::DELETE:
                if (is_array($data)) {
                    $body = implode(
                        '&',
                        array_map(
                            fn($k, $v) => urlencode($k) . '=' . urlencode($v),
                            array_keys($data),
                            array_values($data)
                        )
                    );
                    $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                } else {
                    $body = $data;
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
                $headers['Content-Length'] = strlen($body);
                break;

        }
        if (isset($this->token)) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }
        if (count($headers) !== 0) {
            $hdrs = array_map(
                fn($k, $v) => "{$k}: {$v}",
                array_keys($headers),
                array_values($headers)
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $hdrs);
        }
        $result = curl_exec($curl);
        if ($result === false) {
            throw new CurlErrorException(curl_error($curl), curl_errno($curl));
        }
        curl_close($curl);
        return $result;
    }

    private function requestJSON(ScopeMethod $method, string $url, array|string $data = [], array $headers = []): object
    {
        $result = $this->request($method, $url, $data, $headers);
        $json = json_decode($result);
        if ($json === null) {
            throw new APIErrorException('Empty answer from API');
        }
        return $json;
    }

    private function requestXML(ScopeMethod $method, string $url, array|string $data = [], array $headers = []): \SimpleXMLElement
    {
        $result = $this->request($method, $url, $data, $headers);
        $xml = simplexml_load_string($result, options: LIBXML_NOERROR | LIBXML_NOWARNING);
        if ($xml === false) {
            throw new APIErrorException('Invalid XML answer from API');
        }
        if ("{$xml->status}" === 'fail') {
            $errs = [];
            foreach ($xml->errors->error as $error) {
                $errs[] = "{$error['code']}: {$error}";
            }
            $err = implode(', ', $errs);
            throw new APIErrorException($err);
        }
        return $xml;
    }

    public function login(string $nicLogin, string $nicPassword, string $apiLogin, string $apiPassword, string $scopes)
    {
        unset($this->token);
        $result = $this->requestJSON(
            ScopeMethod::POST,
            '/oauth/token',
            data: [
                'grant_type' => 'password',
                'username' => $nicLogin,
                'password' => $nicPassword,
                'scope' => $scopes
            ],
            headers: [
                'Authorization' => 'Basic ' . base64_encode("{$apiLogin}:{$apiPassword}")
            ]
        );
        if (isset($result->error)) {
            throw new APIErrorException($result->error);
        }
        if (!isset($result->access_token)) {
            throw new APIErrorException('No token in authentication answer from API');
        }
        $this->token = $result->access_token;
    }

    public function getServices(): \SimpleXMLElement
    {
        return $this->requestXML(ScopeMethod::GET, '/dns-master/services');
    }

    public function getZones(?string $serviceName): \SimpleXMLElement
    {
        $url = '/dns-master';
        if ($serviceName !== null) {
            $url .= "/services/{$serviceName}";
        }
        $url .= '/zones';
        return $this->requestXML(ScopeMethod::GET, $url);
    }

    public function createZone(string|ServiceType $serviceName, string $zoneName): \SimpleXMLElement
    {
        $url = '/dns-master';
        if (is_string($serviceName)) {
            $url .= "/services/{$serviceName}/zones/{$zoneName}";
        } else {
            $url .= "/zones/{$serviceName->value}/{$zoneName}";
        }
        return $this->requestXML(ScopeMethod::PUT, $url);
    }

    public function moveZone(string $oldServiceName, string $zoneName, string $newServiceName): void
    {
        $url = "/dns-master/services/{$oldServiceName}/zones/{$zoneName}/move/{$newServiceName}";
        $this->request(ScopeMethod::POST, $url);
    }

    public function deleteZone(string $serviceName, string $zoneName): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}";
        $this->request(ScopeMethod::DELETE, $url);
    }

    public function getZoneXferAllowList(string $serviceName, string $zoneName): \SimpleXMLElement
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/xfer";
        return $this->requestXML(ScopeMethod::GET, $url);
    }

    public function setZoneXferAllowList(string $serviceName, string $zoneName, \SimpleXMLElement $addressList): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/xfer";
        $this->requestXML(ScopeMethod::POST, $url, data: $addressList->asXML());
    }

    public function getZoneFile(string $serviceName, string $zoneName, ?int $revision = null): string
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}";
        if ($revision !== null) {
            $url .= "/revisions/{$revision}";
        }
        $result = $this->request(ScopeMethod::GET, $url);
        $xml = simplexml_load_string($result, options: LIBXML_NOERROR | LIBXML_NOWARNING);
        if ($xml === false) {
            return $result;
        }
        if ("{$xml->status}" === 'fail') {
            $errs = [];
            foreach ($xml->errors->error as $error) {
                $errs[] = "{$error['code']}: {$error}";
            }
            $err = implode(', ', $errs);
            throw new APIErrorException($err);
        }
        return $xml;
    }

    public function setZoneFile(string $serviceName, string $zoneName, string $zoneFile): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}";
        $this->requestXML(ScopeMethod::POST, $url, data: $zoneFile);
    }

    public function rollbackZone(string $serviceName, string $zoneName): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/rollback";
        $this->requestXML(ScopeMethod::POST, $url);
    }

    public function commitZone(string $serviceName, string $zoneName): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/commit";
        $this->requestXML(ScopeMethod::POST, $url);
    }

    public function getZoneRevisions(string $serviceName, string $zoneName): \SimpleXMLElement
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/revisions";
        return $this->requestXML(ScopeMethod::GET, $url);
    }

    public function setZoneRevision(string $serviceName, string $zoneName, int $revision): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/revisions/{$revision}";
        $this->requestXML(ScopeMethod::PUT, $url);
    }

    public function getZoneTTL(string $serviceName, string $zoneName): \SimpleXMLElement
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/default-ttl";
        return $this->requestXML(ScopeMethod::GET, $url);
    }

    public function setZoneTTL(string $serviceName, string $zoneName, \SimpleXMLElement $ttlXML): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/default-ttl";
        $this->requestXML(ScopeMethod::POST, $url, data: $ttlXML->asXML());
    }

    public function getZoneResourceRecords(string $serviceName, string $zoneName): \SimpleXMLElement
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/records";
        return $this->requestXML(ScopeMethod::GET, $url);
    }

    public function addZoneResourceRecords(
        string $serviceName,
        string $zoneName,
        \SimpleXMLElement $resourceRecordsXML
    ): \SimpleXMLElement {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/records";
        return $this->requestXML(ScopeMethod::PUT, $url, $resourceRecordsXML->asXML());
    }

    public function deleteZoneResourceRecord(string $serviceName, string $zoneName, int $resourceRecordId): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/records/{$resourceRecordId}";
        $this->requestXML(ScopeMethod::DELETE, $url);
    }

    public function getZoneMasters(string $serviceName, string $zoneName): \SimpleXMLElement
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/masters";
        return $this->requestXML(ScopeMethod::GET, $url);
    }

    public function setZoneMasters(string $serviceName, string $zoneName, \SimpleXMLElement $mastersXML): void
    {
        $url = "/dns-master/services/{$serviceName}/zones/{$zoneName}/masters";
        $this->requestXML(ScopeMethod::POST, $url, $mastersXML);
    }
}

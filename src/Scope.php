<?php

namespace Rsa97\NicRu;

class Scope
{
    public readonly ?array $methods;
    public readonly ?string $service;
    public readonly ?string $zone;
    private string $scope;

    public function __construct(?array $methods = [], ?string $service = null, ?string $zone = null)
    {
        assert(
            array_reduce(
                $methods,
                fn($acc, $cur) => $acc && ($cur instanceof ScopeMethod),
                true
            ),
            new \Exception('Incorrect scope method, must be case of ScopeMethod')
        );
        $methods[] = ScopeMethod::GET;
        $this->methods = array_unique($methods, SORT_REGULAR);
        $this->service = $service;
        $this->zone = $zone;
    }

    public function asString(): string
    {
        if (!isset($this->scope)) {
            $methods = implode(
                '|',
                array_map(
                    fn($m) => $m->name,
                    $this->methods
                )
            );
            if ($methods === '') {
                $methods = '.+';
            }
            if (count($this->methods) > 1) {
                $methods = "({$methods})";
            }
            $url = '';
            if ($this->service !== null) {
                $url .= "/services/{$this->service}";
            }
            if ($this->zone !== null) {
                $url .= "/zones/{$this->zone}/(/.+)?";
            } else {
                $url .= '/.+';
            }
            $this->scope = "{$methods}:/dns-master{$url}";
        }
        return $this->scope;
    }
}

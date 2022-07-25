<?php

namespace Rsa97\NicRu;

class RevisionInfo
{
    use XMLParse;

    #[XMLParse('@date', type: 'datetime')]
    private \DateTimeImmutable $date;
    #[XMLParse('@ip')]
    private string $ip;
    #[XMLParse('@number')]
    private int $number;

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}

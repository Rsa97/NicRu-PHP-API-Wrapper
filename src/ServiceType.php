<?php

namespace Rsa97\NicRu;

enum ServiceType: string
{
    case DNS_MASTER = 'primary';
    case SECONDARY = 'secondary';
}

<?php

namespace Rsa97\NicRu;

enum ResourceRecordType: string
{
    case SOA = 'SOA';
    case A = 'A';
    case AAAA = 'AAAA';
    case CNAME = 'CNAME';
    case NS = 'NS';
    case MX = 'MX';
    case SRV = 'SRV';
    case PTR = 'PTR';
    case TXT = 'TXT';
    case DNAME = 'DNAME';
    case HINFO = 'HINFO';
    case NAPTR = 'NAPTR';
    case RP = 'RP';
}

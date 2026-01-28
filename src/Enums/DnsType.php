<?php

namespace Space\Cloudflare\Enums;

enum DnsType : string
{

    case A = 'A';
    case CNAME = 'CNAME';
    case TXT = 'TXT';
    case MX = 'MX';
    case NS = 'NS';
    case SRV = 'SRV';
    case LOC = 'LOC';
    case SPF = 'SPF';
    case CERT = 'CERT';
    case DNSKEY = 'DNSKEY';
    case DS = 'DS';
    case URI = 'URI';
    case SSHFP = 'SSHFP';
    case TLSA = 'TLSA';
    case SVCB = 'SVCB';
    case CAAM = 'CAAM';
    case NAPTR = 'NAPTR';
    case SVCB_ALIAS = 'SVCB_ALIAS';
    case CAA = 'CAA';
    case REDIRECT = 'REDIRECT';
    case PROXY = 'PROXY';

}

<?php

namespace App\Enums\Agreement;

enum AuditRights: string
{
    case FULL_AUDIT_RIGHTS = 'full_audit_rights';
    case THIRD_PARTY_AUDIT_ONLY = 'third_party_audit_only';
    case SOC_2_ISO_REPORTS_ONLY = 'soc_2_iso_reports_only';
    case NONE = 'none';
    case LIMITED = 'limited';
}

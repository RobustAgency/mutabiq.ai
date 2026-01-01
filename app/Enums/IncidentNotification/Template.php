<?php

namespace App\Enums\IncidentNotification;

enum Template: string
{
    case DPA_BREACH_NOTIFICATION = 'dpa_breach_notification';
    case DATA_SUBJECT_NOTIFICATION = 'data_subject_notification (GDPR Art. 34)';
    case UAE_PDPL_BREACH_NOTIFICATION = 'uae_pdpl_breach_notification';
    case EXECUTIVE_SUMMARY_TEMPLATE = 'executive_summary_template';
    case CUSTOMER_NOTICE_TEMPLATE = 'customer_notice_template';
    case PRESS_RELEASE_TEMPLATE = 'press_release_template';
    case INTERNAL_ALL_HANDS_TEMPLATE = 'internal_all_hands_template';
    case CUSTOM_OTHER = 'custom_other';
}

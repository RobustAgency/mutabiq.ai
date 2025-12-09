<?php

namespace App\Enums\RecordOfProcessingActivity;

enum ControllerRole: string
{
    case CONTROLLER = 'controller';
    case PROCESSOR = 'processor';
    case JOINT_CONTROLLER = 'joint_controller';
}

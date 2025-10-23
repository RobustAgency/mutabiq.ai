<?php

namespace App\Enums\Dataset;

enum ControllerRole: string
{
    case CONTROLLER = 'Controller';
    case JOINT_CONTROLLER = 'Joint Controller';
    case PROCESSOR = 'Processor';
}

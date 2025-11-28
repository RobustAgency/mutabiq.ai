<?php

namespace App\Enums\UseCase;

enum HumanOversight: string
{
    case HUMAN_IN_THE_LOOP = 'human_in_the_loop';
    case HUMAN_ON_THE_LOOP = 'human_on_the_loop';
    case HUMAN_IN_COMMAND = 'human_in_command';
}

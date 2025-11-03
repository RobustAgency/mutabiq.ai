<?php

namespace App\Enums;

enum ArtifactType: string
{
    case MODEL_BINARY = 'model_binary';
    case TOKENIZER = 'tokenizer';
    case PROMPT_PACK = 'prompt_pack';
    case INDEX = 'index';
    case FEATURE_STORE_EXPORT = 'feature_store_export';
    case CONFIG = 'config';
    case DOCKER_IMAGE = 'docker_image';
    case SBOM = 'sbom';
}

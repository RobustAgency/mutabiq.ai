<?php

namespace App\Enums;

enum ArtifactType: string
{
    case MODEL_WEIGHTS = 'model_weights';
    case SERIALIZED_MODEL = 'serialized_model';
    case ONNX_MODEL = 'onnx_model';
    case TRAINING_SCRIPT = 'training_script';
    case INFERENCE_SCRIPT = 'inference_script';
    case CONFIG_FILE = 'config_file';
    case PIPELINE_DEFINITION = 'pipeline_definition';
    case CONTAINER_IMAGE = 'container_image';
    case NOTEBOOK = 'notebook';
    case EVALUATION_REPORT = 'evaluation_report';
    case EXPLAINABILITY_REPORT = 'explainability_report';
    case DOCUMENTATION = 'documentation';
    case MODEL_CARD = 'model_card';
    case DATA_SCHEMA = 'data_schema';
    case OTHER = 'other';
}

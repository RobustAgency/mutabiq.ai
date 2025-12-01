<?php

namespace App\Enums;

enum ArtifactFileFormat: string
{
    case BINARY = 'binary';
    case ONNX = 'onnx';
    case PICKLE = 'pickle_joblib';
    case JSON = 'json';
    case YAML = 'yaml';
    case CSV = 'csv';
    case PARQUET = 'parquet';
    case NOTEBOOK = 'notebook';
    case PDF = 'pdf';
    case MARKDOWN_TXT = 'markdown_txt';
    case DOCKER_OCI_IMAGE = 'docker_oci_image';
    case OTHER = 'other';
}

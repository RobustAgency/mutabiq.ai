<?php

namespace App\Enums\AiModelDataset;

enum Role: string
{
    case PRETRAIN = 'pretrain';
    case TRAIN = 'train';
    case FINE_TUNE = 'fine_tune';
    case ALIGN_RLHF = 'align_rlhf';
    case VALIDATION = 'validation';
    case TEST = 'test';
    case EVAL_BENCHMARK = 'eval_benchmark';
    case RAG_CORPUS = 'rag_corpus';
    case DRIFT_BASELINE = 'drift_baseline';
    case ONLINE_FEEDBACK = 'online_feedback';
}

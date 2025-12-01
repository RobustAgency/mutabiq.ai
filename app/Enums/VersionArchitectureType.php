<?php

namespace App\Enums;

enum VersionArchitectureType: string
{
    case TRANSFORMER = 'transformer';
    case CNN = 'cnn';
    case RNN_LSTM_GRU = 'rnn_lstm_gru';
    case GRADIENT_BOOSTING = 'gradient_boosting';
    case RANDOM_FOREST = 'random_forest';
    case LOGISTIC_REGRESSION = 'logistic_regression';
    case LINEAR_REGRESSION = 'linear_regression';
    case OTHER = 'other';

}

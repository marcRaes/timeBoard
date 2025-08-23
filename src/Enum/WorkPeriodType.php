<?php

namespace App\Enum;

/**
 * Représente le type d'un créneau de travail.
 *
 * - Work : créneau classique (travail)
 * - MeetingTraining : créneau de réunion ou de formation
 */
enum WorkPeriodType: string
{
    case Work = 'work';
    case MeetingTraining = 'meeting_training';
}

<?php

namespace App\Enums;

enum MedicalEventType: string
{
    case Visit = 'visit';
    case Diagnosis = 'diagnosis';
    case Allergy = 'allergy';
    case LabResult = 'lab-result';
    case Surgery = 'surgery';
    case Symptom = 'symptom';
    case Rehabilitation = 'rehabilitation';
    case Dental = 'dental';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::Visit => 'Veterinary visit',
            self::Diagnosis => 'Diagnosis',
            self::Allergy => 'Allergy or reaction',
            self::LabResult => 'Lab result',
            self::Surgery => 'Surgery or procedure',
            self::Symptom => 'Symptom',
            self::Rehabilitation => 'Rehabilitation',
            self::Dental => 'Dental care',
            self::Note => 'Owner observation',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Visit => 'stethoscope',
            self::Diagnosis => 'clipboard-plus',
            self::Allergy => 'triangle-alert',
            self::LabResult => 'flask-conical',
            self::Surgery => 'activity',
            self::Symptom => 'thermometer',
            self::Rehabilitation => 'person-standing',
            self::Dental => 'sparkles',
            self::Note => 'notebook-pen',
        };
    }
}

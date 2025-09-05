<?php
namespace App\Enums;

enum QuestionType:string {
    case Checkbox = 'checkbox';
    case Radio    = 'radio';
    case Text     = 'text';
    // у майбутньому: case Select = 'select'; і т.д.
}

<?php // app/Services/QuizConfig/Contracts/StepComposer.php
namespace App\Services\QuizConfig\Contracts;

use App\Models\Quiz;

interface StepComposer
{
    public function name(): string;                // start|questions|leadform|thanks|...
    public function compose(Quiz $quiz, array $settings): array|null; // один крок або масив кроків або null
}

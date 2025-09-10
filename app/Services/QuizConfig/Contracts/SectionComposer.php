<?php // app/Services/QuizConfig/Contracts/SectionComposer.php
namespace App\Services\QuizConfig\Contracts;

use App\Models\Quiz;

interface SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array;
}

<?php
declare(strict_types=1);

namespace App\Services;

class QcmGradingService
{
    private string $storagePath;

    /**
     * Set up the absolute path to your flat-file JSON keys storage directory
     */
    public function __construct()
    {
        // Points perfectly from app/Services/ up to your storage root
        $this->storagePath = __DIR__ . '/../../storage/qcm_keys';
    }

    /**
     * Compares student selections against the exam template blueprint.
     * Returns the total calculated score, or null if the master key file doesn't exist.
     */
    public function calculateScore(string $examId, array $studentAnswers): ?float
    {
        $keyFilePath = sprintf('%s/exam_%s.json', $this->storagePath, $examId);

        // Fail early if the professor hasn't generated this QCM template key yet
        if (!file_exists($keyFilePath)) {
            error_log("QcmGradingService Error: Template file missing for exam ID: {$examId}");
            return null;
        }

        // Read the saved JSON file
        $fileContent = file_get_contents($keyFilePath);
        $examBlueprint = json_decode($fileContent, true);

        if (!$examBlueprint || !isset($examBlueprint['grading_matrix'])) {
            error_log("QcmGradingService Error: Corrupted or invalid JSON layout for exam ID: {$examId}");
            return null;
        }

        $totalScore = 0.0;
        $matrix = $examBlueprint['grading_matrix'];

        // Loop through the master key and cross-check the student's choices
        foreach ($matrix as $questionKey => $metadata) {
            $correctChoice = $metadata['correct_choice'];
            $questionWeight = (float) $metadata['weight'];

            // If the student answered this question and matched the key, award the points
            if (isset($studentAnswers[$questionKey]) && $studentAnswers[$questionKey] === $correctChoice) {
                $totalScore += $questionWeight;
            }
        }

        return $totalScore;
    }
}
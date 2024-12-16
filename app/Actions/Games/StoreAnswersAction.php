<?php

namespace App\Actions\Games;

use App\Models\Game;
use App\Models\User;
use App\Models\GameUser;
use App\Models\Answer;


class StoreAnswersAction
{
    public function handle(Game $game, User $user, array $data)
    {
        // Find the game_user entry for the current user and the specified game
        $gameUser = GameUser::where('user_id', $user->id)->where('game_id', $game->id)->first();
        if (!$gameUser) {
            return ['status' => 'error', 'message' => 'User not found for this game'];
        }

        // Loop through the answers and create records in the database
        foreach ($data['answers'] as $questionId => $answerText) {
            Answer::updateOrCreate(
                [
                    'game_user_id' => $gameUser->id,
                    'question_id' => $questionId,
                ],
                [
                    'answer' => $answerText,
                ]
            );
        }

        // Determine the status based on the number of answered questions
        $status = count($data['answers']) < count($game->questions)
            ? count($data['answers']) . ' of ' . count($game->questions) . ' Questions Answered'
            : 'Questions Answered';

        // Update the player's status in the pivot table
        $game->players()->updateExistingPivot($user->id, ['status' => $status]);

        return ['status' => 'success', 'message' => $status];
    }
}
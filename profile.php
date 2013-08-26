<?php

function getMinMax($array, $isMax)
{
    $target = $isMax ? max($array) : min($array);
    foreach ($array as $key => $value) {
        if ($value == $target) {
            return $key;
        }
    }
    return null;
}

function profile($id)
{
    $games = file_get_contents('games.log');
    $games = explode("\n", $games);

    $stats = array(
        'games' => 0,
        'wins' => 0,
        'losses' => 0,
        'wins_goalee' => 0,
        'losses_goalee' => 0,
        'wins_midfield' => 0,
        'losses_midfield' => 0,
        'skunk_wins' => 0,
        'skunk_losses' => 0,
        'best_partner' => 0,
        'worst_partner' => 0,
        'best_opponent' => 0,
        'worst_opponent' => 0,
        'winning_score' => 0,
        'losing_score' => 0,
    );

    $partners = array();
    $opponents = array();
    $scores = array();
    $winningStreak = $losingStreak = $currentStreak = 0;
    $lastResult = null;

    foreach ($games as $game) {
        if (empty($game)) {
            continue;
        }
        $game = explode("\t", $game);
        if (count($game) != 4) {
            continue;
        }
        $teams = array(explode(",", $game[1]), explode(",", $game[2]));
        if (!in_array($id, $teams[0]) && !in_array($id, $teams[1])) {
            continue;
        }
        if (count($teams[0]) != 2 || count($teams[1]) != 2) {
            continue;
        }
        $stats['games']++;

        $team = in_array($id, $teams[0]) ? 0 : 1;
        $partnerId = $teams[$team][0] == $id ? $teams[$team][1] : $teams[$team][0];

        $result = explode(",", $game[3]);
        $winningResult = $losingResult = $result;
        rsort($winningResult);
        sort($losingResult);
        $winningResult = join(':', $winningResult);
        $losingResult = join(':', $losingResult);

        $winningTeam = (count($result) == 1 ? $result[0] : ($result[0] > $result[1] ? 1 : 2)) - 1;
        $isWinner = $winningTeam == $team;
        $stats[$isWinner ? 'wins' : 'losses']++;

        $isGoalee = $id == $teams[$team][0];
        $stats[$isWinner ? ($isGoalee ? 'wins_goalee' : 'wins_midfield') : ($isGoalee ? 'losses_goalee' : 'losses_midfield')]++;

        if ($isWinner !== $lastResult) {
            $currentStreak = 0;
            $lastResult = $isWinner;
        }
        $currentStreak++;

        if ($isWinner && $winningStreak < $currentStreak) {
            $winningStreak = $currentStreak;
        }
        if (!$isWinner && $losingStreak < $currentStreak) {
            $losingStreak = $currentStreak;
        }

        if (count($result) == 2) {
            if ($isWinner && $result[1-$team] == 0) {
                $stats['skunk_wins']++;
            }
            if (!$isWinner && $result[$team] == 0) {
                $stats['skunk_losses']++;
            }
            $normalizedResult = $isWinner ? $winningResult : $losingResult;
            $scores[$normalizedResult] = (isset($scores[$normalizedResult]) ? $scores[$normalizedResult] : 0) + ($isWinner ? 1 : -1);
        }

        $partners[$partnerId] = (isset($partners[$partnerId]) ? $partners[$partnerId] : 0) + ($isWinner ? 1 : -1);
        $opponents[$teams[1-$team][0]] = (isset($opponents[$teams[1-$team][0]]) ? $opponents[$teams[1-$team][0]] : 0) + ($isWinner ? 1 : -1);
        $opponents[$teams[1-$team][1]] = (isset($opponents[$teams[1-$team][1]]) ? $opponents[$teams[1-$team][1]] : 0) + ($isWinner ? 1 : -1);
    }

    if ($lastResult && $winningStreak < $currentStreak) {
        $winningStreak = $currentStreak;
    }
    if (!$lastResult && $losingStreak < $currentStreak) {
        $losingStreak = $currentStreak;
    }

    $stats['best_partner'] = getMinMax($partners, true);
    $stats['worst_partner'] = getMinMax($partners, false);
    $stats['best_opponent'] = getMinMax($opponents, true);
    $stats['worst_opponent'] = getMinMax($opponents, false);
    $stats['winning_score'] = getMinMax($scores, true);
    $stats['losing_score'] = getMinMax($scores, false);
    $stats['winning_streak'] = $winningStreak;
    $stats['losing_streak'] = $losingStreak;

    return $stats;
}

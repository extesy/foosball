<?php

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
        'skunks' => 0,
        'skunked' => 0,
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

    foreach ($games as $game)
    {
        if (empty($game)) continue;
        $game = explode("\t", $game);
        if (count($game) != 4) continue;
        $teams = array(explode(",", $game[1]), explode(",", $game[2]));
        if (!in_array($id, $teams[0]) && !in_array($id, $teams[1])) continue;
        if (count($teams[0]) != 2 || count($teams[1]) != 2) continue;
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

        if (count($result) == 2) {
            if ($isWinner && $result[1-$team] == 0) $stats['skunks']++;
            if (!$isWinner && $result[$team] == 0) $stats['skunked']++;
            $normalizedResult = $isWinner ? $winningResult : $losingResult;
            $scores[$normalizedResult] = (isset($scores[$normalizedResult]) ? $scores[$normalizedResult] : 0) + ($isWinner ? 1 : -1);
        }

        $partners[$partnerId] = (isset($partners[$partnerId]) ? $partners[$partnerId] : 0) + ($isWinner ? 1 : -1);
        $opponents[$teams[1-$team][0]] = (isset($opponents[$teams[1-$team][0]]) ? $opponents[$teams[1-$team][0]] : 0) + ($isWinner ? 1 : -1);
        $opponents[$teams[1-$team][1]] = (isset($opponents[$teams[1-$team][1]]) ? $opponents[$teams[1-$team][1]] : 0) + ($isWinner ? 1 : -1);
    }

    $stats['best_partner'] = array_keys($partners, max($partners))[0];
    $stats['worst_partner'] = array_keys($partners, min($partners))[0];
    $stats['best_opponent'] = array_keys($opponents, max($opponents))[0];
    $stats['worst_opponent'] = array_keys($opponents, min($opponents))[0];
    $stats['winning_score'] = array_keys($scores, max($scores))[0];
    $stats['losing_score'] = array_keys($scores, min($scores))[0];

    return $stats;
}

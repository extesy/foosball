<?php

function load_players()
{
    $content = file_get_contents('players.txt');
    $content = explode("\n", $content);

    $players = array();
    foreach ($content as $line) {
        if (empty($line)) continue;
        $columns = explode("\t", $line);
        $id = (int)$columns[0];
        $name = trim($columns[1]);
        $players[$id] = $name;
    }

    return $players;
}

function players()
{
    $players = load_players();
    $result = array();
    foreach ($players as $id => $name)
        $result[] = array($id, $name);
    return $result;
}

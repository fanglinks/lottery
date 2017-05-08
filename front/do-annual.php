<?php

require '../config.php';

$action = __reqt('action');

// echo $total;

function run(&$winner_array) {
    $total = AnnualLottery::getTotalAmount();
    $could_lottery = AnnualLottery::getAllPeople();
    if(!count($could_lottery)) {
        return;
    }
    $win_num = rand(0, $total - 1);
    $win_num = $could_lottery[$win_num]['id'];
    
    $winner = AnnualLottery::getWinner($win_num);
    if( $winner['name'] && (float)$winner['rate'] !== 1 ){
        $win_rate = $winner['rate'] * 100;
        if( $win_rate < rand(1, 100) ) {
            run($winner_array);
            return;
        }
    }
    array_push($winner_array, $winner);
    AnnualLottery::setWinner($win_num);    
};

switch ($action) {
    case 'lottery':
        $amount = __post('amount', 2);
        $winner_array = array();
        for($i = 0; $i < $amount; $i++){
            run($winner_array);
        }
        $output = $winner_array;
        break;
    case 'queryPeople':
        $output = AnnualLottery::queryPeople();
        break;
    case 'clearWinner':
        $output = AnnualLottery::clearWinner();
        $output['status'] = 'ok';
        break;
    case 'clearRate':
        $output = AnnualLottery::clearRate();
        $output['status'] = 'ok';
        break;
    case 'changeRate':
        $rate = __post('rate', array());
        // $id = __post('id', array());
        $users = __post('users', array());
        if(count($users)){
            $id = '(';
            foreach($users as $key => $user){
                $id .= '"';
                $id .= $user['id'];
                $id .= '",';
            }
            $id = substr($id, 0, -1);
            $id .= ')';
            $output = AnnualLottery::changeRate($rate, $id);
            $output['status'] = 'ok';
        }else{
            $output['status'] = 'error';            
        }
        
        break;
}

die(json_encode($output));
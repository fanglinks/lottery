<?php
class AnnualLottery {
    /* ---------------前端使用------------------------ */
    public static function getTotalAmount(){ // 可参与者的数量
        $sql = 'SELECT count(*) as num FROM `annual_lottery` WHERE `winner` != 1';
        $amount = DB::getOne($sql);
        return $amount['num'];
    }
    public static function getAllPeople(){ // 获取所有可参与者id
        $sql = 'SELECT id FROM `annual_lottery` WHERE `winner` != 1';
        $all = DB::getAll($sql);
        return $all;
    }
    public static function getWinner($num){ //获取某id获奖者信息
        $sql = 'SELECT * FROM `annual_lottery` WHERE `id` = ? AND `winner` != 1';
        $winner = DB::getOne($sql, array($num));
        return $winner;
    }
    public static function setWinner($id){ // 记录获奖者
        $sql = 'UPDATE `annual_lottery` SET `winner` = 1 WHERE `id` = ?';
        $result = DB::exec($sql, array($id));
        return $result;
    }
    public static function reStart(){ // 重新开始
        $sql = 'UPDATE `annual_lottery` SET `winner` = ?';
        $result = DB::exec($sql, array('0'));
        return $result;
    }
    /* ---------------CMS使用------------------------ */
    public static function queryPeople(){ // 获取所有可参与者id
        $sql = 'SELECT * FROM `annual_lottery`';
        $all = DB::getAll($sql);
        return $all;
    }
    public static function clearWinner(){ // 清空获奖者信息
        $sql = 'UPDATE `annual_lottery` SET `winner` = "0" where `name` != ""';
        $all = DB::exec($sql);
        return $all;
    }
    public static function clearRate(){ // 初始化所有概率
        $sql = 'UPDATE `annual_lottery` SET `rate` = "1" where `name` != ""';
        $all = DB::exec($sql);
        return $all;
    }
    public static function changeRate($rate, $id){ // 改变参与者概率
        $sql = 'UPDATE `annual_lottery` SET `rate` = ? where `id` in'.$id;
        $all = DB::exec($sql, array($rate));
        return $all;
    }
}

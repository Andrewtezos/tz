<?php

namespace App\Helpers;

use App\Helpers\RequestApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

Class Functions {

  function valute($data)
  {
    $dataOut['cycle'] = $data['period_summary']->cycle;
    $dataOut['level'] = number_format($data['level_hash']->level, 0, ',', ' ');
    $dataOut['timevalue'] = ($data['last_level_position'] - $data['level_hash']->level);
    $dataOut['timevalue'] = self::secondsToTime((int)($dataOut['timevalue']*60));
    $dataOut['percent'] = number_format((($data['level_hash']->cycle_position/4096)*100), 0, ',', ' ');
    $dataOut['circulating_supply'] = number_format(($data['supply']->circulating_supply/1000000000000), 2, ',', ' ');
    $dataOut['price_usd'] = number_format($data['apiMarketCapTicker'][0]->price_usd, 2, ',', ' ');
    $dataOut['price_btc'] = number_format($data['apiMarketCapTicker'][0]->price_btc, 8, ',', ' ');
    $dataOut['percent_change_24h'] = $data['apiMarketCapTicker'][0]->percent_change_24h;
    $dataOut['market_cap_usd'] = round($data['apiMarketCapTicker'][0]->market_cap_usd/1000);
    $dataOut['market_cap_usd'] = number_format($dataOut['market_cap_usd'], 0, ',', ' ');

    return $dataOut;
  }

  function getPec($data, $tz)
  {
      $endorsement=array();
      $baking=array();
      foreach ($data['cycle_bakings'] as $b){
          $baking[$b->cycle] = array(
              'bakingAll'=>$b->count->count_all,
              'bakingMiss'=>$b->count->count_miss,
              'bakingSteal'=>$b->count->count_steal,
          );
      }
      foreach ($data['cycle_endorsements'] as $e){
          $endorsement[$e->cycle] = array(
              'endorsementAll'=>$e->slots->count_all,
              'endorsementMiss'=>$e->slots->count_miss,
              'endorsementSteal'=>$e->slots->count_steal,
              'bakingAll'=>$baking[$e->cycle]['bakingAll'],
              'bakingMiss'=>$baking[$e->cycle]['bakingMiss'],
              'bakingSteal'=>$baking[$e->cycle]['bakingSteal'],
          );
      }

      $a = [
          'baking'=> [
              'All'=>$data['total_bakings'][0]->count->count_all,
              'Miss'=>$data['total_bakings'][0]->count->count_miss,
              'Steal'=>$data['total_bakings'][0]->count->count_steal,
          ],
          'endorsement'=>[
              'All'=>$data['total_endorsements'][0]->slots->count_all,
              'Miss'=>$data['total_endorsements'][0]->slots->count_miss,
              'Steal'=>$data['total_endorsements'][0]->slots->count_steal,
          ],
          'balance'=>[
              'balance_from_balance_updates'=>[
                  'spendable'=>$data['balance_from_balance_updates']->spendable,
                  'frozen'=>$data['balance_from_balance_updates']->frozen,
                  'fees'=>$data['balance_from_balance_updates']->fees,
              ],
              'rewards_split'=>$data['staking_balance'][0],
          ],
          'past'=>$endorsement,
      ];
      $dataOut['freespace'] = self::freeSpace($a);
      $dataOut['efficiency'] = self::efficiency($a);
      $dataOut['last10'] = self::last10($a);
      $dataOut['lifetime'] = $data['number_cycle_bakings'][0];
      return $dataOut;
  }

  function loadPec($data, $tz) {
    $endorsement=array();
    $baking=array();
    foreach ($data['cycle_bakings'] as $b){
        $baking[$b->cycle] = array(
            'bakingAll'=>$b->count->count_all,
            'bakingMiss'=>$b->count->count_miss,
            'bakingSteal'=>$b->count->count_steal,
        );
    }

    foreach ($data['cycle_endorsements'] as $e){
        $endorsement[$e->cycle] = array(
            'endorsementAll'=>$e->slots->count_all,
            'endorsementMiss'=>$e->slots->count_miss,
            'endorsementSteal'=>$e->slots->count_steal,
            'bakingAll'=>$baking[$e->cycle]['bakingAll'],
            'bakingMiss'=>$baking[$e->cycle]['bakingMiss'],
            'bakingSteal'=>$baking[$e->cycle]['bakingSteal'],
        );
    }

    $tezos = DB::table('nodes')->where('tezosid', $tz)->first();
    $a = [
      'lifetime' => $data['number_cycle_bakings'][0],
      'baking'=> [
          'All'=>$data['total_bakings'][0]->count->count_all,
          'Miss'=>$data['total_bakings'][0]->count->count_miss,
          'Steal'=>$data['total_bakings'][0]->count->count_steal,
      ],
      'endorsement'=>[
          'All'=>$data['total_endorsements'][0]->slots->count_all,
          'Miss'=>$data['total_endorsements'][0]->slots->count_miss,
          'Steal'=>$data['total_endorsements'][0]->slots->count_steal,
      ],
      'past'=>$endorsement,
      'balance'=>[
          'balance_from_balance_updates'=>[
              'spendable'=>$data['balance_from_balance_updates']->spendable,
              'frozen'=>$data['balance_from_balance_updates']->frozen,
              'fees'=>$data['balance_from_balance_updates']->fees,
          ],
          'rewards_split'=>$data['staking_balance'][0],
      ],
      'account_status'=>$data['account_status'],
      'fee'=>$tezos->fee,
      'support'=>$tezos->support,
      'projects'=>$tezos->projects,
      'payouts'=>$tezos->payouts,
    ];
    $a['freespace'] = self::freeSpace($a);
    $a['efficiency'] = self::efficiency($a);
    $a['last10'] = self::last10($a);
    $a['totalpoints'] = self::totalPoints($a);
    $a['evbalance'] = $a['balance']['balance_from_balance_updates']['spendable']+$a['balance']['balance_from_balance_updates']['frozen'];
    $a['evbalance'] = (int) number_format(($a['evbalance']/1000000), 0,'.', '');
    $a['stbalance'] = (int) number_format(($a['balance']['rewards_split']/1000000), 0,'.', '');
    $a['roll'] = $data['roll'][0];
    $a['delegates'] = $data['delegates'][0];

    $dataTableNodes = DB::table('data_table_nodes')->where('id', 1)->first();
    $legend = unserialize($dataTableNodes->obj);

    foreach ($legend as $k=>$val){
         foreach ($data['votesAccount'] as $v){
            if($k==substr($v->proposal_hash, 0, 10)){
                if($v->period_kind=='proposal'){
                    $value='checkboxenable.svg';
                }
                if($v->period_kind=='testing_vote' || $v->period_kind=='promotion_vote'){
                    $value=(isset($v->ballot)?$v->ballot:'');
                }
                $dataOut[$val][$v->period_kind]['value']=$value;
                $dataOut[$val][$v->period_kind]['ballot']=(isset($v->ballot)?$v->ballot:'');
                $dataOut[$val][$v->period_kind]['period']=$v->period_kind;
            }
         }
    }
    if(isset($dataOut) && !empty($dataOut)) $a['tableOut'] = serialize($dataOut);
    unset($a['baking']);
    unset($a['endorsement']);
    unset($a['past']);
    unset($a['balance']);
    unset($a['account_status']);
    unset($a['fee']);
    unset($a['support']);
    unset($a['projects']);
    unset($a['payouts']);
    return $a;
  }

  function loadTestPec($data, $tz) {
    $endorsement=array();
    $baking=array();
    foreach ($data['cycle_bakings'] as $b){
        $baking[$b->cycle] = array(
            'bakingAll'=>$b->count->count_all,
            'bakingMiss'=>$b->count->count_miss,
            'bakingSteal'=>$b->count->count_steal,
        );
    }
    foreach ($data['cycle_endorsements'] as $e){
        $endorsement[$e->cycle] = array(
            'endorsementAll'=>$e->slots->count_all,
            'endorsementMiss'=>$e->slots->count_miss,
            'endorsementSteal'=>$e->slots->count_steal,
            'bakingAll'=>$baking[$e->cycle]['bakingAll'],
            'bakingMiss'=>$baking[$e->cycle]['bakingMiss'],
            'bakingSteal'=>$baking[$e->cycle]['bakingSteal'],
        );
    }
    $tezos = DB::table('nodes')->where('tezosid', $tz)->first();
    $a = [
      'lifetime' => $data['number_cycle_bakings'][0],
      'baking'=> [
          'All'=>$data['total_bakings'][0]->count->count_all,
          'Miss'=>$data['total_bakings'][0]->count->count_miss,
          'Steal'=>$data['total_bakings'][0]->count->count_steal,
      ],
      'endorsement'=>[
          'All'=>$data['total_endorsements'][0]->slots->count_all,
          'Miss'=>$data['total_endorsements'][0]->slots->count_miss,
          'Steal'=>$data['total_endorsements'][0]->slots->count_steal,
      ],
      'past'=>$endorsement,
      'balance'=>[
          'balance_from_balance_updates'=>[
              'spendable'=>$data['balance_from_balance_updates']->spendable,
              'frozen'=>$data['balance_from_balance_updates']->frozen,
              'fees'=>$data['balance_from_balance_updates']->fees,
          ],
          'rewards_split'=>$data['staking_balance'][0],
      ],
      'account_status'=>$data['account_status'],
      'fee'=>$tezos->fee,
      'support'=>$tezos->support,
      'projects'=>$tezos->projects,
      'payouts'=>$tezos->payouts,
    ];
    $a['freespace'] = self::freeSpace($a);
    $a['efficiency'] = self::efficiency($a);
    $a['last10'] = self::last10($a);
    $a['totalpoints'] = self::totalPoints($a);
    $a['evbalance'] = $a['balance']['balance_from_balance_updates']['spendable']+$a['balance']['balance_from_balance_updates']['frozen'];
    $a['evbalance'] = (int) number_format(($a['evbalance']/1000000), 0,'.', '');
    $a['stbalance'] = (int) number_format(($a['balance']['rewards_split']/1000000), 0,'.', '');
    $a['roll'] = $data['roll'][0];
    $a['delegates'] = $data['delegates'][0];
    dd($a, $data);
    $dataTableNodes = DB::table('data_table_nodes')->where('id', 1)->first();
    $legend = unserialize($dataTableNodes->obj);

    foreach ($legend as $k=>$val){
         foreach ($data['votesAccount'] as $v){
            if($k==substr($v->proposal_hash, 0, 10)){
                if($v->period_kind=='proposal'){
                    $value='checkboxenable.svg';
                }
                if($v->period_kind=='testing_vote' || $v->period_kind=='promotion_vote'){
                    $value=(isset($v->ballot)?$v->ballot:'');
                }
                $dataOut[$val][$v->period_kind]['value']=$value;
                $dataOut[$val][$v->period_kind]['ballot']=(isset($v->ballot)?$v->ballot:'');
                $dataOut[$val][$v->period_kind]['period']=$v->period_kind;
            }
         }
    }
    if(isset($dataOut) && !empty($dataOut)) $a['tableOut'] = serialize($dataOut);
    unset($a['baking']);
    unset($a['endorsement']);
    unset($a['past']);
    unset($a['balance']);
    unset($a['account_status']);
    unset($a['fee']);
    unset($a['support']);
    unset($a['projects']);
    unset($a['payouts']);
    return $a;
  }

  function secondsToTime($seconds) {
      $dtF = new \DateTime('@0');
      $dtT = new \DateTime("@$seconds");
      return $dtF->diff($dtT)->format('%ad %hh %im');
  }

  function freeSpace($tz) {
    $evaluateBalance = $tz['balance']['balance_from_balance_updates']['spendable']+$tz['balance']['balance_from_balance_updates']['frozen'];
    $stakingBalance = $tz['balance']['rewards_split'];
    $freeSpace = (($evaluateBalance/0.113) - $stakingBalance)/1000000;
    $freeSpace = number_format((float)$freeSpace, 0, '.', '');
    return $freeSpace;
  }

  function efficiency($tz){
    $efficiency =
      ((
          ($tz['baking']['All']-$tz['baking']['Miss']+$tz['baking']['Steal'])*8
          +
          ($tz['endorsement']['All']-$tz['endorsement']['Miss'])
      ) / (
              $tz['baking']['All']*8
              + $tz['endorsement']['All']
          )
      )*100;
      $efficiency = number_format((float)$efficiency, 2, '.', '');
      return $efficiency;
  }

  function last10($tz){
    $effCalc=0;
    $i = 10;
    foreach ($tz['past'] as $v){
      if($v['endorsementAll'] > 0) {
        $efficiency10 =
        (
          (
            (
              (
                $v['bakingAll'] - $v['bakingMiss'] + $v['bakingSteal']
              ) * 8
            )
            +
            (
              $v['endorsementAll'] - $v['endorsementMiss']
            )
          ) / (
            $v['bakingAll'] * 8 + $v['endorsementAll']
          )
        )*100
        ;
      } else {
        $efficiency10 = 0;
        $i--;
      }
      if($efficiency10 <= 0) {
        $efficiency10 = 0;
      }
      $effCalc+=$efficiency10;
    }
    $effCalc=$effCalc/$i;
    $effCalc = number_format((float)$effCalc, 2, '.', '');
    return $effCalc;
  }

  function totalPoints($tz) {
    $efficiency= str_replace(",",".",$tz['efficiency']);
    $lifetime = $tz['lifetime'];
    $effCalc= str_replace(",",".",$tz['last10']);
    $evaluateBalance = $tz['balance']['balance_from_balance_updates']['spendable']+$tz['balance']['balance_from_balance_updates']['frozen'];
    $stakingBalance = $tz['balance']['rewards_split'];
    $freeSpace = (($evaluateBalance/0.113) - $stakingBalance)/1000000;
    $freeSpace = number_format($freeSpace, 0, '', ',');
    $efficiencyPoints=array('100'=>30,'99,5'=>25,'99,01'=>20,'98,01'=>15,'97,01'=>10,'96,01'=>5);
    $effCalcPoints=array('100'=>20,'99,5'=>15,'99,01'=>12,'98,01'=>10,'97,01'=>7,'96,01'=>5);
    $fee=array('4.99'=>20,'7.99'=>17,'9.99'=>15,'12.49'=>12,'14.99'=>10,'19.99'=>5);
    $support=array('handphones.svg'=>10,'mail.svg'=>5,'discus.svg'=>5, 'internet.svg'=>0, 'internet_1.svg'=>0);
    $projects=array('gear.svg'=>10,'human.svg'=>5,'biscuit.svg'=>0);
    $payouts=array('snowleft1.svg'=>10,'snowright1.svg'=>5, 'snowright2.svg'=>0);
    $totalPoints=0;
    $linePoints=array();
    $totalPoints+=self::calcTotalPoints($efficiencyPoints, $efficiency);
    $linePoints['efficiencyPoints']=$totalPoints;
    $totalPoints+=self::calcTotalPoints($effCalcPoints, $effCalc);
    $linePoints['+effCalcPoints']=$totalPoints;
    foreach ($fee as $key=>$val) {
        if((str_replace(",",".",$key)-str_replace(",",".",$tz['fee']))>=0) {
            $totalPoints+=$val;
            break;
        }
    }
    $linePoints['+fee']=$totalPoints;
    if(isset($tz['support']) && $tz['support'] != '') {
      $totalPoints+=$support[$tz['support']];
      $linePoints['+support']=$totalPoints;
    }
    if(isset($tz['projects']) && $tz['projects'] != '') {
      $totalPoints+=$projects[$tz['projects']];
      $linePoints['+projects']=$totalPoints;
    }
    if(isset($tz['payouts']) && $tz['payouts'] != '') {
      $totalPoints+=$payouts[$tz['payouts']];
      $linePoints['+payouts']=$totalPoints;
    }
    return $totalPoints;
  }

  function calcTotalPoints($pList,$item) {
      $totalPoints=0;
      foreach ($pList as $k=>$e) {
          if(($item-str_replace(",",'.',$k))>=0) {
              $totalPoints+=$e;
              break;
          }
      }
      return $totalPoints;
  }

  public function feeUpdate($date)
  {
      $date = new \DateTime($date);
      $date2 = new \DateTime(now());
      $months = 0;

      $date->add(new \DateInterval('P1M'));
      while ($date <= $date2){
          $months ++;
          $date->add(new \DateInterval('P1M'));
      }

      if($months >= 3) {
          return true;
      } else {
          return false;
      }
  }

  public function processData($data)
  {
      $dataOut['title'] = $data->title;
      if(isset($data->newimg) && $data->newimg != '') {
          $name = str_replace('img/tezos/', '', $data->newimg->store('img/tezos', 'files'));
          $dataOut['img'] = $name;
      } else {
          $dataOut['img'] = $data->img;
      }
      if(isset($data->tezosid) && $data->tezosid != '' && $data->tezosid != null) {
          $dataOut['tezosid'] = $data->tezosid;
      }
      $dataOut['fee'] = $data->fee;
      $dataOut['support'] = $data->support;
      $dataOut['support_link'] = $data->support_link;
      $dataOut['projects'] = $data->projects;
      $dataOut['projects_link'] = $data->projects_link;
      $dataOut['payouts'] = $data->payouts;
      foreach($data->contact as $key=>$value) {
          $dataOut['contacts'][$value] = $data->contact_link[$key];
      }
      $dataOut['contacts'] = serialize($dataOut['contacts']);
      foreach($data->personal as $key=>$value) {
          $dataOut['personal']['img'][$key] = $value;
          $dataOut['personal']['text'][$key] = $data->personal_text[$key];
          $dataOut['personal']['link'][$key] = $data->personal_link[$key];
      }
      $dataOut['personal'] = serialize($dataOut['personal']);
      $dataOut['article'] = htmlentities($data->article);
      $dataOut['statusPec'] = (int) $data->delegations;
      $dataOut['pro'] = isset($data->pro) ? $data->pro : 0;
      $dataOut['position'] = isset($data->position) ? $data->position : 0;
      $dataOut['sleep'] = isset($data->sleep) ? $data->sleep : 0;
      $dataOut['sleep_about'] = isset($data->sleep_about) ? $data->sleep_about : '';
      $dataOut['exchange'] = isset($data->exchange) ? $data->exchange : 0;
      $dataOut['notifier'] = isset($data->notifier) ? $data->notifier : '';
      $dataOut['updated_at'] = now();
      return $dataOut;
  }
}

?>

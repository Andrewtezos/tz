<?php
namespace App\Helpers;

use App\Helpers\Cls;
use App\Helpers\Ldr;
use App\Helpers\Functions;
use Illuminate\Support\Facades\DB;

class RequestApi extends Cls {

  function __construct(){
    $this->api = 'https://api6.tzscan.io/v3';
    $this->apiV1 = 'https://api1.tzscan.io/v1';
    $this->apiMarketCap = 'https://api.coinmarketcap.com/v1';
  }

  function getOnePec($tz=null) {
      $i['balance_from_balance_updates'] = [
          'url' => $this->api.'/balance_from_balance_updates/'.$tz,
      ];
      $i['total_bakings'] = [
          'url' => $this->api.'/total_bakings/'.$tz,
      ];
      $i['total_endorsements'] = [
          'url' => $this->api.'/total_endorsements/'.$tz,
      ];
      $i['cycle_endorsements'] = [
        'url'=>$this->api.'/cycle_endorsements/'.$tz,
        'get'=>array(
          'p'=>0,
          'number'=>10,
        ),
      ];
      $i['staking_balance'] = [
        'url'=>$this->api.'/staking_balance/'.$tz,
      ];
      $i['number_cycle_bakings'] = [
        'url' => $this->api.'/number_cycle_bakings/'.$tz,
      ];
      $i['cycle_bakings'] = [
        'url'=>$this->api.'/cycle_bakings/'.$tz,
        'get'=>array(
          'p'=>0,
          'number'=>10,
        ),
      ];
      $data = Ldr::http_multi($i);
      if($data['staking_balance'] == null) return false;
      $dataOut = new Functions;
      return $dataOut->getPec($data,$tz);
  }

  function period_summary(){
    return Ldr::http([
        'url'=>$this->api.'/period_summary',
    ]);
  }
  function loadOnePec($tz=null){
      $cycle = DB::table('valute')->where('id', 1)->first();
      $cycle->cycle = $cycle->cycle + 5;
     $i['total_bakings'] = [
      'url' => $this->api.'/total_bakings/'.$tz,
    ];
    $i['total_endorsements'] = [
      'url' => $this->api.'/total_endorsements/'.$tz,
    ];
    $i['number_cycle_bakings'] = [
      'url' => $this->api.'/number_cycle_bakings/'.$tz,
    ];
    $i['balance_from_balance_updates'] = [
      'url' => $this->api.'/balance_from_balance_updates/'.$tz,
    ];
    $i['account_status'] = [
      'url' => $this->api.'/account_status/'.$tz,
    ];
    $i['cycle_bakings'] = [
      'url'=>$this->api.'/cycle_bakings/'.$tz,
      'get'=>array(
        'p'=>0,
        'number'=>10,
      ),
    ];
    $i['cycle_endorsements'] = [
      'url'=>$this->api.'/cycle_endorsements/'.$tz,
      'get'=>array(
        'p'=>0,
        'number'=>10,
      ),
    ];
    $i['rewards_split'] = [
      'url'=>$this->api.'/rewards_split/'.$tz,
      'get'=>array(
        'p'=>0,
        'number'=>10,
      ),
    ];
    $i['staking_balance'] = [
      'url'=>$this->api.'/staking_balance/'.$tz,
    ];
    $i['roll'] = [
      'url'=>$this->api.'/roll_number/'.$tz,
    ];
    $i['delegates'] = [
       'url' => $this->api.'/nb_delegators/'.$tz.'?cycle='.$cycle->cycle,
    ];
    $i['votesAccount'] = [
      'url'=>$this->api.'/votes_account/'.$tz,
      'get'=>array(
          'p'=>0,
          'number'=>10,
      ),
    ];
    $data = Ldr::http_multi($i);
    $dataOut = new Functions;
    return $dataOut->loadPec($data,$tz);
  }
  function loadTestPec($tz=null){
      $cycle = DB::table('valute')->where('id', 1)->first();
      $cycle->cycle = $cycle->cycle + 5;
     $i['total_bakings'] = [
      'url' => $this->api.'/total_bakings/'.$tz,
    ];
    $i['total_endorsements'] = [
      'url' => $this->api.'/total_endorsements/'.$tz,
    ];
    $i['number_cycle_bakings'] = [
      'url' => $this->api.'/number_cycle_bakings/'.$tz,
    ];
    $i['balance_from_balance_updates'] = [
      'url' => $this->api.'/balance_from_balance_updates/'.$tz,
    ];
    $i['account_status'] = [
      'url' => $this->api.'/account_status/'.$tz,
    ];
    $i['cycle_bakings'] = [
      'url'=>$this->api.'/cycle_bakings/'.$tz,
      'get'=>array(
        'p'=>0,
        'number'=>10,
      ),
    ];
    $i['cycle_endorsements'] = [
      'url'=>$this->api.'/cycle_endorsements/'.$tz,
      'get'=>array(
        'p'=>0,
        'number'=>10,
      ),
    ];
    $i['rewards_split'] = [
      'url'=>$this->api.'/rewards_split/'.$tz,
      'get'=>array(
        'p'=>0,
        'number'=>10,
      ),
    ];
    $i['staking_balance'] = [
      'url'=>$this->api.'/staking_balance/'.$tz,
    ];
    $i['roll'] = [
      'url'=>$this->api.'/roll_number/'.$tz,
    ];
    $i['delegates'] = [
       'url' => $this->api.'/nb_delegators/'.$tz.'?cycle='.$cycle->cycle,
    ];
    $i['votesAccount'] = [
      'url'=>$this->api.'/votes_account/'.$tz,
      'get'=>array(
          'p'=>0,
          'number'=>10,
      ),
    ];
    $data = Ldr::http_multi($i);
    $dataOut = new Functions;
    return $dataOut->loadTestPec($data,$tz);
  }
  function loadValute() {
    $i['period_summary'] = [
      'url'=>$this->api.'/period_summary',
    ];
    $i['supply'] = [
      'url'=>$this->api.'/supply',
    ];
    $i['head'] = [
      'url'=>$this->api.'/head',
    ];
    $i['apiMarketCapTicker'] = [
      'url'=>$this->apiMarketCap.'/ticker/tezos/?convert=usd',
    ];

    $data = Ldr::http_multi($i);

    $data['level_hash'] = json_decode(Ldr::http([
      'url'=>$this->api.'/level/'.$data['head']->hash,
    ]));

    $data['block_per_cycle'] = ($data['level_hash']->level-$data['level_hash']->cycle_position+1)/$data['level_hash']->cycle;
    $data['last_level_position'] = ($data['block_per_cycle']*($data['level_hash']->cycle + 1));

    $dataOut = new Functions;
    $dataOut = $dataOut->valute($data);
    $dataOut['updated_at'] = now();
    DB::table('valute')->where('id', 1)->update($dataOut);
  }

  function updateOnePec($data, $tz) {
    $data['updated_at'] = now();
    DB::table('nodes')->where('tezosid', $tz)->update($data);
  }

  function head(){
    return Ldr::http([
      'url'=>$this->api.'/head',
    ]);
  }
  function blocks(){
    return Ldr::http([
      'url'=>$this->api.'/blocks',
    ]);
  }
  function voting_period_info(){
    return Ldr::http([
      'url'=>$this->api.'/voting_period_info',
    ]);
  }
  function supply(){
    return Ldr::http([
      'url'=>$this->api.'/supply',
    ]);
  }

  function total_bakings($tz=null){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/total_bakings/'.$tz,
    ]);
  }
  function account_status($tz=null){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/account_status/'.$tz,
    ]);
  }
  function number_cycle_bakings($tz=null){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/number_cycle_bakings/'.$tz,
    ]);
  }
  function total_endorsements($tz=null){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/total_endorsements/'.$tz,
    ]);
  }
  function cycle_bakings($tz=null,$p=0,$number=10){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/cycle_bakings/'.$tz,
      'get'=>array(
        'p'=>$p,
        'number'=>$number,
      ),
    ]);
  }
  function cycle_endorsements($tz=null,$p=0,$number=10){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/cycle_endorsements/'.$tz,
      'get'=>array(
        'p'=>$p,
        'number'=>$number,
      ),
    ]);
  }
  function balance_from_balance_updates($tz=null){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/balance_from_balance_updates/'.$tz,
    ]);
  }
  function rewards_split($tz=null,$p=0,$number=10){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/rewards_split/'.$tz,
      'get'=>array(
        'p'=>$p,
        'number'=>$number,
      ),
    ]);
  }
  function rewards_split_cycles($tz=null,$p=0,$number=10){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/rewards_split_cycles/'.$tz,
      'get'=>array(
        'p'=>$p,
        'number'=>$number,
      ),
    ]);
  }

  function staking_balance($tz=null){
    if($tz)
    return Ldr::http([
      'url'=>$this->api.'/staking_balance/'.$tz,
    ]);
  }

  function level_hash($hash){
    return Ldr::http([
      'url'=>$this->api.'/level/'.$hash,
    ]);
  }
  function level_timestamp($hash){
    return Ldr::http([
      'url'=>$this->api.'/timestamp/'.$hash,
    ]);
  }
  function apiMarketCapTicker(){
    return Ldr::http([
      'url'=>$this->apiMarketCap.'/ticker/tezos/?convert=usd',
    ]);
  }

}

?>

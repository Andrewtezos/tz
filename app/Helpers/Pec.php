<?php

namespace App\Helpers;

use App\Helpers\RequestApi;

Class Pec {

  function update($tz)
  {
    $id = new RequestApi;
    $data = $id->loadOnePec($tz);
    $id->updateOnePec($data,$tz);
    return 'Update successful Tezos: '.$tz;
  }

  function calc($tz)
  {
      $id = new RequestApi;
      $data = $id->getOnePec($tz);
      if($data == false) {
          return false;
      }
      return $data;
  }

}

 ?>

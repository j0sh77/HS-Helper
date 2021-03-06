<?php
  ini_set('display_errors', 'On');
  ini_set('memory_limit', '-1');
  ini_set('max_execution_time', 0);
  include('simple_html_dom.php');


  //get full json
  $json = json_decode(file_get_contents("cards.json"));

  $all_collectible = array();
  $all = array();

  $char = array(" ", "!", ".", "'", ":");
  $charReplace = array("_", "", "", "", "");

  //split json objects into files
  foreach($json as $card){
    $card = json_decode(json_encode($card), true);

    if(is_array($card) && array_key_exists("name", $card) && $card["type"] != "Hero"){
      $filename = strtolower(str_replace($char, $charReplace, $card["name"]));
      $all[$card["name"]] = $filename;

      echo date('h:i:s') . " - Created cards/$filename.json";
      file_put_contents("cards/$filename.json", json_encode($card));

      if(array_key_exists("collectible", $card) && $card["collectible"] == "true"){
        $all_collectible[$card["name"]] = $filename;
      }else{
        echo "<b style='font-size: 10px'>(uncollectible)</b>";
      }

      echo "<br/>";
    }

  }


  echo "<br/><br/><br/>";

  //get images
  $url = "http://www.hearthpwn.com/cards?page=";

  for($i = 1; $i <= 21; $i+=1){
    $imageDom = file_get_html($url . $i);
    $images = $imageDom->find("td[class=visual-image-cell] a img");
    $titleDom = file_get_html($url . $i);
    $titles = $titleDom->find("td[class=visual-details-cell] h3 a");
    $detailsDom = file_get_html($url . $i);
    $details = $detailsDom->find("td[class=visual-details-cell]");

    //for($j = 0; $j <= 2; $j+=1){
    for($j = 0; $j <= sizeof($images); $j+=1){
      if(!isset($titles[$j]->innertext)) continue;

      $title = $titles[$j]->innertext;
      $title = strtolower(str_replace($char, $charReplace, $title));

      $infoDom = str_get_html($details[$j]);
      $info = $infoDom->find("ul li");
      $type = "";

      foreach($info as $in){
        $d = str_get_html($in);
        if($type == "" && strpos($in, "Type") !== false){
          $type = $d->find("a", 0)->innertext;
        }
      }

      if($type == "Hero" || !file_exists("cards/$title.json")) continue;

      $json = json_decode(file_get_contents("cards/$title.json"), true);
      echo "<b>cards/$title.json: </b>";
      if(isset($images[$j]->attr['data-imageurl'])){
        $normal = $images[$j]->attr['data-imageurl'];
        $json["normal"] = $normal;
        echo "Added normal image. ";
      }
      if(isset($images[$j]->attr['data-gifurl'])){
        $gold = $images[$j]->attr['data-gifurl'];
        $json["gold"] = $gold;
        echo "Added gold image. ";
      }
      echo "<br/>";
      file_put_contents("cards/$title.json", json_encode(reorder($json)));
    }
  }


  //re-order json
  function reorder($json){
    $order = array("name", "type", "rarity", "playerClass", "set", "cost", "attack", "durability", "health", "text", "flavor", "id", "collectible", "howToGet", "howToGetGold", "mechanics", "artist", "normal", "gold");
    $ret = array();

    foreach($order as $o){
      if(isset($json[$o])){
        if($o == "text") $json[$o] = str_replace("$", "", $json[$o]);
        $ret[$o] = $json[$o];
      }
    }
    return $ret;
  }


  file_put_contents("cards/all-cards.json", json_encode($all));
  echo date('h:i:s') . " - Created cards/all-card.json<br/>";
  file_put_contents("cards/all-collectible-cards.json", json_encode($all_collectible));
  echo date('h:i:s') . " - Created cards/all-collectible-cards.json<br/>";
  echo "Complete!";
?>

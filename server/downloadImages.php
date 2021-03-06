<?php
  ini_set('display_errors', 'On');
  ini_set('memory_limit', '-1');
  ini_set('max_execution_time', 0);
  include('simple_html_dom.php');

  $saveFile = true;

  $url = "http://www.hearthpwn.com/cards?page=";
  $saveFolder = "images";

  $char = array(" ", "!", ".", "'", ":");
  $charReplace = array("_", "", "", "", "");

  for($i = 1; $i <= 11; $i+=1){
    $imageDom = file_get_html($url . $i);
    $images = $imageDom->find("td[class=visual-image-cell] a img");
    $titleDom = file_get_html($url . $i);
    $titles = $imageDom->find("td[class=visual-details-cell] h3 a");
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

      if($type == "Hero") $title .= "-hero";

      if(isset($images[$j]->attr['data-imageurl'])){
        $normal = $images[$j]->attr['data-imageurl'];
        $img = "$saveFolder/$title." . substr($normal, -3);

        if($saveFile) download($normal, $img);
        echo "<i>$normal</i> -> <b>$img</b><br/>";
      }
      if(isset($images[$j]->attr['data-gifurl'])){
        $gold = $images[$j]->attr['data-gifurl'];
        $img = "$saveFolder/$title-g." . substr($gold, -3);

        if($saveFile) download($gold, $img);
        echo "<i>$gold</i> -> <b>$img</b><br/>";
      }
    }
  }
  echo "<b>Complete!</b>";

function download($url, $location){
  $content = file_get_contents($url);
  $fp = fopen($location, "w");
  fwrite($fp, $content);
  fclose($fp);
}
?>
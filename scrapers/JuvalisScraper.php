<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * 
 * 
 * done
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
class JuvalisScraper extends KaskeScraper{
    
    const URL="http://www.juvalis.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=9;
    protected $position=1;
    protected $check_liks=[];


    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
             
               $this->save_links=true;
           /* $this->parse_links();  */
           
               if ($this->save_links) {
                $this->save_data();
             }else{
                   $this->parse_links();
             }
      }
    
    
      protected function parse_links(){
          $this->parse_marken();
          
          
         
           $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
          $box=$content->find('#header-navi',0);
          
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=  trim($a[$i]->getAttribute('href'));
              $this->position=1;
              var_dump($link);
             $sub_con=  $this->get_content($link,'#wrapper');
              if (!$sub_con) {
                  continue;
              }
               $this->parse_pagination($sub_con,$link);
                $this->parse_nav($sub_con);
              
          }
          
          
         // $this->save_data();
          
         
          
      }
      
      protected function parse_marken(){
        
          $url="http://www.juvalis.de/markenshops.html";
          $id="markenshops";
          $content=  $this->get_content(str_replace('&amp;', '&',$url),"#$id");
          $a=$content->find('a');
          
          
          for ($i = 0; $i < count($a); $i++) {
           
            
              if (!$i) {
                  continue;
              }
              $href=$a[$i]->getAttribute('href');
              var_dump($href);
              $content=  $this->get_content($href);
             if ($marken_k=$content->find('#markenshop-kategorien',0)) {
                  $am=$marken_k->find('a');
                  for ($m = 0; $m < count($am); $m++) {
                      $mhref=str_replace('&amp;', '&',$am[$m]->getAttribute('href'));
                      if (in_array($mhref, $this->check_liks)) {
                          continue;
                      }
                      $this->check_liks[]=$mhref;
                       $this->parse_marken_pag($mhref);
                  }
              }elseif($top=$content->find('#topseller',0)){
               $this->parse_topseller($top,$href);
                  
                  
              }else{
                    $this->parse_pagination($content,$href);
                    $this->parse_nav($content);
              }
              
              
             
          }
          
         
           
      }
      /**
       * 
       * @param type $con
       * @return boolean
       * 
       * 
       * 
       * 
       * 
       */
      protected function parse_topseller($con,$from){
          if (!$con) {
              return false;
          }
          $this->position=1;
          $a=$con->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=$a[$i]->getAttribute('href');
               $first=  strpos($link, '.de/')+4;
                   $fl=  substr($link, $first);  
                   $pzn= $this->strict_numbers(substr($fl, 0,  strpos($fl, '/')));
               if (in_array($pzn, $this->pzn,TRUE)) {
                   if ($this->save_links) {
                              $this->mysql->insert_links(self::ID,$link,  $this->position,$from); 
                           }
                             $this->products_url[]=[
                                 'link'     => $link,
                                 'position' =>  $this->position,
                                 'kws'      =>  '',
                                 'from'     =>$from
                             ];
                          //    var_dump($link);
                  
                       
                   }
                   ++$this->position;
              
              
              
          }
          
          
          
          
          
          
          
          
      }

      






      protected function parse_marken_pag($url){
    
    var_dump(str_replace('&amp;', '&',$url));
      $content=  $this->get_content(str_replace('&amp;', '&',$url),'#shopseite');
      if (!$content) {
          return false;
      }
      $this->position=1;
      $this->save_marken($content,$url);
        $domain=  self::URL;
      
        
           while(TRUE){
                  
                  $pagination=$content->find('.blaettern',0);
                  if (!$pagination||! $pagination->find('a')) {
                      break;
                  }
                 
                  $next=$pagination->find('a',  count($pagination->find('a'))-1);
                 $img=$next->find('img',0);
                 if (!$img||trim($img->getAttribute('src'))!=='pics/pfeil_r_.gif') {
                     break;
                 }
                  $link='http://www.juvalis.de/'.  str_replace('&amp;', '&', $next->getAttribute('href'));
                  var_dump('LInk From pagianton: '.$link);
                  $content=  $this->get_content($link,'#wrapper');
                  $this->save_marken($content, $url);
                  
              }
    
    
    
}
 


protected function save_marken($con,$from){
     
    if (!$con) {
                
        return false;
    }
    $id=$con->find('#ergebnisse',0);
    if (!$id) {
        return FALSE;
    }
    
     
    $box=$id->find('.liste2'); 
    if (!$box) {
        return false;
    }
    for ($i = 0; $i < count($box); $i++) {
            $link=$box[$i]->find('a',0)->getAttribute('href');
          //  var_dump($link);
                   $e=  explode('/', $link); 
                   $pzn= $this->strict_numbers( $e[0]);
                    //var_dump($pzn);
                   if (in_array($pzn, $this->pzn,TRUE)) {
                   if ($this->save_links) {
                                $this->mysql->insert_links(self::ID,self::URL.$link,  $this->position,$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>self::URL.$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  '',
                                 'from'     =>$from
                             ];
                           var_dump($link,$this->position,'-----------------');
                  
                       
                   }
                   ++$this->position;
        
        
        
    }
    
    
    
    
    
}












protected function parse_nav($con){
          if (!$con) {
              return false;
          }
        $nav=  $con->find('#rubrikennavi',0);
        if (!$nav) {
            return false;
        }
        
          $a=$nav->find('a');
           
          for ($i = 0; $i < count($a); $i++) {
              $link=$a[$i]->getAttribute('href');
              var_dump("parse_nav link: ".$link);
              $content=$this->get_content($link,"#wrapper");
              $this->parse_pagination($content,$link);
              $this->parse_nav($content);
          
         
          
          
          
      }

      }















      protected function parse_pagination($con,$from){
          if (!$con) {
              return false;
          }
          $this->position=1;
          $promo=$con->find('#topseller',0);
           $list=$con->find('#shopseite',0);
          if ($promo) {
             $this->save_links($con,true,$from);
          }elseif($list){ 
                $this->save_links($con,false,$from);
              while(TRUE){
                  
                  $pagination=$con->find('.blaettern',0);
                  if (!$pagination||! $pagination->find('a')) {
                      break;
                  }
                 
                  $next=$pagination->find('a',  count($pagination->find('a'))-1);
                 $img=$next->find('img',0);
                 if (!$img||trim($img->getAttribute('src'))!=='pics/pfeil_r_.gif') {
                     break;
                 }
                  $link='http://www.juvalis.de/'.  str_replace('&amp;', '&', $next->getAttribute('href'));
                  var_dump('LInk From pagianton: '.$link);
                  $con=  $this->get_content($link,'#wrapper');
                  $this->save_links($con,false,$from);
                  
              }
              
              
             
              
              
              
              
              
          }
         
          
          
          
          
          
      }


      protected function save_links($con,$promo=false,$from){
          
          if ($promo) {
              $box=$con->find('#topseller',0)->find('ul',0)->find('li');
              for ($p = 0; $p < count($box); $p++) {
                   $link=$box[$p]->find('a',0)->getAttribute('href');
                    
                   $first=  strpos($link, '.de/')+4;
                   $fl=  substr($link, $first);  
                   $pzn= $this->strict_numbers(substr($fl, 0,  strpos($fl, '/')));
                  //  var_dump($pzn);
                   if (in_array($pzn, $this->pzn,TRUE)) {
                      // $link='http://www.juvalis.de/'.$link;
                       if ($this->save_links) {
                                $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($con->find('#brotkrumen',0)),$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  $this->get_kws($con->find('#brotkrumen',0)),
                                 'from'     =>$from
                             ];
                          //   var_dump($link,$this->position,$this->get_kws($con->find('#brotkrumen',0)));
                   
                       
                   }
                   ++$this->position;
                   
                  
              }
              
              
              
              
          }else{
              $list=$con->find('#shopseite',0);
              $div=$list->find('#ergebnisse',0);
              $ul=$div->find('ul',0);
              foreach ($ul->children as $k => $v) {
                 $link=$v->find('a',0)->getAttribute('href');
               //  var_dump('else link: '.$link);
                 $first=  strpos($link, '.de/');
                   $fl=  substr($link, $first);
                   $pzn= $this->strict_numbers(substr($fl, 0,  strpos($fl, '/')));
                   
                   var_dump($pzn);
                  if (in_array($pzn, $this->pzn,TRUE)) {
                       $link='http://www.juvalis.de/'.$link;
                  
                  
                   
                if ($this->save_links) {
                               $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($con->find('#brotkrumen',0)),$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  $this->get_kws($con->find('#brotkrumen',0)),
                                 'from'     =>$from
                             ];
                      //   var_dump($link,$this->position,$this->get_kws($con->find('#brotkrumen',0)));
                      
                      
                      
                      
                      
                  } 
                 ++$this->position;
              }
           }
          
         
          return $con;
      }


 
      
      protected function save_data(){
          var_dump('save_data called');
          if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
        
          foreach ($this->products_url as $k=> $v) {
              $url=$v['link'];
              $position=$v['position'];
             $keywords_from_link=  strtolower($v['kws']);
               $from_link=$v['from_link'];
              
          
            var_dump($url);
              $con=  $this->get_content($url,'#auswahl');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#artikeldetails',0);
             
             
              
              
              $title= str_replace('&nbsp;', '', trim($content->find('.bezeichnung',0)->plaintext));
                   $first=  strpos($url, '.de/')+4;
                   $fl=  substr($url, $first);
              $pzn= $this->strict_numbers(substr($fl, 0,  strpos($fl, '/')));
              
              
              
               
              $description=$content->find('.block',3)->innertext;
             
              $infos=$content->find('.infos',0);
              $price=  $this->money_format($infos->find('.preis',0)->plaintext);
               
              $avb=$content->find('.lieferzeit',0)->find('img',0)->getAttribute('src');
              if (trim($avb)==='pics/ampel_gruen.gif') {
                  $availability=1;
              }else{
                  $availability=0;
              }
            
              $domain= self::URL;
              $link=$url;
              
               $brd=  $this->get_kws($con->find('#brotkrumen',0)).",".$keywords_from_link;
              $exp= array_unique(explode(',', $brd));
              
             $kws=  implode(',', $exp);
               
              $datetime=date("Y-m-d H:i:s");
                
            $category="";
            
            
            
           /*      var_dump(
                         $title,
                      $description,
                      $price,
                      $pzn,
                      $domain,
                      $link,
                      $availability,
                      $kws,
                      $position, 
                      $datetime,
                       $from_link,
                      [$datetime=>$price],
                      $category,
                       [$datetime=>$kws],
                      [$datetime=>$category],
                      ''
                         );
            */
              $this->mysql->insert_data(
                         $title,
                      $description,
                      $price,
                      $pzn,
                      $domain,
                      $link,
                      $availability,
                      $kws,
                      $position, 
                      $datetime,
                       $from_link,
                      [$datetime=>$price],
                      $category,
                       [$datetime=>$kws],
                      [$datetime=>$category],
                      '');
             
             
            //  break;
          }
         // var_dump($this->products_url);
  
      }
      
      
      
      
    
             protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='http://www.juvalis.de/index.php?auswahl=shopseite&aktion=Suche&rubrik3=&rubrik4=&Sprachzeile=deutsch&Portoland=&ue=&up=&suchart=&filter=&abdaDarreichung=&aA=&agb=&std_search=1&ff_sortstring=&filterHersteller=&ppp=10&usernummer=31077793-508715925&rubrik1=Suche&rubrik3=&rubrik4=&rubrik2=';
          
          
           $domain=  substr(self::URL, 0,-1);
              
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
        $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
     // $v="10333719";
         foreach ($pzns as $v) {
        $content=  $this->get_content(sprintf($search_url, $v)); 
          $headers=$this->getResponseHeaders();
         if (isset($headers['Location'])) {
             $Location=self::URL.$headers['Location'];
             var_dump($Location);
              $this->products_url[]=[
                                 'link'     =>$Location,
                                 'position' =>  0,
                                 'kws'      =>  '',
                                'from_link' =>'search'
                             ];
           }
      
  
       
       
       
              
          
               
      }
           
         
           
       //  $this->save_data();
      }
      
      
      
      
      
      
}
<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * 
 * 
 *  
 * 
 * done 3
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
class AponeoScraper extends KaskeScraper{
    
    const URL="https://www.aponeo.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=3;
    protected $position=1;



    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
               $this->save_links=true;
          /*   $this->parse_links(); */
            
              if ($this->save_links) {
                 $this->save_data();
             }else{
                   $this->parse_links();
             }  
      }
    
    
      protected function parse_links(){
           $this->parse_angelbote();
        $this->parse_marken();
          
          
          
            
          
           $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
          
          $box=$content->find('.kategorien',0);
          
           $a=$box->find('a');
           for ($i = 0; $i < count($a); $i++) {
               $kats=$a[$i]->getAttribute('href');
               $this->position=1;
                $kat_1=  $this->get_content($kats,'.kat_gruppe');
                $a1=$kat_1->find('a');
                for ($y = 0; $y < count($a1); $y++) {
                    $kats_1=$a1[$y]->getAttribute('href');
                    $this->position=1;
                       $con=  $this->parse_pagination($kats_1,$kats_1);
                    
                   while(TRUE){
                       
                       $pagination=$con->find('.paging',0);
                       if (!$pagination||!$pagination->find('.paginierung',0)->find('.text_rechts',0)) {
                           break;
                       }
                       
                       $next=$pagination->find('.paginierung',0)->find('.text_rechts',0)->find('span',0)->find('a',0);
                       if (!$next) {
                           break;
                       }
                       
                       $link=$next->getAttribute('href');
                       $con=  $this->parse_pagination($link,$kats_1);
                       
                       
                       
                       
                   }
                    
                    
                }
               
               
               
               
           }
          
         
           
           
           
           
        //  $this->save_data();
          
         
          
      }
      protected function parse_marken(){
          $url="https://www.aponeo.de/marken/";
          $content=$this->get_content($url);
          $lett=range('A','Z');
          foreach ($lett as $v) {
             $id=$content->find("#letter_$v",0);
             if (!$id) {
                 continue;
             }
             $a=$id->find('a');
             for ($i = 0; $i < count($a); $i++) {
                 $this->parse_pagination_marken($a[$i]->getAttribute('href'));
                 
             }
             
             
             
             
          }
          
          
      }
      
      protected function parse_pagination_marken($url){
          var_dump($url);
          $this->position=1; 
          $con=  $this->get_content($url);
          $this->save_marken($con,$url);
           
             while(TRUE){
                       
                       $pagination=$con->find('.paging',0);
                       if (!$pagination||!$pagination->find('.paginierung',0)->find('.text_rechts',0)) {
                           break;
                       }
                       
                       $next=$pagination->find('.paginierung',0)->find('.text_rechts',0)->find('span',0)->find('a',0);
                       if (!$next) {
                           break;
                       }
                       
                       $link=$next->getAttribute('href');
                       $con=  $this->get_content($link);
                         $this->save_marken($con, $url);
                       
                       
                       
                       
                   }
          
          
      }

      protected function save_marken($content,$from){
        
          
          if (!$content) {
              return false;
          }
          $listenblock=$content->find('#listenblock',0);
          if (!$listenblock) {
              return false;
          }
          $box=$listenblock->find('.block4');
          for ($i = 0; $i < count($box); $i++) {
              $a=$box[$i]->find('a',0);
              $info=$box[$i]->find('.info',0); 
               $e=explode('PZN:',$info->plaintext);
                $pzn=  $this->strict_numbers($e[1]);
                $link=$a->getAttribute('href');
               if (in_array($pzn, $this->pzn,TRUE)) { 
                  if ($this->save_links) {
                                 $this->mysql->insert_links(self::ID,$link, $this->position,'',$from); 
                           }
                               $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>'',
                                   'from_link'=>$from
                             ];
                          // var_dump($link,$this->position);
            
              } 
              
                ++$this->position;
              
              
          }
          
          
          
      }

      


      protected function parse_angelbote(){
          $url=$from="https://www.aponeo.de/produkte.html#angebote";
          $id="#listenblock";
          
          $content=  $this->get_content($url);
          
          
          $tab_angebote=$content->find('#tab_angebote',0);
          if ($tab_angebote) {
              $this->position=1;
              $box=$tab_angebote->find('.block4m');
              for ($i = 0; $i < count($box); $i++) {
                 $a=$box[$i]->find('a',0);
                 if ($a) {
                     
               
                  $link=$box[$i]->find('a',0)->getAttribute('href');
            
                  
                
                  $info=$box[$i]->find('.info',0);
                  $e=explode('PZN:',$info->plaintext);
                  $pzn=  $this->strict_numbers($e[1]);
                   
              if (in_array($pzn, $this->pzn,TRUE)) { 
                  if ($this->save_links) {
                                   $this->mysql->insert_links(self::ID,$link, $this->position,'',$from); 
                           }
                               $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>'',
                                   'from_link'=>$from
                             ];
                        //  var_dump($link,$this->position);
            
              } 
              
                ++$this->position;
                  }
            
                  
              }
              
              
              
          }
          $tab_topseller=$content->find('#tab_topseller',0);
          if ($tab_topseller) {
              $this->position=1;
              $this->position=1;
              $box=$tab_topseller->find('.block4m');
              for ($i = 0; $i < count($box); $i++) {
                 $a=$box[$i]->find('a',0);
                 if ($a) {
                     
               
                  $link=$box[$i]->find('a',0)->getAttribute('href');
            
                  
                
                  $info=$box[$i]->find('.info',0);
                  $e=explode('PZN:',$info->plaintext);
                  $pzn=  $this->strict_numbers($e[1]);
                   
              if (in_array($pzn, $this->pzn,TRUE)) {
                  if ($this->save_links) {
                                   $this->mysql->insert_links(self::ID,$link, $this->position,'',$from); 
                           }
                               $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>'',
                                   'from_link'=>$from
                             ];
                        //   var_dump($link,$this->position);
            
              }    ++$this->position;
                  }
              }
              
              
              
          }
          $tab_neuheiten=$content->find('#tab_neuheiten',0);
          if ($tab_neuheiten) {
              $this->position=1;
              $box= $tab_neuheiten->find('.block4m');
              for ($i = 0; $i < count($box); $i++) {
                 $a=$box[$i]->find('a',0);
                 if ($a) {
                     
               
                  $link=$box[$i]->find('a',0)->getAttribute('href');
            
                  
                
                  $info=$box[$i]->find('.info',0);
                  $e=explode('PZN:',$info->plaintext);
                  $pzn=  $this->strict_numbers($e[1]);
                   
              if (in_array($pzn, $this->pzn,TRUE)) {
                  if ($this->save_links) {
                                   $this->mysql->insert_links(self::ID,$link, $this->position,'',$from); 
                           }
                               $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>'',
                                   'from_link'=>$from
                             ];
                           var_dump($link,$this->position);
            
              }   ++$this->position;
                  }
              }
              
              
              
          }
          
          
          
          
          
          
          
          
      }

      
      protected function parse_pagination($url,$from){
          var_dump($url);
          $content=  $this->get_content($url,'#content_line1');
           $listen_bloock=$content->find('#listenblock',0);
           $block6=$listen_bloock->find('.block6',0);
           if ($block6) {
                  $box=$block6->find('.liste_eintrag');
          
          for ($i = 0; $i < count($box); $i++) {
              $product=$box[$i]->find('.links',0);
              if (!$product) {
                  continue;
              }
                $exp=  explode('PZN:', $product->find('.info',0)->plaintext);
                        $pzn=  $this->strict_numbers(trim($exp[1]));
              
              if (in_array($pzn, $this->pzn,TRUE)) {
                  $link=$box[$i]->find('.titel',0)->find('a',0)->getAttribute('href');
                    if (strpos($link, '/informationen/')===false&&  strpos($link, 'javascript:')===FALSE) {
                  
                  
                   if ($this->save_links) {
                                   $this->mysql->insert_links(self::ID,$link, $this->position,'',$from); 
                           }
                               $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>'',
                                   'from_link'=>$from
                             ];
                       //    var_dump($link,$this->position);
              } 
              }
              ++$this->position;
              
              
          }
           }else{
               
               $block12=$listen_bloock->find('.block12',0);
               
               $box=$block12->find('.big_kachel');
              for ($i = 0; $i < count($box); $i++) {
                        $product=$box[$i]->find('.links',0);
                        if (!$product) {
                            continue;
                        }
                        $exp=  explode('PZN:', $product->find('.info',0)->plaintext);
                        $pzn=  $this->strict_numbers(trim($exp[1]));

                        if (in_array($pzn, $this->pzn,TRUE)) {
                            $link=$box[$i]->find('.titel',0)->find('a',0)->getAttribute('href');
                              if (strpos($link, '/informationen/')===false&&  strpos($link, 'javascript:')===FALSE) {

                            
                             if ($this->save_links) {
                                          $this->mysql->insert_links(self::ID,$link,$this->position,''); 
                                     }
                                $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position
                             ];
                            var_dump($this->position);
                        } 
                        }
                         ++$this->position;
               }
           
           
           }
           
           
           
           
       
          
          
          
          return $content;
          
      }
    
    
      
      protected function save_data(){
          var_dump('save_data called');
           if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
       
         // var_dump($this->products_url);
          foreach ($this->products_url as $v) {
                 $url=$v['link'];
              $position=$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
              $from_link=$v['from_link'];
              
              
              var_dump($url);
              
              
              
              
              
              
              $content=  $this->get_content($url,'body');
             
          $title=  trim($content->find('#seitentitel',0)->plaintext);
              
              
              $description=$content->find('#tab_content1',0)->innertext;
               $e=  explode('.de/', $url);
               $p= explode('-', $e[1]);
               $pzn=  trim($p[0]);
                 
              $price= $this->money_format($content->find('.hauptinfo_preis',0)->plaintext);
            
              $domain= self::URL;
              $link=$url;
              
              $availability=1;
              $brd=  $this->get_kws($content->find('#breadcrumb',0));
              $exp= array_unique(explode(',', $brd));
              
             $kws=  implode(',', $exp);
             
             
             $kk=  str_replace('&raquo;', '>', $content->find('#breadcrumb',0)->plaintext );
            $category= trim(str_replace('  > ', '>', $kk));
            
              
              $datetime=date("Y-m-d H:i:s");
            //  $cross_seling=$this->cross_seling($content);
              $cross_seling="";
              
           /*      
              var_dump( $title,
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
                      $cross_seling);
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
                      $cross_seling);
            
              
            //  break;
          }
         // var_dump($this->products_url);
  
      }
      
      
      
  
    
      protected function cross_seling($c){
           
          $arr=[];
          if (!$c->find('#productjson2',0)) {
              return "";
          }
          
          $cross=$c->find('#productjson2',0)->find('a');
          if ($cross) {
                for ($i = 0; $i < count($cross); $i++) {
                     $href=$cross[$i]->getAttribute('href');
                     
             
              }
          
          
          }
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
        protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.aponeo.de/suche/?q=';
          
          
           $domain=  substr(self::URL, 0,-1);
                 $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
      $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
       $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
           foreach ($pzns as $v) {
         
          $content=  $this->get_content($search_url.$v); 
         $headers=$this->getResponseHeaders();
         if (isset($headers['Location'])) {
             $Location=$headers['Location'];
             var_dump($Location);
              $this->products_url[]=[
                                 'link'     =>$Location,
                                 'position' =>  0,
                                 'kws'      =>  '',
                                'from_link' =>'search'
                             ];
             
             
         }else{
           //  var_dump($headers);
           //  var_dump("Pzn: $v");
            // throw new Exception("Location is not set");
         }
         
         
       
       
       
              
          
               
        }
           
         
          
          
          
          
         $this->save_data();
      }
      
      
      
    
}


 

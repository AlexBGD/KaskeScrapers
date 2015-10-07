<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * 
 *  done 3
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
class DelmedScraper extends KaskeScraper{
    
    const URL="http://www.delmed.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=6;
    protected $position=1;
    protected $check_links=[];


    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
             
               $this->save_links=true;
          /*  $this->parse_links();*/
            if ($this->save_links) {
                  $this->save_data();
             }else{
                   $this->parse_links();
             }  
      }
    protected function parse_main_nav($a){
        $domain=  substr(self::URL, 0,-1);
        for ($i = 0; $i < count($a); $i++) {
               $href=$a[$i]->getAttribute('href');
               $content=  $this->get_content($href);
               
               $md9=$content->find('.col-md-9',0);
               if ($md9) {
                   $this->parse_pagination($href);
               }elseif ($row8=$content->find('.row8',0)) {
                    $this->parse_pagination($href);
                }elseif($con=$content->find('.content__container',0)){
                    $ca=$con->find('a');
                    for ($y = 0; $y < count($ca); $y++) {
                        $href1=$domain.$ca[$y]->getAttribute('href');
                        $c1=  $this->get_content($href1);
                        if (!$c1) {
                            continue;
                        }
                        $con11=$c1->find('.col-md-9',0);
                        $c1a=$con11->find('a');
                        for ($x = 0; $x < count($c1a); $x++) {
                            $this->parse_pagination($c1a[$x]->getAttribute('href')); 
                        }
                        
                    }
                    
                }
                
                
                
                
            }
        
        
        
        
    }


    protected function parse_links(){
           $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
            
            
            
            $manu=$content->find('.nav--main',0);
            $this->parse_main_nav($manu->find('a'));
             
          $box=$content->find('.category-list',0);
          
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=  trim( $a[$i]->getAttribute('href'));
              if (in_array($link, $this->check_links)) {
                  continue;
              }
             // var_dump('-------------------------');
             // var_dump($link);
           //  var_dump('-------------------------');
           $this->parse_pagination($link);
              $cat_con=  $this->get_content($link);
              $this->sub_content($cat_con);
              
              
              
              
              
          }
          
          
         // $this->save_data();
          
         
          
      }
      
      protected function sub_content($con){
          if (!$con) {
              return false;
          }
                $page=$con->find('.page',0);
              $main_con=$page->find('.col-md-9',0);
              $kats=$main_con->find('.hidden-xs',0);
              $cat_a=$kats->find('a');
          
                 for ($y = 0; $y < count($cat_a); $y++) {
                  $cat_a_links=$cat_a[$y]->getAttribute('href');
                   if (in_array($cat_a_links, $this->check_links)) {
                  continue;
              }
              $this->check_links[]=$cat_a_links;
                  var_dump($cat_a_links);
                   $con=  $this->parse_pagination($cat_a_links);
                  $con=  $this->get_content($cat_a_links);
                  $this->sub_content($con);
                  
                  
                 
                  
          
            
                }
          
          
          
      }

      
      
      protected function save_links($url,$from){
             
          $content=  $this->get_content($url,'.page');
          if (!$content) {
              return false;
          }
          $products=$content->find('.products',0);
          if (!$products) {
              return false;
          }
          
          $row8=$products->find('.row-8',0);
          if (!$row8) {
              return false;
          }
           $div=$row8->find('.match__height');
           if (!$div) {
               return false;
           }
          
           
           for ($i = 0; $i < count($div); $i++) {
                $c=$div[$i];
            //   $con=$c->find('.content',0);
               $img=$c->find('img',0)->getAttribute('src');
               $e=  explode('/Listenansicht/', $img);
               $pzn=  $this->strict_numbers($e[1]);
               
               if (in_array($pzn, $this->pzn,TRUE)) {
                   $link=$c->find('a',0)->getAttribute('href');
                  
                    if ($this->save_links) {
                                $this->mysql->insert_links(self::ID,$link,  $this->position,'',$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                       'kws'      =>  '',
                                 'from_link'    =>$from
                             ];
                        //    var_dump($link,$this->position);
                  
               }
               
               ++$this->position;
           }
           
           
           return $content;
          
        
          
      }

       

      protected function parse_pagination($url){
          var_dump($url);
          
          
            $this->position=1;
          $con=$this->save_links($url, $url);
           
              
              
           
              while(TRUE){
                  if (!$con) {
                      break;
                  }
                  $pagination=$con->find('.pagination',0);
                  if (!$pagination) {
                      break;
                  }
                  $next=$pagination->find('.next',0);
                  if (!$next) {
                      break;
                  }
                  $link1=$next->getAttribute('href');
                  var_dump($link1);
                  $con=  $this->save_links($link1,$url);
                  
                  
              }
              
            
             
        
      }
    
    
      
      protected function save_data(){
          var_dump('save_data called');
          if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
          //$this->products_url=  array_unique($this->products_url);
        
          foreach ($this->products_url as $k=> $v) {
              $url=$v['link'];
              $position=$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
               $from_link=$v['from_link'];
               var_dump($url );
          
              $con=  $this->get_content($url,'.wrapper');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#details',0);
              if (!$content) {
                  continue;
              }
              $info=$content->find('.details__info',0);
              if (!$info) {
                  continue;
              }
              $title=  str_replace('&nbsp;', '', $info->find('h2',0)->plaintext);
              $pzn=  $this->strict_numbers($info->find('table',0)->find('tr',0)->find('td',1)->plaintext);
              $description=$content->find('.information',0);
              if ($description) {
                  $this->remove_el($description, [
                      'img' =>  range(0, 10)
                  ]);
                  $description=$description->innertext;
              }else{
                  $description=$con->find('#dtab1',0);
                  if ($description) {
                      $description=$description->innertext;
                  }else{
                      $description="";
                  }
              }
              
              $price=  $this->money_format($content->find('.product__price',0)->plaintext);
               
    
          
              $domain= self::URL;
              $link=$url;
              
              $availability=1;
              $brd=  $this->get_kws($con->find('.nav--breadcrumb',0)).",".$keywords_from_link;
              $exp= array_unique(explode(',', $brd));
              $kws=  implode(',', $exp);
             
              $datetime=date("Y-m-d H:i:s");
              
              
              
              $kk=$con->find('.nav--breadcrumb',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk); $i++) {
                  $cat.=trim($this->only_letters_num_spaces($kk[$i]->plaintext)).'>';
              }
              $category=substr($cat, 0,-1);
              
                $cross_seling=$this->cross_seling($con);
           //     var_dump($cross_seling);
       /*           
            var_dump( 
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
                      $cross_seling
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
                      $cross_seling);
              
              
             
            //  break;
          }
         // var_dump($this->products_url);
  
      }
      
      
       
          protected function cross_seling($c){
           
          $arr=[];
          if (!$s=$c->find('.s-30')) {
              return "";
          }
          
          for ($i = 0; $i < count($s); $i++) {
              $imgs=$s[$i]->find('img');
              for ($y = 0; $y < count($imgs); $y++) {
                  $src=$imgs[$y]->getAttribute('src');
                  if (strpos($src, '/Listenansicht/')!==FALSE) {
                      $e=  explode('/Listenansicht/', $src);
               $pzn=  $this->strict_numbers($e[1]);
               $arr[]=$pzn;
                  }
                 
              }
              
              
              
              
              
          }
         
          
          $arr=  array_filter(array_unique($arr));
          
           
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
      protected function valid_pzn($con){
          if (!$con||!$con->find('h2',0)) {
              return false;
          }
          $pzn=trim($con->find('h2',0)->plaintext);
          
          $e= array_map('trim',explode('PZN:', $pzn));
          $p=array_map('trim',  explode(' ', $e[1]));
          $pzn=$p[0];
          if (in_array($pzn, $this->pzn)) {
              return $pzn;
          }
          return false;
          
          
          
          
      }
      
      
       
        protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.delmed.de/search.html?searchtext=%s&searchfield_submit=1';
          
          
           $domain=  substr(self::URL, 0,-1);
              
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
      $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
      
         foreach ($pzns as $v) {
        
          $content=  $this->get_content(sprintf($search_url,$v));
          if (!$content) {
              continue;
          }
          $product=$content->find('.product__box',0);
          if ($product&&$a=$product->find('a',0)) {
              $href=$a->getAttribute('href');
              var_dump($href);
              $this->products_url[]=[
                                 'link'     =>$href,
                                 'position' =>  0,
                                 'kws'      =>  '',
                                'from_link' =>'search'
                             ];
           }
      
  
       
       
       
              
          
               
       }
           
         
          
          
          
          
         $this->save_data();
      }
      
      
      
      
      
      
    
}
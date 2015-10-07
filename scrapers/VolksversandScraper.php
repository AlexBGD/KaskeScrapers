<?php
include './KaskeScraper.php';
 
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
class VolksversandScraper extends KaskeScraper{
    
    const URL="http://volksversand.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=12;
    protected $position=1;
    protected $check_links=[];
    protected $all_urls=[];

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
              /*  $this->parse_links(); */
             
              if ($this->save_links) {
                 $this->save_data();
             }else{
                   $this->parse_links();
             } 
      }
    
    
      protected function parse_links(){
           $get=  $this->get(self::URL.'arzneimittel/');
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
            $box=$content->find('#left',0)->find('ul',0)->find('.active',0);
          
          
            
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              
              $link=  trim( $a[$i]->getAttribute('href'));
             
              //  var_dump($link);
               $con=  $this->get_content($link);
               $wrapper=$con->find('#wrapper',0);
               $this->parse_sub_links($wrapper);
               $this->parse_pagination($link);
               $this->check_links[]=$link;
                  
          }
       
         // $this->parse_pagination($this->check_links);
      //  var_dump($this->check_links);
          
         
          
      }
      
      
      protected function parse_sub_links($con){
          if (!$con) {
              return false;
          }
          $box=$con->find('#left',0)->find('ul',0)->find('.active',0);
         $active=$box->find('.active');
         if (!$active) {
             return false;
         }
         for ($i = 0; $i < count($active); $i++) {
             $act_con=$active[$i]->find('a');
             for ($y = 0; $y < count($act_con); $y++) {
                 $link=$act_con[$y]->getAttribute('href');
                 if (in_array($link, $this->check_links,TRUE)) {
                     continue;
                 }
                 $this->check_links[]=$link;
                 var_dump($link);
                 $c=  $this->get_content($link,'#wrapper');
                 $this->parse_pagination($link);
                 $this->parse_sub_links($c);
                 
             }
             
             
             
             
         }
          
          
          
      }

 
      
      protected function parse_pagination( $url){
          if (in_array($url, $this->all_urls )) {
              return FALSE;
          }
      $this->all_urls[]=$url;
           $content=  $this->get_content($url,'#wrapper');
           if (!$content) {
              return false;
           }
           $this->position=1;
          $save= $this->save_links($content,$url);
           while(true){
               
               $pagination=$save->find('.paging',0);
               if (!$pagination) {
                   break;
               }
               $next=$pagination->last_child();
               if (!$link=$next->getAttribute('href')) {
                   break;
               }
               var_dump($link);
               $save=  $this->get_content($link,'#wrapper');
               $this->save_links($save,$url);
               
               
         
           
           
           
           
           
       }
          
          
          
          
          
          
          
      } 

            protected function save_links($content,$from){
                              
                    $center=$content->find('#center',0);
                    $listing=$center->find('#listing-3col',0);
                    $box=$listing->find('.artbox');
                    for ($i = 0; $i < count($box); $i++) {
                        $link=$box[$i]->find('a',0)->getAttribute('href');
                        if (strpos($link, 'pzn-')){
                            $pzn= array_map('trim', explode('-', substr($link, strpos($link, 'pzn-')+4))) ;
                            $pzn=  $this->strict_numbers($pzn[0]);
                            if (in_array($pzn, $this->pzn,TRUE)) {
                                
                         
                            if ($this->save_links) {
                                          $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('#breadcrumb',0)),$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('#breadcrumb',0)),
                                            'from_url'  =>$from
                                        ];
                                      //   var_dump($link,  $this->position,$pzn,$from);
                            }
                        }



                        ++$this->position;

                    }
           
                
                    return $content;
          
          
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
          
             
               
               
              //  continue;
              $con=  $this->get_content($url,'#wrapper');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#content',0);
              if (!$content) {
                  continue;
              }
          $box=$content->find('#detailbox',0);
              if (!$box) {
                  continue;
              }
              $title=  trim($box->find('h1',0)->plaintext);
              
              
              
              $pzn =$this->strict_numbers($box->find('#detail_menu',0)->find('li',1)->find('.newDetailText',0)->plaintext);
              
              
              
              if ($content->find('.article_details_price2',0)) {
                   $price=  $this->money_format($content->find('.article_details_price2',0)->find('strong',0)->plaintext);
              }else{
                   $price=  $this->money_format($content->find('.article_details_price',0)->find('strong',0)->plaintext);
              }
             
              
              $description=$content->find('#description',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
              
              $buy_box=$content->find('#buybox',0);
              $status=$buy_box->find('.status2',0);
              if ($status) {
                  $availability=1;
              }else{
                  $availability=0;
              }
              
             
             $kws=   $this->get_kws($con->find('#breadcrumb',0)).",".$keywords_from_link;
             
              
             
             
              $kk=$con->find('#breadcrumb',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk)-1; $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
             
            //  var_dump($category);
             
             
             
              $datetime=date("Y-m-d H:i:s");
               $cross_seling=  $this->cross_seling($con);
               
           /*
              var_dump(   $title,
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
          if (!$s=$c->find('.bought-slider',0)) {
              return "";
          }
          
          
              $a=$s->find('a');
              for ($y = 0; $y < count($a); $y++) {
                  $href=$a[$y]->getAttribute('href');
                  if (!$href) {
                      continue;
                  }
                  $exp=  explode('-pzn-', $href);
                  if (isset($exp[1])) {
                     $e=  explode('-', $exp[1]);
                      $arr[]=$e[0];
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
      
      
      
      
      
      
      
      
    
}
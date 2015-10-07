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
class MediherzScraper extends KaskeScraper{
    
    const URL="http://www.mediherz-shop.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=20;
    protected $position=1;
    protected $check_links=[];
    protected $all_urls=[];

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
              /*   $this->parse_links();*/
             
             if ($this->save_links) {
                 $this->save_data();
             }else{
                   $this->parse_links();
             }  
      }
    
    
      protected function parse_links(){
           $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
            $box=$content->find('#menu',0);
          
          
            
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              
              $link=  trim( $domain.$a[$i]->getAttribute('href'));
              var_dump($link);
              $this->parse_pagination($link);
              
                  
          }
       
   
          
         
          
      }
      
      
 
      
      protected function parse_pagination( $url){
          if (in_array($url, $this->check_links)) {
              return false;
          }
          $this->check_links[]=$url;
           $content=  $this->get_content($url,'#content_container');
           if (!$content) {
              return false;
           }
           $this->position=1;
          $save= $this->save_links($content,$url);
        $domain=  substr(self::URL, 0,-1);
          
         while(true){
             if (!$content) {
                 break;
             }
               $pagination=$content->find('.page-lst',0);
               if (!$pagination) {
                   break;
               }
               $next=$pagination->find('.btnNext',0);
               if (!$next) {
                   break;
               }
               $link=$domain.$next->getAttribute('href');
               var_dump('From pagiantion: '.$link);
               $content=  $this->get_content($link,'#content_container');
               $this->save_links($content,$url);
            }
          
          
          
          
          
          
          
      } 

            protected function save_links($content,$from){
                if (!$content) {
                    return false;
                }
                
                $wrap=$content->find('.productsList',0);
                if (!$wrap) {
                    return false;
                }
                
                    $domain=  substr(self::URL, 0,-1);
                    $box=$wrap->find('.product');
                    for ($i = 0; $i < count($box); $i++) {
                        $link=$domain.$box[$i]->find('a',0)->getAttribute('href');
                        $pzn=  $this->strict_numbers($box[$i]->find('.pzn',0)->plaintext);
                        if (in_array($pzn, $this->pzn,TRUE)) {
                           if ($this->save_links) {
                                          $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('.navigatorBox',0)),$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('.navigatorBox',0)),
                                            'from'      =>$from
                                        ];
                                       //  var_dump($link,  $this->position,$pzn,$this->get_kws($content->find('.navigatorBox',0)));
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
              $con=  $this->get_content($url,'#container');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#productDetail',0);
             
          
              $title=  trim($content->find('h1',0)->plaintext);
              
              
              
              $pzn =$this->strict_numbers($content->find('.pzn',1)->plaintext);
              
              
                   $price=  $this->money_format($content->find('.yourPrice',1)->plaintext);
             
                   
                  
              $status3=$content->find('.status3',0);
              if ($status3) {
                  $availability=0;
              }else{
                  $availability=1;
              }
                   
                   
                   
              
              $description=$content->find('.description',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
              
            
              
             
              $kws=  $this->get_kws($con->find('.navigatorBox',0));
           //   var_dump($kws);
            $e= array_map('trim',  explode('&amp;', $con->find('.navigatorBox',0)->plaintext));
            $ex=$con->find('.navigatorBox',0)->find('a');
            $category="";
            for ($i = 0; $i < count($ex); $i++) {
                $category.=$ex[$i]->plaintext.">";
            }
            $category=  substr(trim($category), 0,-1);
          //  var_dump($category);
              $datetime=date("Y-m-d H:i:s");
              $cross_seling=$this->cross_seling($con);
          //    var_dump($cross_seling);
        
          /*       var_dump(     $title,
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
          if (!$cross=$c->find('.productDetailOther',0)) {
              return "";
          }
         
          $cross=$cross->find(".pzn");
                for ($i = 0; $i < count($cross); $i++) {
                    $pzn=  $this->strict_numbers($cross[$i]->plaintext);
               $arr[]=$pzn; 
             
              }
          
          
         
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
     
      
      
      
      
      
    
}
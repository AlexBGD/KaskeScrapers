<?php
include './KaskeScraper.php';
 
/*
 * 
 * 
 * 
 * 
 *  
 * 
 *  done
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
class ShopApothekeScraper extends KaskeScraper{
    
    const URL="http://www.shop-apotheke.com/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=19;
    protected $position=1;
    protected $check_links=[];
    

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
            /*      $this->parse_links(); */
           
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
            $box=$content->find('.to_be_pulled_down',0);
            $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
               $link=  trim($a[$i]->getAttribute('href'));
               var_dump($link);
                $c=  $this->get_content($link,'body');
                 $this->parse_pagination($c,$link);
               $this->parse_sub_content($c);
                
               
          }
       
          
         
          
      }
       
      
      protected function parse_sub_content($con){
          if (!$con) {
              return FALSE;
          }
         $box=$con->find('#newCategoryList',0);
         if (!$box) {
             return false;
         }
         
         
          $domain=  substr(self::URL, 0,-1);
          $a=$box->find('a');
             for ($i = 0; $i < count($a); $i++) {

                $link=$a[$i]->getAttribute('href');

                if ( in_array($link, $this->check_links)) {
                    continue;
                  }
                  var_dump($link); 
                  $this->check_links[]=$link;
                $c=  $this->get_content($link,'body');
                $this->parse_pagination($c,$link);
                $this->parse_sub_content($c);

              
           
             
         }
          
          
          
          
          
          
          
          
          
      }

      



      protected function parse_pagination( $con,$from){
                   
          if (!$con) {
              return FALSE;
          }
           
          $this->position=1;
           $domain=  substr(self::URL, 0,-1);
          $this->save_links($con,$from);
          
          
          
           
           while (TRUE){
              
              $pagination=$con->find('#pagenavigation',0);
              if (!$pagination) {
                  break;
              }
              
              $next=$pagination->first_child();
              if (!$next) {
                  break;
              }
               $link= $next->getAttribute('href');
               if (!$link) {
                   break;
               }
               var_dump('from pagiantion: '.$link);
              $con=  $this->get_content($link,'body');
              $this->save_links($con,$from);
              
              
              
              
              
              
              
          }
         
          
           
           
           
       }
          
          
          
          
          
          
       protected function save_links($content,$from){
           if (!$content) { 
               return false;
           }
                            $products_con=$content->find('.grList',0);
                            if (!$products_con) {
                                return false;
                            }
                     $box=$products_con->find('.listEntry');
                     if (!$box) {
                         return false;
                     }
                    
                     
                     $domain=  substr(self::URL, 0,-1);
                    for ($i = 0; $i < count($box); $i++) {  
                        $link= $box[$i]->find('a',0)->getAttribute('href');
                        
                        $pzn=  $this->strict_numbers($box[$i]->find('.product-pzn',0)->plaintext);
                       
                       
                        if (in_array($pzn, $this->pzn,TRUE)) {
                            if ($this->save_links) {
                                           $this->mysql->insert_links(self::ID,$link,  $this->position, '',$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  '',
                                            'from_url'  =>$from
                                        ];
                                          //    var_dump($link,  $this->position,$pzn,'');
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
              $con=  $this->get_content($url,'#content');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#productDetailsContent',0);
             
     
              
              $title=  trim($content->find('h1',0)->plaintext);
              
              $pzn =$this->strict_numbers($content->find('#productPZN',0)->plaintext);
               
            $price=  $this->money_format($content->find('table',0)->find('tr',0)->plaintext);
                  
           $status=$content->find('.font-green',0);
           if ($status) {
               $availability=1;
           }else{
                   $availability=0;
                 }
                  
                
              $description=$con->find('.content',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
             
              
             
              $kws=  $this->get_kws($con->find('#breadCrumb',0)) ;
      
               
                 $kk=$con->find('#breadCrumb',0)->find('a') ;
              $cat='';
              for ($i = 0; $i < count($kk); $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
              
              $datetime=date("Y-m-d H:i:s");
               $cross_seling=$this->cross_seling($con);
          //    var_dump($cross_seling);
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
                
           
          }
 
  
      }
      
      
      
     
             protected function cross_seling($c){ 
           
              $cross=$c->find('.econda_buy',0);
          
              $arr=[];
              if ($cross) {
                  $a=$cross->find('a');
                  for ($i = 0; $i < count($a); $i++) {
                      $href=$a[$i]->getAttribute('href');
                      $e=  explode('=', $href);
                      $pzn= $this->strict_numbers(array_pop($e));
                      if (strlen($pzn)>4) {
                          $arr[]=$pzn;
                      }
                     // $e=  $this->strict_numbers($a[$i]->plaintext);
                    //  
                   
                  }
                  
                  
              }
           
         
       
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
      
      
      
      
    
}
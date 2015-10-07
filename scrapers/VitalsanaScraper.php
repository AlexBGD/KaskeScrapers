<?php
include './KaskeScraper.php';
 
/*
 * 
 * 
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
 */
class VitalsanaScraper extends KaskeScraper{
    
    const URL="http://www.vitalsana.com/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=22;
    protected $position=1;
    protected $check_links=[];
    protected $all_urls=[];

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
            /*   $this->parse_links(); */
                
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
            $box=$content->find('.categoryroot',0);
          
          
            
            
            
            
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              
              $link=  trim( $domain.$a[$i]->getAttribute('href'));
               if (in_array($link, $this->check_links)) {
                  continue;
              }
              var_dump($link);
               $this->parse_pagination($link);
               $this->check_links[]=$link;
              $c=  $this->get_content($link);
             $this->parse_sub_content($c);
                  
          }
       
   
          
         
          
      }
      
      
      protected function parse_sub_content($c){
          if (!$c) {
              return false;
          }
          $cat=$c->find('.productcategory',0);
          if (!$cat) {
              return false;
          }
          $a=$cat->find('a');
          $domain=  substr(self::URL, 0,-1);
          for ($i = 0; $i < count($a); $i++) {
              $link=$domain.$a[$i]->getAttribute('href');
              if (in_array($link, $this->check_links)) {
                  continue;
              }
              var_dump($link);
              $this->check_links[]=$link;
               $this->parse_pagination($link);
              
              
          }
          
          
          
          
          
          
          
          
          
      }

            protected function parse_pagination( $url){
         
         
           $content=  $this->get_content($url,'body');
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
               $pagination=$content->find('.pagination',0);
               if (!$pagination) {
                   break;
               }
               $next=$pagination->find('ul',0);
               if (!$next) {
                   break;
               }
               if (!$next->last_child()) {
                   break;
               }
               $link=$next->last_child()->find('a',0);
               if (!$link) {
                   break;
               }
             
              
               $link=$domain.$link->getAttribute('href');
               var_dump('From pagiantion: '.$link);
               $content=  $this->get_content($link,'body');
               $this->save_links($content,$url);
            }
          
          
          
          
          
          
      } 

            protected function save_links($content,$from){
                if (!$content) {
                    return false;
                }
                 
                $wrap=$content->find('#productList',0);
                if (!$wrap) {
                    return false;
                }
              
                
                    $domain=  substr(self::URL, 0,-1);
                    $box=$wrap->find('.productteaser' );
                    
                    for ($i = 0; $i < count($box); $i++) {
                       $link=$domain.$box[$i]->find('a',1)->getAttribute('href');
                     
                           if ($this->save_links) {
                                        $this->mysql->insert_links(self::ID,$link,  $this->position,'',$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  '',
                                            'from_url'  =>$from
                                        ];
                                   //     var_dump(  $this->position);
                           


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
              $con=  $this->get_content($url,'body');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('.productDescriptionExtensive',0);
             
          
              
              
              
              
              
             $d= $con->find('#productDetails',0)->plaintext;
              $e=  explode('PZN', $d);
              if (!isset($e[2])) {
                  continue;
              }
              $pzn=  $this->strict_numbers($e[2]);
              var_dump($pzn);
              if (!in_array($pzn, $this->pzn,TRUE)) {
                  continue;
              }
             
              
              
              
              
              $title=  trim($content->find('.productname',0)->plaintext);
              
              
              
             
              
              
                   $price= str_replace('...', '',  $this->money_format($content->find('.price',0)->plaintext));
             
                   
                  
              $status=$con->find('.articleAvailable',0);
              if ($status) {
                  $availability=1;
              }else{
                  $availability=0;
              }
                   
                   
                   
              
              $description=$con->find('#productDetails',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
              
            
              
             
              $kws=   $this->get_kws($con->find('.breadcrumb',0));
            
              
               
            
             
              $kk=$con->find('.breadcrumb',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk)-1; $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
           //  var_dump($category);
             
             
              $datetime=date("Y-m-d H:i:s");
              $cross_seling="";
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
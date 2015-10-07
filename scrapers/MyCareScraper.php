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
class MyCareScraper extends KaskeScraper{
    
    const URL="https://www.mycare.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=16;
    protected $position=1;
    protected $check_links=[];
    

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
              /*    $this->parse_links(); */
            
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
            $box=$content->find('.all-categories',0);
            $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
               $link=  trim($a[$i]->getAttribute('href'));
             
              if ($link==='/online-shop') {
                  continue;
              }
              $link=$domain.$link;
           //   var_dump($link);
                $c=  $this->get_content($link,'body');
             
                $this->parse_sub_content($c);
                 if (!in_array($link, $this->check_links)) {
                     $this->check_links[]=$link;
                     
                 }
                
               
          }
       
          
         
          
      }
       
      
      protected function parse_sub_content($con ){
          if (!$con) {
              return FALSE;
          }
          $nav=$con->find('.facetNavigation',0);
          if (!$nav) {
              return false;
          }
        
          $a=$nav->find('ul',0)->find('a');
          $domain=  substr(self::URL, 0,-1);
          for ($i = 0; $i < count($a); $i++) {
              
              $link=$domain.$a[$i]->getAttribute('href');
              
              if ( in_array($link, $this->check_links)) {
                  continue;
                }
                var_dump($link); 
                $this->check_links[]=$link;
              $c=  $this->get_content($link,'#content');
              $this->parse_pagination($c,$link);
              $this->parse_sub_content($c );
              
              
              
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
              
              $pagination=$con->find('.pagination',0);
              if (!$pagination) {
                  break;
              }
              
              $next=$pagination->find('.next',0);
              if (!$next) {
                  break;
              }
              
               $link=$domain.$next->find('a',0)->getAttribute('href');
               var_dump('from pagiantion: '.$link);
              $con=  $this->get_content($link,'#content');
              $this->save_links($con,$from);
              
              
              
              
              
              
              
          }
         
         
          
           
           
           
       }
          
          
          
          
          
          
       protected function save_links($content,$from){
                            $products=$content->find('.span-9',0); 
                            if (!$products) { 
                                return false;
                            }
                            
                            
                            
                            
                            
                            $greed=$products->find('.productGrid',0);
                            if (!$greed) {
                                return false;
                            }
                     $box=$greed->find('.span-2');
                    
                    
                     
                     $domain=  substr(self::URL, 0,-1);
                    for ($i = 0; $i < count($box); $i++) {  
                        $link=$domain.$box[$i]->find('a',0)->getAttribute('href');
                     
                        $e=  explode('-', $link);
                        $pzn=  $this->strict_numbers(array_pop($e));
                        
                        if (in_array($pzn, $this->pzn,TRUE)) {
                            if ($this->save_links) {
                                           $this->mysql->insert_links(self::ID,$link,  $this->position, 
                                             $this->get_kws($content->find('#breadcrumb',0)),$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('#breadcrumb',0)),
                                            'from_url'  =>$from
                                        ];
                                    //       var_dump($link,  $this->position,$pzn,$this->get_kws($content->find('#breadcrumb',0)));
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
              $content=  $con->find('.product-detail-container',0);
             
     
              
              $title=  trim($content->find('h1',0)->plaintext);
              
            
              $pzn_con=$content->find('table',0)->find('tr',2)->find('td',0);
              if ($pzn_con) {
                  $pzn =$this->strict_numbers($pzn_con->plaintext);
              }
              if ($default_price=$content->find('.default-price',0)) {
                  $price=  $this->money_format($default_price->plaintext);
              }elseif($p_price=$content->find('.price',0)){
                  $price=  $this->money_format($p_price->plaintext);
              }
              
              
              
              
              
              
              
              $status=$content->find('.na',0); 
              if ($status) {
                  $availability=0;
                 
              }else{
                   $availability=1;
                 }
              
              
              
              
               
              
              $description=$con->find('#longDescription',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
             
              
             
                $kws=   $this->get_kws($con->find('#breadcrumb',0)).",".$keywords_from_link;
              
             $ex=$con->find('#breadcrumb',0)->find('a');
            $category="";
            for ($i = 0; $i < count($ex); $i++) {
                $category.=trim($ex[$i]->plaintext).">";
            }
            $category= trim(substr(trim($category), 0,-1));
           // var_dump($category);
            
              $datetime=date("Y-m-d H:i:s");
                 $cross_seling=$this->cross_seling($pzn);
          //    var_dump($cross_seling);
         /*
              var_dump(    $title,
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
      
      
      
     
      protected function cross_seling($pzn){ 
           $url='https://www.mycare.de/online-kaufen/recommendations?code='.$pzn;
           $content=  $this->get_content($url);
         $arr=[];
          $a=$content->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $href=$a[$i]->getAttribute('href');
              $e=  explode('-', $href);
              $last= $this->strict_numbers(array_pop($e));
              $arr[]=$last;
              
               
          }
          
       
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
      
      
      
      
      
      
      
    
}
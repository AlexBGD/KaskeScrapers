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
class VersadapoScraper extends KaskeScraper{
    
    const URL="http://www.versandapo.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=18;
    protected $position=1;
    protected $check_links=[];
    

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
              /*     $this->parse_links(); */
          
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
            $box=$content->find('.articleGroupList',0);
            $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
               $link=  trim($domain.$a[$i]->getAttribute('href'));
               var_dump($link);
               $c=  $this->get_content($link,'body');
               $this->parse_sub_content($c);
                
               
          }
       
          
         
          
      }
       
      
      protected function parse_sub_content($con){
          if (!$con) {
              return FALSE;
          }
         $box=$con->find('.subcategories');
          $domain=  substr(self::URL, 0,-1);
         for ($sub = 0; $sub < count($box); $sub++) {
             $a=$box[$sub]->find('a');
             
             for ($i = 0; $i < count($a); $i++) {
              
              $link=$domain.$a[$i]->getAttribute('href');
              
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
          
          
          
          
          
          
          
          
          
      }

      



      protected function parse_pagination( $con,$from){
                   
          if (!$con) {
              return FALSE;
          }
           
          $this->position=1;
           $domain=  substr(self::URL, 0,-1);
          $this->save_links($con,$from);
          
          
          
          
           while (TRUE){
              
              $pagination=$con->find('.articleListPaginateButtons',0);
              if (!$pagination) {
                  break;
              }
              
              $next=$pagination->find('.nextLink',0);
              if (!$next) {
                  break;
              }
               $link=$domain.$next->getAttribute('href');
               var_dump('from pagiantion: '.$link);
              $con=  $this->get_content($link,'body');
              $this->save_links($con,$from);
              
              
              
              
              
              
              
          }
         
          
           
           
           
       }
          
          
          
          
          
          
       protected function save_links($content,$from){
           if (!$content) { 
               return false;
           }
                            
                     $box=$content->find('.hproduct');
                     if (!$box) {
                         return false;
                     }
                    
                     
                     $domain=  substr(self::URL, 0,-1);
                    for ($i = 0; $i < count($box); $i++) {  
                        $link= $domain.$box[$i]->find('a',0)->getAttribute('href');
                        $e=  explode('/', $box[$i]->find('a',0)->getAttribute('href'));
                        $pzn=  $this->strict_numbers($e[2]);
                       
                        
                      
                        if (in_array($pzn, $this->pzn,TRUE)) {
                                                        var_dump($pzn);
                            if ($this->save_links) {
                                          $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('.breadcrumbs',0)),$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('.breadcrumbs',0)),
                                            'from_url'  =>$from
                                        ];
                                       //      var_dump($link,  $this->position,$pzn,$this->get_kws($content->find('.breadcrumbs',0)));
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
              $con=  $this->get_content($url,'body');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('.hproduct_single',0);
              if (!$content) {
                  continue;
              }
     
              
              $title=  trim($content->find('h1',0)->plaintext);
              
              $pzn =$this->strict_numbers($content->find('.order_no',0)->plaintext);
               
            $price=  $this->money_format($content->find(".price",0)->find('span',0)->plaintext);
                  
           $status=$content->find('.available',0);
           if ($status) {
               $availability=1;
           }else{
                   $availability=0;
                 }
                  
                
                 
               
              
              $description=$con->find('.hproduct_single_extended',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
             
              
             
               $kws=   $this->get_kws($con->find('.breadcrumbs',0)).",".$keywords_from_link;
                $kk=$con->find('.breadcrumbs',0)->find('li');
              $cat='';
              for ($i = 0; $i < count($kk)-1; $i++) {
                  $cat.=trim(str_replace('&gt;', '', $kk[$i]->plaintext)).'>';
              }
              $category=substr($cat, 0,-1);
              
             
            
              $datetime=date("Y-m-d H:i:s");
              $cross_seling="";
           /*    
              var_dump(  $title,
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
      
      
      
     
      
      
      
      
      
      
      
    
}
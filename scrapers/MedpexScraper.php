<?php
include './KaskeScraper.php';
 
/*
 * 
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
 */
class MedpexScraper extends KaskeScraper{
    
    const URL="http://www.medpex.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=21;
    protected $position=1;
    protected $check_links=[];
    protected $all_urls=[];

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
            $box=$content->find('.categories',0);
          
          
            
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              
              $link=  trim( $domain.$a[$i]->getAttribute('href'));
              var_dump($link);
              
              $this->parse_pagination($link);
              $this->check_links[]=$link;
              $c=  $this->get_content($link);
              $this->parse_sub_menu($c);
            // 
              
              
              
              
              
             
              
                  
          }
       
   
        //   $this->save_data();
         
          
      }
      
      protected function parse_sub_menu($c){
          if (!$c) {
              return false;
          }
           $kats=$c->find('.categories',0);
           if (!$kats) {
               return false;
           }
              $a_kats=$kats->find('a');
              $domain=  substr(self::URL, 0,-1);
              for ($x = 0; $x < count($a_kats); $x++) {
                  $kats_link=  trim($domain.$a_kats[$x]->getAttribute('href'));
                  if (in_array($kats_link, $this->check_links)) {
                      continue;
                  }
                  $this->check_links[]=$kats_link;
                  var_dump($kats_link);
                  $c=  $this->get_content($kats_link);
                  var_dump('From sub menu: '.$kats_link);
                   $this->parse_pagination($kats_link);
                 $this->parse_sub_menu($c);
                  
              }
          
          
          
      }

      
      protected function parse_pagination( $url){
         
          
           $content=  $this->get_content($url);
            if (!$content) {
             return false;
           }
           $url_format= $url.'%s';
           $this->position=1;
          $save= $this->save_links($content,$url);
        $domain=  substr(self::URL, 0,-1);
         $num=1;
         while(true){
               
               $pagination=$content->find('.pagenav',0);
               if (!$pagination) {
                   break;
               }
              
             $link=  sprintf($url_format,$num);
               var_dump('From pagiantion: '.$link);
               $content=  $this->get_content($link,'#category');
               $this->save_links($content,$url);
               ++$num;
            }
        
          
          
          
          
          
          
      } 

            protected function save_links($content,$from_url){
                if (!$content) {
                    return false;
                }
                
               
                
                    $domain=  substr(self::URL, 0,-1);
                    $box=$content->find('.product-list-entry');
                    for ($i = 0; $i < count($box); $i++) {
                        
                        $link=$domain.$box[$i]->find('.description',0)->find('a',0)->getAttribute('href');
                        $e=  explode('-p', $link);
                        
                        $pzn=  $this->strict_numbers(array_pop($e));
                      
                       
                        if (in_array($pzn, $this->pzn,TRUE)) {
                           if ($this->save_links) {
                          $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('.breadcrumb',0)),$from_url); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('.breadcrumb',0)),
                                            'from_link'=>$from_url
                                        ];
                                    //  var_dump($link,  $this->position,$pzn,$this->get_kws($content->find('.breadcrumb',0)));
                            }
                       



                        ++$this->position;

                    }
           
                
                    return $content;
          
          
      }
    
      
      
   



 




      protected function save_data(){
          var_dump('save_data called');
          if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
  $domain=  substr(self::URL, 0,-1);
          foreach ($this->products_url as $k=> $v) {
              $url=$v['link'];
              $position=$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
              $from_link=$v['from_link'];
              var_dump($url );
              
             
               
                $get_content= $this->get_content($url,'#contentColumn',0,true);
                $headers=$get_content['headers'];
          
              $con= $get_content['content'];
              if (!$con) { 
                  continue;
              }
              $content=  $con->find('.description',0);
             
          
              $title=  trim($content->find('.product-name',0)->plaintext);
              
              
              
              $pzn =$this->strict_numbers(array_pop(explode('-p', $url)));
              
              
                   $price=  $this->money_format($con->find('.normal-price',0)->plaintext);
             
                   
                  
               
                  $availability=1;
             
                   
                   
                   
              
              $description=$con->find('.content',0)->innertext;
              $domain= self::URL;
              
               
              if (isset($headers['Location'])&&strpos($headers['Location'], $url)===false) {
                  $link=$url.','.$get_content['headers']['Location'];
                  var_dump($link,'-------------------------------');
              }else{
                   $link=$url;
              }
              
             
              
              
            
              
             
               $kws=   $this->get_kws($con->find('.breadcrumb',0)).",".$keywords_from_link;
            
              
             $ex=$con->find('.breadcrumb',0)->find('a');
            $category="";
            for ($i = 0; $i < count($ex); $i++) {
                $category.=trim($ex[$i]->plaintext).">";
            }
            $category= trim(substr(trim($category), 0,-1));
           
            
              $datetime=date("Y-m-d H:i:s");
               $cross_seling=$this->cross_seling($con);
          //    var_dump($cross_seling);
      
             /*      var_dump(  $title,
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
      
      
      
      
  protected function cross_seling($c){return "";
           
          $arr=[];
          if ( $cross=$c->find('#recommended-products',0)) {
             $a1=$cross->find('a');
             for ($i = 0; $i < count($a1); $i++) {
                 $href=$a1[$i]->getAttribute('href');
                 var_dump($href);
             }
          }
         
        
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
      
      
      
      
      
    
}
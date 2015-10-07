<?php
include './KaskeScraper.php';
/*
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
 */
class ZurRoseScraper extends KaskeScraper{
    
    const URL="http://www.zurrose.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=11;
    protected $position=1;



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
            $box=$content->find('.category-nav',0);
          
          
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=  trim( $a[$i]->getAttribute('href'));
              // var_dump($link);
             
               
               $con=  $this->get_content($link);
               $kat=$con->find('.dropdownlist',0);
               $this->position=1;
               var_dump($link);
               $this->parse_pagination($con->find('#content',0),$link);
               
               
               
                    
               if ($kat) {
                   $kat_a=$kat->find('a');
                   for ($y = 0; $y < count($kat_a); $y++) {
                       $sub_kat_link=$kat_a[$y]->getAttribute('href');
                       $sub_get_con=  $this->get_content($sub_kat_link);
                     
                       var_dump($sub_kat_link);
                       $this->parse_pagination($sub_get_con->find('#content',0),$sub_kat_link);
                       
                        $sub_kats=$sub_get_con->find('.dropdownlist',0);
                            if ( $sub_kats) { 
                                 $deep_sub_a=$sub_kats->find('a');
                            for ($x = 0; $x < count($deep_sub_a); $x++) {
                                $deep_links=$deep_sub_a[$x]->getAttribute('href');
                                $deep_con=  $this->get_content($deep_links);
                                var_dump($deep_links);
                                $this->parse_pagination($deep_con->find('#content',0),$deep_links);
                                
                                
                                
                                
                            }
                          }
                    }
               }
               
               
               
               
               
          
             
              
              
          }
          
          
        
          
         
          
      }
      
      protected function parse_pagination($content,$from){
          if (!$content) {
              return false;
          }
          $this->position=1;
                  $this->save_links($content,$from);
                   
                  
                  while(TRUE){
                      
                      $pagination= $content->find('.pagination',0);
                      if (!$pagination) {
                          break;
                      }
                      $next=$pagination->find('.next',0);
                      if (!$next) {
                          break;
                      }
                      $next_link=$next->getAttribute('href');
                       var_dump($next_link. " FROM PAGINATION");
                      $content=$this->get_content($next_link);
                      $this->save_links($content,$from);
                      
                      
                      
                      
                      
                  }
                  
          
          
          
      }

            protected function save_links($content,$from){
                $products=$content->find('.product-grid',0);
                if (!$products) {
                   return false;
                }
                
                
                $a=$products->find('a');
               
                    $count=  count($a);
                     for ($i = 0; $i < $count; $i++) {
                         $link=$a[$i]->getAttribute('href');
                        // var_dump($link);
                        
                          if ($this->save_links) {
                               $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('#breadcrumbs',0)),$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position++,
                                 'kws'      =>  $this->get_kws($content->find('#breadcrumbs',0)),
                                 'from_url'  =>$from
                             ];
                           //  var_dump($this->products_url);
                         
                         
                         
                         
                     }
                
                
                
             
          
          
      }
    
      
      
      protected function get_kws($content){
          if (!$content) {
              return "";
          }
          
          $kws=  $this->only_letters_num_spaces($content->plaintext);
          $exp= array_unique(array_filter(array_map('trim', explode(" ", $kws))));
         
          foreach ($exp as $k=>$v) {
              if (in_array($v, $this->negative_kws)) {
                  unset($exp[$k]);
              }
          }
          
          return strtolower(implode(',', $exp));
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
               // var_dump($url,$position,$keywords_from_link);
          
             
               var_dump($url);
               
              
              $content=  $this->get_content($url,'#content');
              if (!$content) {
                  continue;
              }
             
              
              $pack_sizes=$content->find('.product-pack-sizes',0);
              if (!$pack_sizes) {
                  continue;
              }
              $tr=$pack_sizes->find('table',0)->find('tr');
              $pzn=[];
              for ($i = 0; $i < count($tr); $i++) {
                  if (!$tr[$i]->find('.sku',0)) {
                      continue;
                  }
                $num=$this->strict_numbers($tr[$i]->find('.sku',0)->plaintext);
                  if ( in_array($num, $this->pzn,TRUE)) {
                       $pzn[]=[
                          'pzn'=>$num,
                           'price'=>  $this->money_format($tr[$i]->find('.price-wrap',0)->plaintext),
                           'avb'=>  trim($tr[$i]->find('.availability',0)->plaintext)==='Verfügbar'?1:0
                           ];
                   }
                  
                 
                  
                  
                  
                  
              }
              
               
              if (count($pzn)===0) {
                  continue;
              }
            //  var_dump($pzn);
              $title=  trim($content->find('.page-title',0)->plaintext);
              $content->find('#product-description',0)->find('h2',0)->outertext="";
               $description=$content->find('#product-description',0)->innertext;
              
              $domain= self::URL;
              $link=$url;
            
            
              
              
              
               $kws=   $this->get_kws($content->find('#breadcrumbs',0)).",".$keywords_from_link;
               if ($content->find('#breadcrumbs',0)) {
                      $kk=$content->find('#breadcrumbs',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk)-1; $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
               }else{
                   $category="";
               }
            
          //   var_dump($category);
            
              
              
              
              $datetime=date("Y-m-d H:i:s");
              
              foreach ($pzn as $key => $value) {
                  
              
                   $this->mysql->insert_data(
                       $title,
                      $description,
                      $value['price'],
                      $value['pzn'],
                      $domain,
                      $link,
                      $value['avb'],
                      $kws,
                      $position,
                      $datetime,
                      $from_link,
                      [$datetime=>$value['price']],
                      $category,
                      [$datetime=>$kws],
                      [$datetime=>$category],
                      '');
                
                
                  
                  
                  
              //   var_dump($title,$value['pzn'],$value['price'],$description, $value['avb']);
                  
              }
              
              
             
               
          }
         // var_dump($this->products_url);
  
      }
      
      
      
      
      
      
      
      
      
      
      
    
}
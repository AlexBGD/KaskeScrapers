<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * 
 * done 3
 * 
 * 
 */
class ApothekeScraper extends KaskeScraper{
    
    const URL="https://www.apotheke.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=4;
    protected $position=1;



    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
             
               $this->save_links=true;
            /*  $this->parse_links();  */
             
             
            
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
            
          $a=$content->find('#NavigationList',0)->find('a');
          
          for ($i = 0; $i < count($a); $i++) {
              $link=$a[$i]->getAttribute('href');
            //  var_dump($link);
       
          
                  $this->position=1;
                  
                    $con=  $this->parse_pagination($link,$link);
               //   $list=$con->find('#product_listing_block_container',0);
                  
                   
                  while(TRUE){
                      if (!$con) {
                          break;
                      }
                      $pagination=$con->find('.navigation_page_links',0);
                      if (!$pagination) {
                          break;
                      }
                      
                       
                         $next=$pagination->find('.next_and_prev_button',1); 
                         if (!$next) {
                             break;
                         }
                        
                         
                         $link1=$next->getAttribute('href');
                         if (!$link1) {
                                                         
                             break;
                         } var_dump(1);
                         $con=  $this->parse_pagination($link1,$link);
                      
                      
                  }
                  
                  
              }
          
           
          
         // $this->save_data();
          
         
          
      }
    
      protected function parse_pagination($url,$from){
           var_dump($url);
          $content=  $this->get_content($url,'#content_column');
          if (!$content) {
              return false;
          }
          $block=$content->find('#product_listing_block_container',0);
          $box=$block->find('.product_listing_block_boxes');
          $count_box=  count($box);
          
          for ($i = 0; $i <  $count_box; $i++) {
              $link=$box[$i]->find('a',0)->getAttribute('href');
              
              $img=$box[$i]->find('.thumbnail_image',0)->find('img',0)->getAttribute('data-src');
              $e=  explode('thumbnail_images/', $img);
              $pzn=  $this->strict_numbers($e[1]);
              if (in_array($pzn, $this->pzn,TRUE)) {
                      if ($this->save_links) {
                                $this->mysql->insert_links(self::ID,$link,  $this->position, '',$from); 
                           }
                            $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  '',
                                'from_link' =>$from
                             ];
                            //  var_dump($this->products_url);
              }
              
              
           
              
              ++$this->position;
             
              
              
              
          }
          
        
          
          return $content;
          
      }
    
    
      //SELECT * FROM `crawler_data` WHERE `domain`="https://www.apotheke.de/"
      protected function save_data(){
          var_dump('save_data called');
           if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
         
         // var_dump($this->products_url);
          foreach ($this->products_url as $v) {
            
              $url=$v['link'];
              $position=(int)$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
              $from_link=$v['from_link'];
              var_dump($url);
              $con=  $this->get_content($url,'#content_column');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('.main',0);
              if (!$content||!$pzn=$this->valid_pzn($content->find('.product_detail_info',0))) {
                   continue;
              }
           //    var_dump($url);
             //   var_dump($position);
              
              $title= trim(str_replace('&nbsp;', '', $content->find('h1',0)->plaintext));
              
              $content->find('.package_insert',0)->outertext="";
              $description=$content->find('#product_description_box_1',0)->innertext;
              //$pzn
              $price_product=$content->find('.product_detail_price_box',0)->find('.product_detail_price',0);
                $price=$this->money_format($price_product->plaintext);
              
            //  $price=  $this->only_numbers($content->find('.productPrice',0)->find('.yourPrice',1)->plaintext);
              
              $domain= self::URL;
              $link=$url;
              
              $availability=1;
              
              
              
                $kk=$con->find('.headerNavigationWrapper',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk); $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
              
              
              
              $exp=$this->only_letters_num_spaces_replace_for_space($category);
               
               $e=  explode(' ', $exp) ;
              $kws='';
               for ($i = 0; $i < count($e); $i++) {
                   $st_n= strtolower(trim($this->only_letters_num_spaces_replace_for_space($e[$i])));
                    if (!in_array( $st_n, $this->negative_kws)&&strlen( $st_n)>=3) {
                   $kws.=$st_n.',';
              }
                   
                   
               }
               $kws=  substr($kws, 0,-1);
             
          $cross_seling=$this->cross_seling($content);
          $datetime=date("Y-m-d H:i:s");
           
                  
            /*     var_dump($title,
                    //  $description,
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
                      $cross_seling
                     );
                      
              
            //  break;
          }
         // var_dump($this->products_url);
  
      }
      
      
          protected function cross_seling($c){
           
          $arr=[];
          if (!$c->find('#cross_selling_scroll',0)) {
              return "";
          }
          
          $cross=$c->find('#cross_selling_scroll_inner',0)->find('.thumbnail_image');
          if ($cross) {
                for ($i = 0; $i < count($cross); $i++) {
                     $thum=$cross[$i]->find('img',0)->getAttribute('data-src');
              $e=  explode('/thumbnail_images/', $thum);
              $pzn=$this->strict_numbers($e[1]);
             
              }
          
          
          }
          
          
     
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
          if (in_array($pzn, $this->pzn,TRUE)) {
              return $pzn;
          } 
          return false;
          
          
          
          
      }
      
      
      protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.apotheke.de/advanced_search_result.php?keywords=';
          
          
           $domain=  substr(self::URL, 0,-1);
           
           foreach ($pzns as $v) {
             
          $content=  $this->get_content($search_url.$v); 
          $box=$content->find('.product_listing_container',0);
          if ($box) {
             $a=$box->find('a',0);
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

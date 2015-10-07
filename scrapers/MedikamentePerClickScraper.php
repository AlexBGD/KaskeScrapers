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
class MedikamentePerClickScraper extends KaskeScraper{
    
    const URL="https://www.medikamente-per-klick.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=10;
    protected $position=1;

    protected $check_links=[];

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
           $box=$content->find('#smallBoxBrowseCategories',0);
           $this->parse_a($box);
              $content1=  $this->get_content('https://www.medikamente-per-klick.de/markenshops');
            $box1=$content1->find('#smallBoxBrowseCategories',0);
          
            
             $this->parse_a($box1);
         
          
          
        
          
         
          
      }
      
      
      protected function parse_a($con) {
           $a=$con->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=  trim( $a[$i]->getAttribute('href'));
              if (in_array($link, $this->check_links)) {
                  continue;
              }
                var_dump($link);
              $con=  $this->get_content($link);
              if (!$con) {
                  continue;
              }
             $id_content=$con->find('#content',0);
               $products=$id_content->find('.productsList',0);
              
             if ($products) {
                  $this->parse_pagination($id_content,$link);
                  $this->check_links[]=$link;
               }
              
               $this->parse_sub_cat($link);
              
              
             
              
              
          }
          
      }




      protected function parse_sub_cat($url){
          $con=  $this->get_content($url);
          if (!$con) {
              return false;
          }
          $sub=$con->find('.categoryLinks',0);
          if (!$sub) {
              return false;
          }
          $a=$sub->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=$a[$i]->getAttribute('href');
               if (in_array($link, $this->check_links)) {
                  continue;
              }
              $this->check_links[]=$link;
              $content=  $this->get_content($link);
               $this->parse_pagination($content, $link);
              var_dump("sub content: " .$link);
              $this->parse_sub_cat($link);
              
              
              
          }
          
          
          
          
          
          
          
          
          
      }


       
      
      
      protected function parse_pagination($content,$from_link){
          $this->position=1;
          if (!$content) {
              return false;
          }
                  $this->save_links($content,$from_link);
                   
                  
                  while(TRUE){
                      
                      $pagination= $content->find('.boxNavigationTop',0);
                      if (!$pagination) {
                          break;
                      }
                      $next=$pagination->find('.btnNext',0);
                      if (!$next) {
                          break;
                      }
                      $next_link=str_replace('&amp;', '&',$next->getAttribute('href'));
                       
                      var_dump($next_link. " FROM PAGINATION");
                      $content=$this->get_content($next_link);
                      $this->save_links($content,$from_link);
                      
                      
                      
                      
                      
                  }
                  
          
          
          
      }

            protected function save_links($content,$from_url){
                $products=$content->find('.productsList',0);
                if (!$products) {
                   return false;
                }
                 $t=0;
                              //    var_dump($this->get_kws($content->find('.navigatorBox',0)));
                $first_products=$products->find('.firstProducts');
                if ($first_products) {
                   
                    
               
                for ($i = 0; $i < count($first_products); $i++) {
                    $pro=$first_products[$i]->find('.product',0);
                    $pzn=  $this->strict_numbers($pro->find('.pzn',1));
                   // var_dump($pzn);
                    if (in_array($pzn, $this->pzn,TRUE)) {
                        $link=$pro->find('.h3',0)->find('a',0);
                        if ($link) {
                            $link=$link->getAttribute('href');
                       
                         if ($this->save_links) {
                               $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('.navigatorBox',0)),$from_url); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  $this->get_kws($content->find('.navigatorBox',0)),
                                 'from_url' =>$from_url
                             ];
                        //     var_dump($link,$this->position);
                    } }
                    
                    //var_dump('not in list:'.$pzn);
                    
                    
                    
                $products->find('.firstProducts',$i)->outertext='';
                    ++$this->position;
                    
                }
                $t=count($first_products);
                    }
              
                $pag_list= $products->find('.produktuebersicht',$t);
                if (!$pag_list) {
                    return false;
                }
                $table_ch=$pag_list->children;
                foreach ($table_ch as $v) {
                    //var_dump($v->outertext);
                        $pro=$v->find('.product',0);
                    $pzn=  $this->strict_numbers($pro->find('.pzn',1));
                            $pp=$pro->find('.h3',0);
                        if (!$pp) {
                            continue;
                        }
                        if (!$pp->find('a',0)) {
                            continue;
                        }          
        $link=$pp->find('a',0)->getAttribute('href');
        if (!$link) {
            continue;
        }
                   // var_dump($pzn);
                    if (in_array($pzn, $this->pzn,TRUE)) {
                       // var_dump($pzn);
                
                         if ($this->save_links) {
                               $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($content->find('.navigatorBox',0)),$from_url); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  $this->get_kws($content->find('.navigatorBox',0)),
                                 'from_url' =>$from_url
                             ];
                        //    var_dump($link,$this->position);
                    }
                    
                 //   var_dump('not in list:'.$pzn);
                    
                    ++$this->position;
                    
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
                var_dump($url);
          
             
               
                $get_content= $this->get_content($url,'#content',0,true);
                $headers=$get_content['headers'];
          
              $con= $get_content['content'];
              if (!$con) {
                 
              //    var_dump($from_link,"positon: ".$position);
              //     throw new Exception('tesssset');
                  continue;
              }
              $content=  $con->find('.boxProductDetail',0);
             
          
              
              $title=  trim($content->find('h1',0)->plaintext);
              
              $pzn=$this->strict_numbers($content->find('.pzn',1)->plaintext);
              
              
              $price=  $this->money_format($content->find('.price',0)->plaintext);
              
              $description=$content->find('#productDesc',0)->innertext;
              $domain= self::URL;
                 if (isset($headers['Location'])&&strpos($headers['Location'], $url)===false) {
                  $link=$url.','.$get_content['headers']['Location'];
                  var_dump($link,'-------------------------------');
              }else{
                   $link=$url;
              }
              
              
              
              
              $avb=$content->find('.productAvailability',0);
              $status=$avb->find('.status1',0);
              if ($status) {
                  $availability=1;
              }else{
                  $availability=0;
              }
              
             
              $kws=   $this->get_kws($con->find('.navigatorBox',0)).",".$keywords_from_link;
               $ex=$con->find('.navigatorBox',0)->find('a');
            $category="";
            for ($i = 0; $i < count($ex); $i++) {
                $category.=trim($ex[$i]->plaintext).">";
            }
            $category= trim(substr(trim($category), 0,-1));
            var_dump($category);
              $datetime=date("Y-m-d H:i:s");
              
              $cross_seling="";
            //  var_dump($title,$pzn,$price,$description, $availability);
              
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
             
           
            //  break;
          }
         // var_dump($this->products_url);
  
      }
      
      
      
      
 
      
      
      
      
      
      
    
}
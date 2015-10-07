<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * done 2
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
class BesamexScraper extends KaskeScraper{
    
    const URL="https://www.besamex.de/"; 
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=5;
    protected $position=1;
    protected $check_link=[];


    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
             
             
               $this->save_links=true;
              $this->parse_links(); 
             
         /*    if ($this->save_links) {
                 $this->save_data();
             }else{
                   $this->parse_links();
             } */
      }
    
    
      protected function parse_links(){
           $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
          
           
          
          $box=$content->find('.subnavx');
           $count_box=  count($box);
          for ($i = 0; $i < $count_box; $i++) {
              $a=$box[$i]->find('a');
              $count_a=  count($a);
              for ($y = 0; $y < $count_a; $y++) {
                  
                    $link=  trim($a[$y]->getAttribute('href'));
                    if (in_array($link, $this->check_link)) {
                        continue;
                        
                    }
                    $this->check_link[]=$link;
              $this->position=1;
                $con=  $this->parse_pagination($link,$link);
              
              while(TRUE){
                  if (!$con) {
                      break;
                  }
                  $pagination=$con->find('.boxNavigation',0);
                  
                  $next=$pagination->find('.btnNext',0);
                  if (!$next) {
                      break;
                  }
                  $link1=$next->getAttribute('href');
                  $con=  $this->parse_pagination($link1,$link);
                  
                  
              }
              
                  
                  
                  
                  
              }
              
              
             }
          
          
           
           
                  $add_l=[
                 'https://www.besamex.de/preishits/19888.html',
                 'https://www.besamex.de/sonderbonuspunkte/23687.html',
                 'https://www.besamex.de/neu-im-shop/19795.html'
             ];
             
             for ($x = 0; $x < count($add_l); $x++) {
                 $link=$add_l[$x];
                    $this->position=1;
                $con=  $this->parse_pagination($link,$link);
              
              while(TRUE){
                  if (!$con) {
                      break;
                  }
                  $pagination=$con->find('.boxNavigation',0);
                  
                  $next=$pagination->find('.btnNext',0);
                  if (!$next) {
                      break;
                  }
                  $link1=$next->getAttribute('href');
                  $con=  $this->parse_pagination($link1,$link);
                  
                  
              }
              
             }
             
             
             
         // $this->save_data();
          
         
          
      }
      
       


      protected function parse_pagination($url ,$from){
                 
           var_dump($url);
          $content=  $this->get_content($url,'#content');
          if (!$content) {
              return false;
          }
          $gen=$content->find('#generic_content_container',0);
          if (!$gen) {
              return false;
          }
          
          $box=$gen->find('.artikel');
           $count_box=  count($box);
          
          for ($i = 0; $i < $count_box; $i++) {
              $pzn=  $this->strict_numbers($box[$i]->find('.pzn',0)->plaintext);
               var_dump($pzn);
               if (in_array($pzn, $this->pzn,TRUE)) {
                                     
                   $link=$box[$i]->find('a',0)->getAttribute('href');
                   if ($this->save_links) {
                                 $this->mysql->insert_links(self::ID,$link,  $this->position,'',$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  '',
                                'from_link' =>$from
                             ];
                     // var_dump($this->products_url);
               }
              
                ++$this->position;
              
               
           }
          
          
          return $content;
          
      }
    
    
      
      protected function save_data(){
          var_dump('save_data called');
           if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
         
         // var_dump($this->products_url);
          foreach ($this->products_url as $v) {
               $url=  trim($v['link']);
              $position=$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
               $from_link=$v['from_link'];
          
             
               var_dump($url);
               
               
               
               
               
              $con=  $this->get_content($url,'#container');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#product_content_box_middle',0);
              if (!$content ) {
                   continue;
              }
            
              
              
              $title=  str_replace('&nbsp;', '', trim($content->find('.product_name',0)->plaintext));
              
             
              $description=$content->find('.product_short_description',0)->innertext.$content->find('.product_long_description',0)->innertext;
             
             $price_con=$content->find('.yourPrice',0);
             
            $price=  substr($this->money_format( $price_con->plaintext), 0,-1) ;
              
              $domain= self::URL;
              $link=$url;
              
              $availability=$content->find('.status_1',0)?1:0;
              
               $kws=  $this->get_kws($con->find('.navigatorBoxMiddle',0));
         
              
              $datetime=date("Y-m-d H:i:s");
              $pzn=  $this->strict_numbers($content->find('.pzn',0)->plaintext);
              
                $kk=$con->find('.navigatorBoxMiddle',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk); $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
             $cross_seling=$this->cross_seling($con);
             
           /*   
                var_dump( 
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
          if (!$c->find('#generic_content_container_product_detail',0)) {
              return "";
          }
          
          $cross=$c->find('#generic_content_container_product_detail',0)->find('.pzn' ) ;
          if ($cross) {
                for ($i = 0; $i < count($cross); $i++) {
                 $pzn=  $this->strict_numbers($cross[$i]->plaintext);
               $arr[]=$pzn;
             
              }
          
          
          }
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
            protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='http://www.besamex.de/keywordsearch/~sortBy=default';
           
          
          
         //  foreach ($pzns as $v) {
            
  
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
      $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
      $this->setCookie('JSSESSIONID', '79A021E182772A752875603B56F3AFE6-memc0.pla1tom2');	
      $this->setReferrer('http://www.besamex.de');
               $v='IBU LYSIN';
               
               
                  $get=  $this->post($search_url,[
                         'VIEW_SIZE'=>10,
                        'SEARCH_STRING_CONTENT'=>$v,
                        'SEARCH_REQUIRED_PID'=>'',
                        'searchType'=>'lucene',
                        'searchTypeH'=>'keyword',
                        'SEARCH_CATEGORY_ID'=>'',
                        'SEARCH_OPERATOR'=>'AND'
                  ]);
                  
            $response = $this->getResponse();
          $content = str_get_html($response);
          echo $content->outertext;
         //   var_dump($this->getResponseHeaders());
         //   var_dump($this->getRequestHeaders());
         
        /*
               $this->products_url[]=[
                                 'link'     =>$href,
                                 'position' =>  0,
                                 'kws'      =>  '',
                                'from_link' =>'search'
                             ];
         
              */ 
               
               
      //     }
           
         
          
          
          
          
       //   $this->save_data();
      }
       
      
      
      
    
}

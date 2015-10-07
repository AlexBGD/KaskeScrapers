<?php 
//include './KaskeScraper.php';
 /*
  * 
  * 
  * 
  * done 3
  * 
  *  
  * 
  * 
  */
class AlivaScraper extends KaskeScraper{
    
    const URL="https://www.aliva.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=13;
    protected $position=1;




    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
             
               $this->save_links=true;
          /* $this->parse_links(); */ 
             
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
          
          $box=$content->find('#smallBoxCategory',0);
          $a=$box->find('a');
          $cnt=  count($a);
          for ($i = 0; $i < $cnt; $i++) {
              $link=$a[$i]->getAttribute('href');
              var_dump($link);
               
              $this->position=1;
            
              $content=$this->parse_pagination($link,$link);
             
              while(true){
                  if (!$content) {
                      break;
                  }
                  $pagination=$content->find('.page-lst',0);
                  if (!$pagination) {
                      break;
                  }
                  $next=$pagination->last_child()->find('a',0);
                  if (!$next) {
                      break;
                  }
                  
                  $href=$next->getAttribute('href');
                  if (!$href) {
                      break;
                  }
                  
                  
                  
                  
                  $link1=  trim(preg_replace('/\s+/', '', $href));
                  $content=  $this->parse_pagination($link1,$link);
                 
                  
              }  
              }
          
      //    $this->save_data();
          
          
          
      }
    
       

      protected function parse_pagination($url,$from){ 
          var_dump($url);
          $con=  $this->get_content($url,'#content');
          $content=  $con->find('#categoryDetail',0);
          if (!$content) {
              return false;
          }
          $list=$content->find('.productsList',0);
          if (!$list) {
              return FALSE;
          }
          $box=$list->find('.boxProduct');
          if (!$box) {
              return FALSE;
          }
          $count_box=  count($box);
          for ($i = 0; $i <  $count_box ; $i++) {
              if (!$box[$i]->find('.pzn')) {
                  continue;
              }
              $pzn=  $this->strict_numbers($box[$i]->find('.pzn',1)->plaintext);
              
              if (in_array($pzn, $this->pzn,true)) {
                //  var_dump('pzn in array: '.$pzn);
                  $link=$box[$i]->find('a',0)->getAttribute('href'); 
                  if ($this->save_links) {
                                 $this->mysql->insert_links(self::ID,$link,  $this->position, $this->get_kws($con->find('.navigatorBox',0)),$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      =>  $this->get_kws($con->find('.navigatorBox',0)),
                                 'from_link'=>$from
                             ];
                         //   var_dump($link,$this->position,$this->get_kws($con->find('.navigatorBox',0)));
                         
                         
                  
              }  
              
              
              
              ++$this->position;
          }
          
         
          
          return $content;
          
      }
    
    
      
      protected function save_data(){
          var_dump('save_data called');
         if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
          foreach ($this->products_url as $v) {
              $uu=  explode(';jsessionid', $v['link']);
               $url=$uu[0];
              $position=$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
              $from_link=$v['from_link'];
              
                var_dump($url);
              
                
              $con=  $this->get_content($url,'#content');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('.boxProductDetail',0);
              if (!$content) {
                   continue;
              }
              
              $pzn=  $this->strict_numbers($content->find('.pzn',0)->plaintext);
              $tt=$con->find('h1',0);
             $t=  $this->remove_el($tt, [
                 'span'=>  range(0, 3)
             ]);
             
              
     
              $title=  trim($tt->innertext);
              if ($content->find('.product-description',0)) {
                 $description=$content->find('.product-description',0)->innertext;  
              }else{
                  $description="";
              }
             
               
              $con_price=$content->find('.productPrice',0)->find('.yourPrice',1);
             
              $price= $this->money_format($con_price->plaintext) ;
            
              $domain= self::URL;
              $link=$url;
              $av=$con->find('.productAvailability',0)->find('.status1',0);
              if ($av) {
                  $availability=1;
              }else{
                   $availability=0;
              }
              
              
              $c=$con->find('.navigatorBoxMiddle',0)->find('a');
              $category="";
              for ($i = 0; $i < count($c); $i++) {
                  $category.=trim(str_replace('&amp;', '', $c[$i]->plaintext)).'>';
              }
              $category=  substr($category, 0,-1);
             
              
              
                $brd=  $this->get_kws($con->find('.navigatorBox',0)).",".$keywords_from_link;
              $exp= array_unique(explode(',', $brd));
              
             $kws=  implode(',', $exp);
              
              
               
              $datetime=date("Y-m-d H:i:s");
              
              
              
              
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
                     $category
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
          //productCrossSell productAlsoBought
         
          
          $arr=[];
          
          
          $cross=$c->find('.productCrossSell',0);
          if ($cross) {
              $cross_pzn=$cross->find('.pzn');
              if ($cross_pzn) {
                         for ($i = 0; $i < count($cross_pzn); $i++) {
              $pzn=  $this->strict_numbers($cross_pzn[$i]->plaintext);
              $arr[]=$pzn;
          }
              }
          
          
          }
          
          
          
          $bought=$c->find('.productAlsoBought',0);
          if ($bought) {
               $pzns=$bought->find('.pzn');
          if ($pzns) {
             for ($i = 0; $i < count($pzns); $i++) {
              $pzn=  $this->strict_numbers($pzns[$i]->plaintext);
              $arr[]=$pzn;
          }
          }
          
          
          
          }
          
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
          
      }
      
    
      
      
      
      
   
        
      protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.aliva.de/keywordsearch/searchitem=';
          
          
           $domain=  substr(self::URL, 0,-1);
           
            foreach ($pzns as $v) {
            
          $content=  $this->get_content($search_url.$v); 
          $box=$content->find('#productsList',0);
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
           
         
     //  var_dump($this->products_url);
          
         $this->save_data();
      }
      
      
      
    
}


 









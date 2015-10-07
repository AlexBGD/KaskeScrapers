<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * 
 * 
 * 
 * 
 * done 3
 * 
 * 
 * 
 * 
 * 
 */
class EuropaApotheekScraper extends KaskeScraper{
    
    const URL="https://www.europa-apotheek.com/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=8;
    protected $position=1;



    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
             
               $this->save_links=true;
            /* $this->parse_links();*/
             
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
          $box=$content->find('#navi-products',0);
          
        
          $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=  trim($a[$i]->getAttribute('href'));
              if (strpos($link, 'produkte-a-bis-z.htm')!==false) {
                  continue;
              }
              
              
              $cat_con=  $this->get_content($link,'body');
              $this->position=1;
              if ($cat_con->find('#product-wrapper')) {
                      var_dump('from wroapper: '.$link);
                  $this->parse_pagination($cat_con->find('#site',0),$link);
                  
                  
                  
                  
              }elseif($cat_list_entry=$cat_con->find('.category-list-entry')){
                    for ($y = 0; $y < count($cat_list_entry); $y++) {
                       $link_entry=$cat_list_entry[$y]->find('.category-name-number',0)->find('a',0)->getAttribute('href');
                       var_dump("from entry: ".$link_entry);
                       $entry_con=  $this->get_content($link_entry);
                       $this->parse_pagination( $entry_con->find('#site',0),$link_entry); 
                    } 
                      
            
                 
                  
              }
            
              
          }
          
          
         // $this->save_data();
          
         
          
      }
      
      protected function parse_pagination($con,$link){        
          if (!$con) { 
              return false;
          }
          
          $num=2;
         $param='?searchMode=top&page='.$num;
         $this->position=1;
           $save_links=  $this->save_links($con,$link);
               while(TRUE){
                   $param='?searchMode=top&page='.$num;
               $c=  $this->get_content($link.$param,'#site');
               if (!$this->save_links($c,$link)) {
                 //  var_dump('BREAK------------------------------------------');
                   break;
               }
               var_dump('pagination link: '.$link.$param);
               
               ++$num;   
                  
              } 
          
          
          
      }

      protected function save_links($con,$from){
          
          if (!$con->find('#product-wrapper',0)) {
              return false;
          }
          $res=$con->find('#product-wrapper',0)->find('.search-result');
          if (!$res) {
              return false;
          }
          for ($i = 0; $i < count($res); $i++) {
              $main_info=$res[$i]->find('.main-info',0);
              $e= array_map('trim', explode(' ', trim($main_info->plaintext)));
              $pzn=$e[1];
              if (in_array($pzn, $this->pzn,TRUE)) {
                   $link=$res[$i]->find('a',0)->getAttribute('href');
                        if ($this->save_links) {
                                  $this->mysql->insert_links(self::ID,$link,  $this->position,$this->get_kws($con->find('#breadcrumb',0)),$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                 'kws'      => $this->get_kws($con->find('#breadcrumb',0)),
                                 'from'     =>$from
                             ];
                      // var_dump($link,  $this->position);
                     
              }
              
                
              
              ++$this->position;
          }
          
          
        
          
          
          return $con;
          
          
          
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
              var_dump($url );
           
              $con=  $this->get_content($url,'#site');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#page-col2',0);
             
             
              
              
              $title=  trim($content->find('h1',0)->plaintext);
               $desc=$content->find('.ifap-data');
               $description="";
               for ($i = 0; $i < count($desc); $i++) {
                   $description.=$desc[$i]->innertext;
                }
                if (!$content->find('.youpay',0)) {
                    $price="";
                }else{
                   $price=  $this->money_format($content->find('.youpay',0)->plaintext); 
                }
               
              
              $domain= self::URL;
              $link=$url;
              
              $availability=1;
             $kws= $this->get_kws($con->find('#breadcrumb',0));
              
             
             
             $pzn=trim($this->strict_numbers($content->find('#selenium-pzn',0)->plaintext));
           
             
             
              $datetime=date("Y-m-d H:i:s");
              
               
               $kk=  str_replace('&gt;', '>', $con->find('#breadcrumb',0)->plaintext );
             $category= trim(str_replace('  > ', '>', $kk));
           $e= array_map('trim', explode('>', $category));
           array_pop($e);
           $category=  implode('>', $e);
           
              
             /*  
                var_dump(     $title,
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
                      '');
              
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
                      '');
            
             
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
      
      
             protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.europa-apotheek.com/search.go?q=';
          
          
           $domain=  substr(self::URL, 0,-1);
              
       
      // $v="10333719";
        foreach ($pzns as $v) {
        $content=  $this->get_content($search_url.$v); 
          $search=$content->find('.search-result',0);
          if ($search&&$a=$search->find('a',0)) {
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
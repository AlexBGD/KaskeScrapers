<?php
//include './KaskeScraper.php';
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
class DocMorrisScraper extends KaskeScraper{
    
    const URL="https://www.docmorris.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=7;
    protected $position=1;
    protected $check_links=[];


    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                   
             }
               $this->save_links=true;
            /*     $this->parse_links(); */
         
           if ($this->save_links) {
                 $this->save_data();
             }else{
                   $this->parse_links();
             } 
      }
    
    
      protected function parse_links(){
           $get=  $this->get('https://www.docmorris.de/navigation');
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
         
          
        
            
            
             
          $a=$content->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=  trim($domain.$a[$i]->getAttribute('href'));
              var_dump($link);
              if (in_array($link, $this->check_links)) {
                  continue;
              }
              $this->check_links[]=trim($link);
               $con=  $this->get_content($link);
               $this->parse_pagination($con,$link);
              
              $this->parse_sub_content($con);
              
          }
          
          
         // $this->save_data();
          
         
          
      }
      protected function parse_sub_content($con){
          if (!$con) {
              return false;
          }
          $static=$con->find('#col-static',0);
          if (!$static) {
              return false;
          }
         $cats=$static->find('.categories',0);
         if (!$cats) {
             return false;
         }
              $cat_a=$cats->find('a');
           $domain=  substr(self::URL, 0,-1);
              for ($i = 0; $i < count($cat_a); $i++) {
                  $link=$domain.$cat_a[$i]->getAttribute('href');
                   if (in_array($link, $this->check_links)||  strpos($link, '/inhalte/produkte')!==false) {
                       continue;
                     }
                   $this->check_links[]=trim($link);
                  var_dump($link);
                   $c=  $this->get_content($link);
                   $this->parse_pagination($c,$link);
                   $this->parse_sub_content($c);
               
                  
                  
                  
                  
              }
          
          
          
          
          
          
      }

      

      protected function parse_pagination($con,$from){
          if (!$con) {
              return false;
          }
          $this->position=1;
           $domain=  substr(self::URL, 0,-1);
          $content=  $con->find('#col-fluid',0);
          if (!$content) {
              return false;
          }
          
          $this->save_links($content,$from);
                  
          
          
          
          
          
          
                while(TRUE){
                  if (!$con) {
                      break;
                  }
                  $pagination=$con->find('.pagination',0);
                  if (!$pagination) {
                      break;
                  }
                  $next=$pagination->find('.next',0);
                  if (!$next) {
                      break;
                  }
                  $link1=str_replace('&amp;', '&',$domain.$next->getAttribute('href'));
                  $e=  explode(';', $link1);
                  $e1=  explode('?', $e[1]);
                  $link=$e[0]."?".$e1[1];
                  
                  var_dump('Pagination: '.$link);
                  $con=$this->get_content($link);
                   $this->save_links($con,$from);
                  
                  
              }
              
            
              
        
      }
    
    
      
      
      
      
      
      
      
      
      
      
       
      protected function save_links($con,$from){
          if (!$con) {
              return false;
          }
           
           $box=$con->find('.l-product-inner');
           
           $domain=  substr(self::URL, 0,-1);
           
           
           for ($i = 0; $i < count($box); $i++) {
                $con=$box[$i]->find('.content',0);
               $pzn=$this->strict_numbers($con->find('.pzn',0)->plaintext);
              
               if (in_array($pzn, $this->pzn,TRUE)) {
                   if ($con->find('a',0)) {
                      
                  
                   $link=$domain.$con->find('a',0)->getAttribute('href');
                    if ($this->save_links) {
                                $this->mysql->insert_links(self::ID,$link,  $this->position,'',$from); 
                           }
                             $this->products_url[]=[
                                 'link'     =>$link,
                                 'position' =>  $this->position,
                                  'kws'      =>  '',
                                 'from_link'    =>$from
                             ];
                          //  var_dump($link,$this->position);
                   }
               }
               
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
              var_dump($url );
          
              $con=  $this->get_content($url,'#wrapper');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('.tabcontent',0);
             
             
              
              
              $title=  trim($content->find('h1',0)->plaintext);
              
              
              
              
              
              
              $info=$content->find('.product-detail-table',0);
              $pzn=  $this->strict_numbers($info->find('tr',3)->find('td',1)->plaintext);
              $description=$content->find('.tab-main',0)->innertext;
             
              if ($content->find('.price-box',0)) {
                  $price=  $this->money_format($content->find('.price-box',0)->find('.price',0)->plaintext);
                   $availability=1;
              }else{
                  $price=0;
                   $availability=0;
              }
              
               
    
          
              $domain= self::URL;
              $link=$url;
              
              $availability=1;
                    
                $brd=  $this->get_kws($con->find('.breadcrumb-list',0)).",".$keywords_from_link;
              $exp= array_unique(explode(',', $brd));
              
             $kws=  implode(',', $exp);
              
                  $kk=$con->find('.breadcrumb-list',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk); $i++) {
                  $cat.=trim($this->only_letters_num_spaces($kk[$i]->plaintext)).'>';
              }
              $category=substr($cat, 0,-1);
             
             
             
             
             
             
             
             
              $datetime=date("Y-m-d H:i:s");
              
                $cross_seling=$this->cross_seling($con);
           //     var_dump($cross_seling);
           /*    
               var_dump(        $title,
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
      
      
      
 
          protected function cross_seling($c){
           
          $arr=[];
          if (!$s=$c->find('.product-slider',0)) {
              return "";
          }
          
          
              $imgs=$s->find('img');
              for ($y = 0; $y < count($imgs); $y++) {
                  $src=$imgs[$y]->getAttribute('data-masterpzn');
                  if ($src) {
                      
               $pzn=  $this->strict_numbers($src);
               $arr[]=$pzn;
                 
                 
                  }
                 
              }
              
               
          
          $arr=  array_filter(array_unique($arr));
          
           
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
      
       protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.docmorris.de/search?query=%s&t=';
          
          
           $domain=  substr(self::URL, 0,-1);
              
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
        $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
     // $v="10333719";
         foreach ($pzns as $v) {
        $content=  $this->get_content(sprintf($search_url, $v)); 
          $headers=$this->getResponseHeaders();
         if (isset($headers['Location'])) {
             $Location=$headers['Location'];
             var_dump($Location);
              $this->products_url[]=[
                                 'link'     =>$Location,
                                 'position' =>  0,
                                 'kws'      =>  '',
                                'from_link' =>'search'
                             ];
           }
      
  
       
       
       
              
          
               
      }
           
         
           
         $this->save_data();
      }
      
      
      
      
    
}
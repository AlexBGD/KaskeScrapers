<?php
//include './KaskeScraper.php';
 
/*
 * 
 * 
 * 
 * 
 *  
 *  done 3
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
class EuVersandapothekeScraper extends KaskeScraper{
    
    const URL="https://eu-versandapotheke.com/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=17;
    protected $position=1;
    protected $check_links=[];
    

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
          
          
          
          
          $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
      $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
       $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
       
        $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
           
            
            $box=$content->find('#category__menu',0);
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
          $nav=$con->find('#category__menu',0);
          if (!$nav) {
              return false;
          }
          
          $a=$nav->find('a');
          $domain=  substr(self::URL, 0,-1);
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
              
              $next=$pagination->last_child();
              if (!$next) {
                  break;
              }
              
              if (!$link= $next->find('a',0)->getAttribute('href')) {
                  break;
              }
              
              
               $link=$domain.$link;
               var_dump('from pagiantion: '.$link);
              $con=  $this->get_content($link,'body');
              $this->save_links($con ,$from);
              
              
              
              
              
              
              
          }
         
          
           
           
           
       }
          
          
          
          
          
          
       protected function save_links($content,$from){
                            $products=$content->find('.col-md-9',0); 
                            if (!$products) {                               
                                return false;
                            }
                            
                            
                     $box=$products->find('.product__box__container');
                    
                    
                     
                     $domain=  substr(self::URL, 0,-1);
                    for ($i = 0; $i < count($box); $i++) {  
                        $link= $domain.$box[$i]->find('a',0)->getAttribute('href');
                     
                       $p=$box[$i]->find('p',2);
                      $pzn=  $this->strict_numbers($p->plaintext);
                      
                        if (in_array($pzn, $this->pzn,TRUE)) {
                            if ($this->save_links) {
                                          $this->mysql->insert_links(self::ID,$link,  $this->position, 
                                                 $this->get_kws($content->find('#breadcrumb',0)),$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('#breadcrumb',0)),
                                            'from'     =>$from
                                        ];
                                         //    var_dump($pzn);
                            }
                        
 


                        ++$this->position;

                    }
           
                
                    
          
          
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
          
             
                  $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
      $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
       $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
               
              //  continue;
              $con=  $this->get_content($url,'body');
              if (!$con) {
                                   
                  continue;
              }
              $content=  $con->find('.pd__details',0);
              if (!$content) { var_dump(11111111111111);
                  continue;
              }
     
              
              $title=  trim($content->find('h1',0)->plaintext);
              
            $det=$content->find('.pd__table',0);
            foreach ($det->children as $tr) {
                $td=$tr->find('td',0)->plaintext;
                if (strpos($td, 'PZN')!==false) {
                     $pzn =$this->strict_numbers($tr->find('td',1)->plaintext);
                     break;
                }
                
            }
           
            
                  
           $status=$det->find('img',0)->getAttribute('src');
           if ($status==='/templates/pharmacy/p/img/euva/green.png') {
               $availability=1;
           }else{
                   $availability=0;
                 }
                  
                
                  $price=  $this->money_format($content->find(".price",0)->plaintext);
              
              
              
              
              
              
             
               
              
              
               
              
              $description=$con->find('#pddetailsanchor',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
             
              
             
              $kws=  $this->get_kws($con->find('#breadcrumb',0));
              $ul=$con->find('#breadcrumb',0)->find('ul');
              $categories='';
              for ($i = 0; $i < count($ul); $i++) {
                  $categories.=trim($ul[$i]->plaintext).">";
              }
              $e= array_map('trim', explode('›', substr(trim($categories), 0,-1)));
              array_pop($e);
              $category=  implode('>', $e);
              
              $datetime=date("Y-m-d H:i:s");
             $cross_seling=$this->cross_seling($con);
             
            /*    
              var_dump(   $title,
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
      
      
       
          protected function cross_seling($c){
           
          $arr=[];
          if (!$cross=$c->find('.carousel__mini')) {
              return "";
          }
         
          
                for ($i = 0; $i < count($cross); $i++) {
                    $img=$cross[$i]->find('img',0)->getAttribute('data-original');
                    
                 $pzn=  $this->strict_numbers($img);
               $arr[]=$pzn; 
             
              }
          
          
         
          
          
     
          if (count($arr)===0) {
              return '';
          }
           
         return implode(',', array_unique(array_filter($arr)));
          
          
          
          
          
      }
      
      
     
      
             protected function search_pzn(){
          $pzns=  parent::search_pzn(self::URL);
          $search_url='https://www.eu-versandapotheke.com/search/result?term=%s&row=0&order_by=Relevance&order_direction=DESC';
          
          
           $domain=  substr(self::URL, 0,-1);
              
       $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
        $this->setOpt(CURLOPT_FOLLOWLOCATION,true,$this->curl);
      $this->setOpt(CURLOPT_MAXREDIRS,10,$this->curl);
       $this->setOpt(CURLOPT_SSL_VERIFYPEER,false,$this->curl);
     // $v="10333719";
         foreach ($pzns as $v) {
        $content=  $this->get_content(sprintf($search_url, $v)); 
          $headers=$this->getResponseHeaders();
         if (isset($headers['Location'])) {
             $Location=$domain.$headers['Location'];
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
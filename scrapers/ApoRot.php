<?php
//include './KaskeScraper.php';
/*
 * 
 * 
 * done 3
 * 
 * 
 * 
 * 
 * 
 */
class ApoRot extends KaskeScraper{
    
    const URL="http://www.apo-rot.de/";
    protected $links=[];
    protected $products_url=[];
    const AV_IMAGE='http://bilder.apo-rot.de/img/icons/in_stock.jpg';
    protected $save_links=false;
    const ID=1;
    protected $position=1;

    public function __construct($search=FALSE) {
             parent::__construct();
             
             
             if ($search) {
                 $this->search_pzn();
                 return false;
                 
                 
                 
                 
             }
             
             
             
             
             
             
             
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
           $box=$content->find('#left_menu_entries',0);
           $a=$box->find('a');
           for ($i = 0; $i < count($a); $i++) {
              $link=$domain.$a[$i]->getAttribute('href');
            //  var_dump($link);
            $this->position=1;
              if (strpos($link, '/produkte/')!==FALSE) {
                    var_dump($link);
                  
                  $con=  $this->get_content($link,'#td_center');
                  if (!$con) {
                      continue;
                  }
                  $box=$con->find('.p_box');
                  for ($y = 0; $y < count($box); $y++) {
                
                      $table=$box[$y]->find('table',0);
                      if (!$table) {
                          continue;
                      }
                      $pzn=trim($table->find('tr',1)->find('td',1)->plaintext);
                       if (in_array($pzn, $this->pzn,TRUE)) {
                           $details=$domain.$table->find('tr',0)->find('td',0)->find('a',0)->getAttribute('href');
                          // var_dump($details);
                         //  $this->products_url[]=$details;
                           if ($this->save_links) {
                            //  $this->mysql->insert_links(self::ID,$details,  $this->position, $this->get_kws($con->find('#menu_navi',0)),$link); 
                           }
                            $this->products_url[]=[
                                 'link'     =>$details,
                                 'position' =>  $this->position,
                                 'kws'      =>  $this->get_kws($con->find('#menu_navi',0)),
                                'from_link' =>$link
                             ];
                           //  var_dump($this->products_url);
                        }
                        ++$this->position;
                  }
              }
         } 
          
          
          
          // $this->save_data();
          
          
          
      }
    
   
    
      
      protected function save_data(){
          var_dump('save_data called');
          
           if ($this->save_links) {$this->products_url=  $this->mysql->get_links_db(self::ID);}
           
         // var_dump($this->products_url);
          foreach ($this->products_url as $v) {
             $url=$v['link'];
                     
                     $position=$v['position'];
              $keywords_from_link=  strtolower($v['kws']);
               $from_link=$v['from_link'];
          
             
                 var_dump($url);
              
              $content=  $this->get_content($url,'#shop');
               if (!$content) {
                   continue;
              }
              
              $con=  $content->find('#td_center',0);
             
              
              
              
              $title= trim($con->find('h1',0)->plaintext);
              $description= $con->find('#tabs-1',0)->find('.details_text',0)->innertext;
              //$pzn
            
             
               $table=$con->find('table',0)->find('form',0)->find('table',0);
             
               $pzn=  trim($table->last_child()->prev_sibling()->find('td',1)->plaintext);
               $price=  $this->money_format( $con->find('.big',0)->plaintext);
              
              
              $status=$table->last_child()->find('img',0);/*->getAttribute('src')===self::AV_IMAGE?1:0;*/
              if ($status) {
                  $availability=1;
              }else{
                  $availability=0;
              }
              $domain= self::URL;
              $link=$url;
              
              
              
              
              
              
              $kk=$content->find('#menu_navi',0)->find('span');
              $cat='';
              for ($i = 0; $i < count($kk); $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=  str_replace("'", '', substr($cat, 0,-1));
               
              
                $brd=  $this->get_kws($content->find('#menu_navi',0));
              $exp= array_unique(explode(',', $brd));
              array_pop($exp);
             $kws=  implode(',', $exp);
             
             
             
             
              $datetime=date("Y-m-d H:i:s");
              
              
              
               $cross_seling=$this->cross_seling($con);
              
              
              
              
              
              
                 
           /*      
              var_dump(   
                   $title,
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
             
               
            //  break;$title,$desc,$price,$pzn,$domain,$link,$avaib,$kws,$date
          }
         // var_dump($this->products_url);
  
      }
      
      
  
      
       
      protected function cross_seling($c){
          //productCrossSell productAlsoBought
         
          
          $arr=[];
          
          
          $cross=$c->find('#pager',0);
          if ($cross) {
              $cross_pzn=$cross->find('a');
              if ($cross_pzn) {
                         for ($i = 0; $i < count($cross_pzn); $i++) {
                       $href=$cross_pzn[$i]->getAttribute('href');      
                        $e=  explode('.html', $href);
                        $pzn=  substr($e[0], strrpos($e[0], '/')+1);
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
          $search_url='http://www.apo-rot.de/index_search.html?_formname=searchform&_errorpage=%2Findex.html&_command=SearchReroute&'
                  . '_validation=34353434&_filterfastsearch2=x&_filteronlyInMenu=x&_filteravgrank=x&_filternolimits=x'
                  . '&_filterfixedrankgroup=x&_filtersearchkat=&_filterktext=';
          
          
           $domain=  substr(self::URL, 0,-1);
           
           foreach ($pzns as $v) {
             
          $content=  $this->get_content($search_url.$v); 
          $box=$content->find('.p_box',0);
          if ($box) {
             $a=$box->find('a',0);
             $href=$domain.$a->getAttribute('href');
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


 



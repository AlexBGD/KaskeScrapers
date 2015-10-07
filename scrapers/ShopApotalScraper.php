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
class ShopApotalScraper extends KaskeScraper{
    
    const URL="http://shop.apotal.de/";
    protected $links=[];
    protected $products_url=[];
    protected $save_links=false;
    const ID=14;
    protected $position=1;
    protected $check_links=[];
    

    public function __construct() {
             parent::__construct();
             
             
               $this->save_links=true;
                  $this->parse_links(); 
             
          /*  if ($this->save_links) {
                 $this->save_data();
             }else{
                   $this->parse_links();
             } */
      }
    
    
      protected function parse_links(){
       
           $get=  $this->get(self::URL);
           $response = $this->getResponse();
           $content = str_get_html($response);
            $domain=  substr(self::URL, 0,-1);
            $box=$content->find('#treeMenu',0);
            $a=$box->find('a');
          for ($i = 0; $i < count($a); $i++) {
               $link=  trim( $domain.$a[$i]->getAttribute('href'));
                var_dump($link);
               $c=  $this->get_content($link,'#container');
              
               $sub=  $this->sub_cats($c,$link);
               
               
          }
       
          
         
          
      }
       
      
      protected function sub_cats($con,$from){
          if (!$con) {
              return false;
          }
           $this->parse_pagination($con,$from);
          $cats=$con->find('.categoryLinks',0);
          if (!$cats) {
             
              return false;
          }
           $domain=  substr(self::URL, 0,-1);
          $a=$cats->find('a');
          for ($i = 0; $i < count($a); $i++) {
              $link=$domain.$a[$i]->getAttribute('href');
              var_dump('from sub cats: '.$link);
              $c=  $this->get_content($link,'#container');
              
              $this->sub_cats($c,$link);
          }
          
          
          
      }

      









      protected function parse_pagination( $con,$from){
          if (!$con) {
              return FALSE;
          }
          var_dump('parse_pagination');
          $this->position=1;
           $domain=  substr(self::URL, 0,-1);
          $content=$con->find('#content',0);
          $this->save_links($content,$from);
          
          while (TRUE){
              
              $pagination=$content->find('.boxNavigation',0);
              if (!$pagination) {
                  break;
              }
              
              $d=$pagination->find('.displayPagination',0);
              $next=$d->find('.btnNext',0);
              if (!$next) {
                  break;
              }
              $link=$domain.$next->getAttribute('href');
               var_dump('from pagiantion: '.$link);
              $content=  $this->get_content($link,'#content');
              $this->save_links($content,$from);
              
              
              
              
              
              
              
          }
          
          
          
           
           
           
       }
          
          
          
          
          
          
       protected function save_links($content,$from){
                            $products=$content->find('.productsList',0); 
                            if (!$products) { 
                                return false;
                            }
                     $box=$products->find('.boxProduct');
                     $domain=  substr(self::URL, 0,-1);
                    for ($i = 0; $i < count($box); $i++) {
                        $link=$domain.$box[$i]->find('a',0)->getAttribute('href');
                        $info=$box[$i]->find('.productInfos',0);
                        $pzn=  $this->strict_numbers($info->find('.pzn',1)->plaintext);
                        if (in_array($pzn, $this->pzn,TRUE)) {
                            if ($this->save_links) {
                                         //  $this->mysql->insert_links(self::ID,$link,  $this->position, 
                                            //       $this->get_kws($content->find('.navigatorBox',0)),$from); 
                            }
                                        $this->products_url[]=[
                                            'link'     =>$link,
                                            'position' =>  $this->position,
                                            'kws'      =>  $this->get_kws($content->find('.navigatorBox',0)),
                                            'from_url'  =>$from
                                        ];
                                        //   var_dump($link,  $this->position,$pzn,$this->get_kws($content->find('.navigatorBox',0)));
                            }
                        



                        ++$this->position;

                    }
           
                
                    return $content;
          
          
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
          
             
               
               
              //  continue;
              $con=  $this->get_content($url,'#content');
              if (!$con) {
                  continue;
              }
              $content=  $con->find('#productDetail',0);
              if (!$content) {
                  continue;
              }
     
              
              $title=  trim($content->find('h1',0)->plaintext);
              
              $info=$content->find('.informations',0);
              
              
              $pzn =$this->strict_numbers($info->find('.pzn',1)->plaintext);
              $price=  $this->money_format($info->find('.yourPrice',1)->plaintext);
              
              
              
              
              
              
              $avb=$info->find('.productAvailability',0);
              $status1=$avb->find('.status1',0);
              $status2=$avb->find('.status2',0);
              if ($status1||$status2) {
                 $availability=1;
                 
                 
              }else{
                      $availability=0;
                 }
              
              
              
              
               
              
              $description=$content->find('.description',0)->innertext;
              $domain= self::URL;
              $link=$url;
              
             
              
             
               $kws=   $this->get_kws($con->find('.navigatorBox',0)).",".$keywords_from_link;
               $kk=$con->find('.navigatorBox',0)->find('a');
              $cat='';
              for ($i = 0; $i < count($kk)-1; $i++) {
                  $cat.=trim($kk[$i]->plaintext).'>';
              }
              $category=substr($cat, 0,-1);
           //   var_dump($category);
             
            
              $datetime=date("Y-m-d H:i:s");
                $cross_seling=$this->cross_seling($con);
          //    var_dump($cross_seling);
         /*
              var_dump(  $title,
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
           
              $cross=$c->find('.productDetailOther',0);
          
              $arr=[];
              if ($cross) {
                  $a=$cross->find('.pzn');
                  for ($i = 0; $i < count($a); $i++) {
                      $e=  $this->strict_numbers($a[$i]->plaintext);
                      $arr[]=$e;
                   
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
          if (in_array($pzn, $this->pzn)) {
              return $pzn;
          }
          return false;
          
          
          
          
      }
      
      
      
      
      
      
      
      
    
}
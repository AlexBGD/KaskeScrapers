<?php






class db_connect {
    
    const HOST='localhost';
    const USER="root";
    const PASS="pass";
    const DB='drkaskescraper';
    
    private $mysqli;

    protected $all_url=false;






    public function __construct() {
        
            $mysqli = new mysqli(self::HOST, self::USER, self::PASS, self::DB);
  if (!$mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
    exit();
} 
            if ($mysqli->connect_error) {
                die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
            }
            if (mysqli_connect_error()) {
                die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
            }
            $this->mysqli=$mysqli;
         //   echo 'Success... ' . $mysqli->host_info . "\n";
    }
    
    
    public function insert_data($title,$desc,$price,$pzn,$domain,$link,$avaib,$kws,$position,$date,$from_link,$price_history,$category,$kws_history,$cat_history,$cross_seling){
                    if (!$this->all_url) {
                        $this->all_url=  $this->get_links();
                     }
                      
                     $or_link=$link;
                        //product 1
                $title = '"'.$this->mysqli->real_escape_string($title).'"';
                $desc = '"'.$this->mysqli->real_escape_string($desc).'"';
                $price = '"'.$this->mysqli->real_escape_string($price).'"';
                $pzn = '"'.$this->mysqli->real_escape_string($pzn).'"';
                $domain = '"'.$this->mysqli->real_escape_string($domain).'"';
                $link = '"'.$this->mysqli->real_escape_string($link).'"';
                $avaib = '"'.$this->mysqli->real_escape_string($avaib).'"';
                $kws = '"'.$this->mysqli->real_escape_string($kws).'"';
                $date = '"'.$this->mysqli->real_escape_string($date).'"';
                $from_link = '"'.$this->mysqli->real_escape_string($from_link).'"';
                $category = '"'.$this->mysqli->real_escape_string($category).'"';
                $cross_seling = '"'.$this->mysqli->real_escape_string($cross_seling).'"';
                
               // $price_history = json_encode($price_history);
                
                if (in_array($or_link, $this->all_url,TRUE)) {
                    $key=  array_search($or_link, $this->all_url);
                    unset($this->all_url[$key]);
                    
                  
                    
                    
                    
                   $his_p=  $this->get_history('price_history',$or_link);
                 
                   
                  
               //    var_dump($his_p);die();
                    
                        $results = $this->mysqli->query("UPDATE crawler_data SET "
                                . "title=$title,"
                                . "description=$desc,"
                                . "pzn=$pzn,"
                                . "price=$price,"
                                . "kws=$kws,"
                                . "datetime=$date,"
                                . "domain=$domain,"
                                . "availability=$avaib,"
                                . "link=$link,"
                                . "position=$position,"
                                . "from_link=$from_link,"
                                . "price_history='". json_encode( array_merge($his_p,$price_history))."',"
                                . "category=$category,"
                                . "kws_history='". json_encode( array_merge($this->get_history('kws_history',$or_link),$price_history))."',"
                                . "cat_history='". json_encode( array_merge($this->get_history('cat_history',$or_link),$price_history))."',"
                                . "cross_seling=$cross_seling"
                                . " WHERE link=$link limit 1");

                        //MySqli Delete Query
                        //$results = $mysqli->query("DELETE FROM products WHERE ID=24");

                        if($results){
                            print 'Success! record updated '; 
                        }else{
                            print 'Error : ('. $this->mysqli->errno .') '. $this->mysqli->error;
                        }
                    
                    
                }else{
                    
                     //Insert multiple rows
                $insert = $this->mysqli->query("INSERT INTO crawler_data(title,description,pzn,price,kws,datetime,domain,availability,link,position,
                    from_link,price_history,category,kws_history,cat_history,cross_seling) VALUES($title,$desc,$pzn,$price,$kws,$date,$domain,$avaib,$link,$position,$from_link,"
                        . "'".  json_encode($price_history)."',$category,'".  json_encode($kws_history)."'"
                        . ",'".  json_encode($cat_history)."'"
                        . ",'$cross_seling')");

                if($insert){
                    //return total inserted records using mysqli_affected_rows
                    var_dump('Success! Total ' .$this->mysqli->affected_rows .' rows added.'); 
                }else{
                    die('Error : ('. $this->mysqli->errno .') '. $this->mysqli->error);
                }
                    
                    
                    
                }
       
        
    }
    
    public function get_history($column,$link){ 
       $link='"'. $this->mysqli->real_escape_string($link).'"';
     //  $column='"'. $this->mysqli->real_escape_string($column).'"';
             $results = $this->mysqli->query("SELECT  $column FROM crawler_data where link=$link limit 1");
             $arr=[];
              
            while($row = $results->fetch_array()) {
                if ($row[$column]) {
                    $arr[]=$row[$column]; 
                }
                 
               
            }  
            if (count(array_filter($arr))===0) {
                return [];
            } 
            // Frees the memory associated with a result
            $results->free();
            return json_decode($arr[0],TRUE);
        
        
    }

    



    public function get_pzn(){
 
            //MySqli Select Query
            $results = $this->mysqli->query("SELECT pzn,zero_pzn FROM products_new");
             $arr=[];
            while($row = $results->fetch_array()) {
                $arr[]=  trim($row['pzn']);
                $arr[]=  trim($row['zero_pzn']);

            }   

            // Frees the memory associated with a result
            $results->free();
            return array_unique($arr);
      
    }
    
    public function get_negative_kws(){
          
            $results = $this->mysqli->query("SELECT kws FROM negative_kws");
             $arr=[];
            while($row = $results->fetch_array()) {
                $arr[]=  strtolower($row['kws']);

            }   

            // Frees the memory associated with a result
            $results->free();
            return $arr;
      
    }
    
    
    
    
    
    public function previev_data($title,$desc,$price,$pzn,$domain,$link,$avaib,$kws,$date){
        
        
        ?>

<h1><a href="<?=$link?>"><?=$title?></a></h1>
<div>
    <?=$desc?>
</div>
<ul>
    <li>Price: <?=$price?></li>
    <li>Price: <?=$pzn?></li>
    <li>Avaiability: <?=$avaib?></li>
    <li>Kws: <?=$kws?></li>
    <li>Domain: <?=$domain?></li>
    <li>Date: <?=$date?></li>
</ul>

 
        <?php
       
    }
    
    
    protected function get_links(){
             $results = $this->mysqli->query("SELECT link FROM crawler_data");
             $arr=[];
            while($row = $results->fetch_array()) {
                $arr[]=$row['link'];

            }   

            // Frees the memory associated with a result
            $results->free();
            return $arr;
        
        
    }
    
    public function insert_links($id,$link,$position,$kws,$from_link=""){
        
        $link = '"'.$this->mysqli->real_escape_string($link).'"';
         $kws = '"'.$this->mysqli->real_escape_string($kws).'"';
         $from_link = '"'.$this->mysqli->real_escape_string($from_link).'"';
                //Insert multiple rows
                $insert = $this->mysqli->query("INSERT INTO links(id_domain,link,position,kws,from_link) VALUES
                                                             ($id,$link,$position,$kws,$from_link)");

                if($insert){
                    //return total inserted records using mysqli_affected_rows
                    var_dump('Success! Total ' .$this->mysqli->affected_rows .' rows added.<br />'); 
                }else{
                    die('Error : ('. $this->mysqli->errno .') '. $this->mysqli->error." INSRT LINK ERROR");
                }
        
        
        
        
        
    }
    
    public function get_links_db($id){
          $results = $this->mysqli->query("SELECT link,position,kws,from_link FROM links where id_domain=$id");
             $arr=[];
            while($row = $results->fetch_array()) {
                $arr[]=[
                    'link'      =>$row['link'],
                    'position'  =>$row['position'],
                    'kws'       =>$row['kws'],
                    'from_link'=>$row['from_link']
                ];

            }   

            // Frees the memory associated with a result
           // $results->free();
            return $arr;
        
        
        
        
    }
    
        public function search_pzn($domain){
    
        $domain = '"'.$this->mysqli->real_escape_string($domain).'"';
         $results = $this->mysqli->query("SELECT pzn FROM crawler_data where domain=$domain");
             $arr=[];
            while($row = $results->fetch_array()) {
                $arr[]=$row['pzn'];

            }  
        
        
            return $arr;
    }
    
    
    

        public function get_pzn_multi(){
 
            //MySqli Select Query
            $results = $this->mysqli->query("SELECT pzn,zero_pzn FROM products_new");
             $arr=[];
            while($row = $results->fetch_array()) {
                $arr[]=[
                    'pzn'   =>trim($row['pzn']),
                    'zero'  =>trim($row['zero_pzn'])
                    
                ];  ;
               
            }   

            // Frees the memory associated with a result
            $results->free();
            return $arr;
      
    }
    
    
    
    
    
    
}


 

<?php
ini_set('xdebug.max_nesting_level',-1);
//header('Content-Type: text/html; charset=utf-8');
 require_once './KaskeScraper.php';
 //register_shutdown_function( "fatal_handler" );
/*
function fatal_handler() {
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if( $error !== NULL) {
    $err[]   = $error["type"];
    $err[]  = $error["file"];
   $err[]  = $error["line"];
   $err[]  = $error["message"];

    var_dump($err);
  }
}*/

$search=true;
//require_once './scrapers/AlivaScraper.php'; 
 // new AlivaScraper($search);


//require_once './scrapers/ApoRot.php';
//new ApoRot($search);

//require './scrapers/ApodiscounterScraper.php';
//new ApodiscounterScraper($search);



  // require './scrapers/AponeoScraper.php';
  // new AponeoScraper($search);

  //require './scrapers/ApothekeScraper.php';
 //new ApothekeScraper($search); 

  // require './scrapers/BesamexScraper.php';
  // new BesamexScraper($search);



 //require './scrapers/DelmedScraper.php';
//new DelmedScraper($search);


//require './scrapers/DocMorrisScraper.php';
// new DocMorrisScraper($search);

 //require './scrapers/EuVersandapothekeScraper.php';
 //new EuVersandapothekeScraper($search);

 //  require './scrapers/EuropaApotheekScraper.php';
// new EuropaApotheekScraper($search);

 require './scrapers/JuvalisScraper.php';
 new JuvalisScraper($search);

 //require './scrapers/MediherzScraper.php';
  //new MediherzScraper();

    //require './scrapers/MedikamentePerClickScraper.php';
   //new MedikamentePerClickScraper(); 


 //require './scrapers/MedpexScraper.php';
 //new MedpexScraper();

 //require_once './scrapers/MyCareScraper.php'; 
//new MyCareScraper();

//require './scrapers/SanicareScraper.php';
//new SanicareScraper();

 //require './scrapers/ShopApotalScraper.php';
 //new ShopApotalScraper();

//require './scrapers/ShopApothekeScraper.php';
//new ShopApothekeScraper();

 //require './scrapers/VersadapoScraper.php';
 //new VersadapoScraper();

 //require './scrapers/VitalsanaScraper.php';
 //new VitalsanaScraper();

//require './scrapers/VolksversandScraper.php';
//new VolksversandScraper();

//require './scrapers/ZurRoseScraper.php';
 //new ZurRoseScraper();



  // $Scraper=new KaskeScraper();

   //$Scraper->search_pzn('http://www.apo-rot.de/');

















 












 







  


 

 










<?php

include 'SpellCorrector.php';
// include 'getSnippet.php';
include 'get.php';
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
error_reporting(0);
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

//build filename--url map
$file = fopen("UrlToHtml_NBCNews.csv", "r") or die("Unable to open file!");
$map = array();
while(!feof($file))
{
    $line = fgets($file);
    $item = explode(",", $line);
    $map[$item[0]] = $item[1];
}


if ($query) {

  
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    if(!isset($_GET['Algorithm'])) {
      $_GET['Algorithm']="Lucene";
    }

    if($_GET['Algorithm'] == 'Lucene'){

      $results = $solr->search($query, 0, $limit);

    }else if($_GET['Algorithm'] == 'pageRank') {

      $additionalParameters = array(

        'sort' => 'pageRankFile.txt desc'

      );
      $results = $solr->search($query, 0, $limit, $additionalParameters);
    }
    
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>

    <title>PHP Solr Client Example</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
      .red{
        color: red;
      }
    </style>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" /><br><br>
      <input type="radio" name="Algorithm" value="Lucene"/>Lucene Algorithm
      <input type="radio" name="Algorithm" value="pageRank"/>pageRank Algorithm<br>
      <input type="submit" value="submit" />
    </form>
    <script>
        
        $( "#q" ).autocomplete({
            source: function( request, response ) {
              var str = $("#q").val().toLowerCase();
              if(str.length == 0 || str.charAt(str.length - 1) == " "){
                return;
              }
              var terms = str.split(" ");
              var search_term = terms[terms.length-1].toLowerCase();
              var prefix = "";
              for (var i = 0; i < terms.length-1; i++) {
                prefix += terms[i]+" ";
              }
              $.ajax({
                  //JSONP API

                  url: "http://localhost:8983/solr/myexample/suggest?&q="+search_term,
                  //the name of the callback function
                  jsonp: 'json.wrf',
                  //tell jQuery to expect JSONP
                  dataType: "jsonp",
                  
                  //work with the response
                  success: function(data) {
                    var suggestions = data.suggest.suggest[search_term].suggestions;
                    var result = [];
                    for (var i = 0; i < suggestions.length; i++) {
                      var item = prefix + suggestions[i].term;
                      result.push(item);
                    }
                    response(result);
                  }
                });
                
            }
        });

        
    </script>
    
    <?php 
      if($query){
        
        $query = strtolower($query);
        $terms = explode(" ",$query);
        $correct_terms = array();
        $is_spell_error = false;
        for($i=0; $i<sizeof($terms); ++$i){
          $term = $terms[$i];
          $correct_term = strtolower(SpellCorrector::correct($term));
          if($term != $correct_term){
            $is_spell_error = true;
          }
          array_push($correct_terms,$correct_term);
        }
        if($is_spell_error){
          $correct_terms = implode(" ",$correct_terms);
          ?>
          Did you mean: <a href="query.php?q=<?=$correct_terms?>"><?= $correct_terms; ?></a>
          <?php
        }
      }
    ?>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid grey; text-align: left; border-radius: 5px;background-color: rgb(128, 191, 255, 0.5)">
<?php
    // iterate document fields / values
	$url="";
	$title="";
	$id="";
    foreach ($doc as $field => $value)
    {
   		
    	if($field=="og_description"){

?>

			<tr>
            	<th>Description</th>
            	<td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          	</tr>
<?php
    	}
    	if($field=="id"){
    		$id=$value;
?>
    		<tr>
            	<th>ID</th>
            	<td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
      	</tr>
<?php
    	}
    	if($field=="title"){
    		$title=$value;
    	}
    	if($field=="og_url"){
    		$url=$value;
    	}
?>
          

<?php
    }
	    if($url==""){
	    	$key=explode("HTML files/", $id);
	    	$url=$map[$key[1]];
	    }
?>
		  <tr>
            <th>Title</th>
            <td><a href="<?php  echo $url  ?>"><?php echo $title; ?></a></td>
          </tr>
          <tr>            
            <th>URL</th>
            <td><a href="<?php  echo $url  ?>"><?php echo $url; ?></a></td>   
          </tr>
          <tr>
            <td><?php 
                  $snippet = excerpt($query,$id);
                  $queryWords = explode(" ", $query);
                  if($snippet==""){
                    echo "none";
                  }else{
                    echo $snippet;
                  }
                  
                ?>

            </td>
          </tr>
        </table>
      </li>
<?php	
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
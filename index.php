<?php

use Google\AdsApi\Examples\AdWords\v201809\BasicOperations\GetKeywords;

require './GetKeywordIdeas.php';

$variants = [
    "PPT",
    "PowerPoint",
    "Presentation",
    "Slides",
    "Template",
    "PPT Templates",
];

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keyword Research Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
 
    <style>
    .bd-form{
        padding: 1.5rem;
        margin: 1rem -.75rem 0;
        margin-right: 130px;
        margin-left: 130px;
        border-width: 1px;
        border-top-left-radius: .25rem;
        border-top-right-radius: .25rem;
        border: solid #dee2e6;
    }
    </style>
</head>
<body>
<div class="wide-container">
    <div class="bd-form">
        <h1>Keyword Search Tool</h1>
        <hr>
        <form method="GET" >
        <div class="mb-3">
            <label for="keywords" class="form-label">Keywords</label>
            <input type="text" class="form-control" id="keywords" aria-describedby="keywords" name="keywords" value="<?php 
               if(isset($_GET['keywords'])){
                   echo $_GET['keywords'];
            } 
            ?>">
            <div id="keywordsHelp" class="form-text">Seperate multiple keywords with a comma (,).</div>
        </div>
        <!--div class="mb-3">
            <label for="customRange3" class="form-label">Number of Results: </label>
            <input type="number" class="form-control" name="results" id="customRange3" value="<?php 
               if(isset($_GET['results'])){
                   echo $_GET['results'];
                }else {echo "6"; } 
            ?>">
        </div-->

        <button type="submit" class="btn btn-primary">Submit</button>

       
        </form>

        <?php 

            $flag = 0;

            $keywords = false;
            if(isset($_GET['keywords'])){
                $keywords = $_GET['keywords'];
                $flag = 1;
            }

            $results = false;
            if(isset($_GET['results'])){
                $results = (int)$_GET['results'];
                $flag = 2;
            }

            if($flag != 0 && $keywords!=""){
                echo '<hr>';
                echo $keywords;
                echo $results;
                echo '<hr>';
                $a = new GetKeywordIdeas();
                $res = $a->getMultipleKeywordIdeas($keywords, 7, $variants);
                //echo json_encode($res);

                echo '
                <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Keyword</th>
                    <th scope="col">Volume</th>';
                    for($i=1; $i <= count($variants); $i++){
                        echo '<th scope="col">Var ' . $i  . '</th><th scope="col">Volume</th>';
                    }
                    echo '<th scope="col">Total</th></tr>
                </thead>
                <tbody>';
                $count = 1;
                foreach($res as $r){
                    echo '<tr>';
                    echo '<th scope="row">'. $count++ .'</th>';
                    $total = 0;
                    for($j=0; $j <=count($variants); $j++){
                        echo '<td>' . $r[$j][0] . '</td>
                        <td>'. $r[$j][1] .'</td>';
                        
                        if($j > 0) {$total += (int)$r[$j][1];}
                    }
                    echo '<th scope="col">'. $total .'</th>';
                    echo '<tr>';
                }
                echo '</tbody></table>';

            }

            


        ?>
        
        
        
    </div>
</div>
    
</body>
</html>


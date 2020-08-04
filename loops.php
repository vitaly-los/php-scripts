<?php
for($i = 1;$i<=5;$i++){
    for($j=1;$j<=$i;$j++){
        echo "*";
    }
    echo "<br>";
}
?>

<?php
for($i = 5;$i>=1;$i--){
    for($j=0;$j<$i;$j++){
        echo "*";
    }
    echo "<br>";
}
?>

<?php
for($i = 1;$i<=5;$i++){
    for($j=1;$j<=$i;$j++){
        echo $i;
    }
    echo "<br>";
}
?>

<?php
$k = 1;
for($i = 1; $i<=4; $i++){
    for($j=1; $j<=$i; $j++){
        echo $k;
        $k++;
    }
    echo "<br>";
}
?>

<html>
<head><title>Buy Products</title></head>
<body>
<?php
session_start();
if(!isset($_SESSION['items'])){
    $_SESSION['items']=array();
}
error_reporting(E_ALL);
ini_set('display_errors','On');

function get_contents($id){
    $fil='http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$id;
    $get_catogories=file_get_contents($fil);
    $get_catogories_xml=new SimpleXMLElement($get_catogories);
//    header('Content-Type: text/xml');
    return $get_catogories_xml;
}

////print $xmlstr;
//$id=72;
//$fil='http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$id;
//$get_catogories=file_get_contents($fil);
//$get_catogories_xml=new SimpleXMLElement($get_catogories);
////header('Content-Type: text/xml');
////print $get_catogories;
//print $get_catogories_xml->category->categories->category['id'];

function generate_html()
{
    echo '<form action="/project4/buy.php">
   <fieldset><legend>Find products:</legend>
        <label>Category: <select name="category"> ';
    $id=72;
    $catog_list = get_contents($id);

    echo '<option value="'.$id.'">' . $catog_list->category->name . '</option>';
    echo '<optgroup label="Computers:">';

    foreach($catog_list->category->categories->category as $item){
//            echo $item['id']."  name: ".$item->name."   ";
            echo '<option value="'.$item['id'].'">'.$item->name.'</option>';
            echo '<optgroup label="'.$item->name.'">';

            $item_id=$item['id'];
            $item_catogories=get_contents($item_id);
            foreach($item_catogories->category->categories->category as $item_child){
                echo '<option value="'.$item_child['id'].'">'.$item_child->name.'</option>';
            }
            echo '</optgroup>-->';
    }
    echo '</optgroup>-->';
    echo '</select>
        </label>
        <label>Search keywords: <input type="text" name="search"/></label>
                <input type="submit" value="Search"/>
    </fieldset>
</form>';
}
generate_html();

//$id=72;
//$key='dell';
$search_list=array();

if(isset($_GET['category']) && isset($_GET['search']) )
{
    $id=urlencode($_GET['category']);
    $key=urlencode($_GET['search']);
    $shopping_item='http://sandbox.api.shopping.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$id.'&keyword='.$key.'&numItems=20';
    $xmlstr = file_get_contents($shopping_item);
    $xml = new SimpleXMLElement($xmlstr);
//header('Content-Type: text/xml');
//print $xmlstr;
    echo '<table border=1> ';
    foreach($xml->categories->category->items->product  as $prod){
        echo '<tr><td>';
        $id=$prod['id'];
        echo '<a name="buy" href="'."/project4/buy.php?buy=".$prod['id'].'" > <img src="'.$prod->images->image->sourceURL .'" /> </a>';
        echo '</td> <td>'.$prod->name.' </td>';
        echo '<td align="right">'.$prod->minPrice.' </td>';
        echo '<td>'.$prod->fullDescription.' </td>';
        echo '<td> <a href="'.$prod->productOffersURL .' "> '.$prod->productOffersURL .'</a> </td>';
        echo '</tr>';

//        $search_list['$prod["id"]']=$prod->images->image->sourceURL.',,,'.$prod->name.',,,'.$prod->minPrice.',,,'.$prod->productOffersURL;
//        echo $search_list['$prod["id"]'];
        $_SESSION['items']["$id"]=$prod['id'].',,,'.$prod->images->image->sourceURL.',,,'.$prod->name.',,,'.$prod->minPrice.',,,'.$prod->productOffersURL;
//        echo $_SESSION['items']['$prod["id"]'];
//        echo  $_SESSION['items']["36448853"] ;

    }
//    echo  $_SESSION['items']['36448853'] ;

    echo '</table>';
}
if(isset($_GET['buy']) && isset($_SESSION['items']))
{
    $id='';

    $id=(string)$_GET['buy'];

    if(!isset($_SESSION['cart'])){
        $_SESSION['cart']=array();
    }
    $_SESSION['cart']["$id"]=$_SESSION['items']["$id"];

    echo 'Shopping Basket:</p><table border=1>';

    $total=0;
    foreach($_SESSION['cart'] as $item_string){

    $cart_item=explode(',,,',$item_string );
    echo '<tr><td><img src="'. $cart_item[1].'"/> </td>';
    echo '<td>' .$cart_item[2].'   </td>';
    echo '<td>'.$cart_item[3].'</td>';
    $total=$total+$cart_item[3];
    echo '<td><a href="'.$cart_item[4].'" >'.$cart_item[4].'</a> </td>';
    echo '<td> <a href="'."/project4/buy.php?delete=".$cart_item[0].'" >'."delete".' </a> </td> </tr>';

}

    echo '</table>';

    echo "Total:".$total;
    echo '<form action="/project4/buy.php" method="GET"> <input type="hidden" name="clear" value="1"/>
            <input type="submit" value="Empty Basket"/>
                </form> ';



//    echo  $_SESSION['items']["$id"] ;
}
if(isset($_GET['delete']) ){
    $del_id=(string)$_GET['delete'];
//    session_unset();

    unset($_SESSION['cart']["$del_id"]);
    echo 'Shopping Basket:</p><table border=1>';

    $total=0;
    foreach($_SESSION['cart'] as $item_string){

        $cart_item=explode(',,,',$item_string );
        echo '<tr><td><img src="'. $cart_item[1].'"/> </td>';
        echo '<td>' .$cart_item[2].'   </td>';
        echo '<td>'.$cart_item[3].'</td>';
        $total=$total+$cart_item[3];
        echo '<td><a href="'.$cart_item[4].'" >'.$cart_item[4].'</a> </td>';
        echo '<td> <a href="'."/project4/buy.php?delete=".$cart_item[0].'" >'."delete".' </a> </td> </tr>';

    }
    echo '</table>';
    echo "Total:".$total;
    echo '<form action="/project4/buy.php" method="GET"> <input type="hidden" name="clear" value="1"/>
            <input type="submit" value="Empty Basket"/>
                </form> ';
}
if(isset($_GET['clear'])){
    $clear=$_GET['clear'];
    if($clear=="1")
    {
        session_unset();
    }
}


?>







</body>




</html>

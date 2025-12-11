<?php
require '../../_base.php';
$_category=$_db->query('SELECT * FROM category')->fetchAll();
$selectCategory=get('category');
$selectMinPrice=get('minPrice');
$selectMaxPrice=get('maxPrice');
$searchInput = get('searchInput');
$searchKeyword = "%$searchInput%";

if($selectMinPrice)
{
    $GLOBALS['minPrice'] =  (int)$selectMinPrice;

}else 
{$GLOBALS['minPrice'] = 0;
$selectMinPrice=0;
}

if($selectMaxPrice)
{
    $GLOBALS['maxPrice'] = (int)$selectMaxPrice;
}else 
{$GLOBALS['maxPrice'] = 10000;
$selectMaxPrice=  10000;
}

$stm = $_db->prepare('SELECT * FROM product
                      WHERE (price BETWEEN ? AND ?)
                      AND (category_id = ? OR ?)
                      AND (name LIKE ? OR ?)');
$stm->execute([$selectMinPrice, $selectMaxPrice,$selectCategory,$selectCategory==null,$searchKeyword,$searchKeyword==null]);
$_product= $stm->fetchAll();
//html start
echo "<div class=sidebar>";
echo "<h2>Categories</h2>";
echo "<ul>";
echo "<li class=categoryList>";
html_button('categoryButton','','all','filter');
echo "</li>";
foreach ($_category as $category) {
    if($category->status!=false){
    echo "<li class=categoryList>";
    html_button('categoryButton',$category->category_id,$category->name,'filter');
    echo "</li>";
    }
}
echo "</ul>";
echo "<h2>Price Range</h2>";
html_number('minPrice','0','','','class=range');
html_number('maxPrice','0','','','class=range');
html_button('categoryButton','','Submit','filter');
html_button('categoryButton','','Reset','reset');
echo "</div>";
if(!empty($_product))
{
    foreach ($_product as $product) {
        if($product->status!=false){
        echo "<div class=container>";
        echo "<a href=productdetail.php?product_id=$product->product_id>";
        echo "<img src=../../images/$product->product_photo alt=$product->name class=productPhoto>";
        echo "<p>$product->name</p>";
        echo "<p>RM$product->price</p>";
        echo "</a>";
        echo "</div>";
        }
    }

    }

else
{
    echo "<p id='noProduct'>No product found</p>";
}

?>

<?php
    $servername = "localhost";
    $database = "job";
    $username = "job";
    $password = "myPassword";
    $connect = mysqli_connect($servername, $username, $password, $database);
?>

<?php

    function get_products($group){
        
        global $connect;

        $name_products_query = mysqli_query(
            $connect, "select name from products where id_group = $group;"
        );
        
        $id_children_query = mysqli_query(
            $connect, "select id from groups where id_parent = $group;"
        );

        while ($name_products = mysqli_fetch_assoc($name_products_query)['name']){
            print_r($name_products);
            print_r('<br>');
        }

        while ($id_child = mysqli_fetch_assoc($id_children_query)['id']){   
            get_products($id_child);
        }

    }

    function get_groups($group){
        
        global $connect;

        $groups_query = mysqli_query(
            $connect, "select id, id_parent from groups"
        );

        // mysqli_close($connect);

        $groups = array();
        while ($line = mysqli_fetch_assoc($groups_query)){   
            $key = $line['id'];
            $value = $line['id_parent'];
            $groups[$key] = $value;
        }

        $path = array($group);
        $child = $group;
        $parent = $groups[$group];
        
        while ($child != 0) {
            array_push($path, $groups[$child]);
            $child = $parent;
            $parent = $groups[$parent];
        }
    
        function group_tree_building($path){
            
            global $connect;

            $group = array_pop($path);
            
            $id_parent = mysqli_fetch_assoc(
                mysqli_query(
                    $connect,
                    "select id_parent from groups where id = $group"
                )
            )['id_parent'];
            
            $children_query = mysqli_query(
                $connect, "select id, name from groups where id_parent = $group;"
            );

            // mysqli_close($connect);
            

            $list = array("<ul>");
            while ($child = mysqli_fetch_assoc($children_query)){
                $id_child = $child['id'];
                $name_child = $child['name'];
                $count = get_count($id_child);
                
                array_push(
                    $list,
                    "<li><a href='http://localhost/?id=$id_child'>$name_child</a> $count</li>"
                );
                if (end($path) == $id_child){
                    array_push($list, group_tree_building($path));
                }
            }

            array_push($list, "</ul>");
            return implode('', $list);

        }

        print_r(group_tree_building($path));

    }

    function get_count($group){
        
        global $connect;

        $count_query = mysqli_query(
            $connect, "select count(*) as count from products where id_group = $group;"
        );
        
        $id_children_query = mysqli_query(
            $connect, "select id from groups where id_parent = $group;"
        );

        // mysqli_close($connect);
        
        $count = mysqli_fetch_assoc($count_query)['count'];

        while ($id_children = mysqli_fetch_assoc($id_children_query)['id']){
            $count = $count + get_count($id_children);
        }

        return $count;

    }

?>

<!DOCTYPE html>
<html lang="en">
    <head lang="en">
        <meta charset="UTF-8">
        <title>Test 1</title>
    </head>
    <body style="padding: 0px; margin: 0px; height:100vh;">
        <div style="display: inline-block; max-width: 100%; height: auto; overflow: hidden; vertical-align: top;">
            <a href="http://localhost/?id=0">Все товары</a>
            <?php
                if( $_GET["id"] ) {
                    get_groups($_GET["id"]);
                } else {
                    get_groups(0);
                }
            ?>
        </div>
        <div style="display: inline-block; max-width: 100%; height: auto; overflow: hidden; vertical-align: top;">
            <?php
                if( $_GET["id"] ) {
                    get_products($_GET["id"]);
                } else {
                    get_products(0);
                }
            ?>
        </div>
    </body>
</html>

<?php mysqli_close($connect);?>
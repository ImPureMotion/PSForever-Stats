<?php
    //  A local protected file to get username, password, and other data from
    $file_get = json_decode(file_get_contents("./local_user_data/user_data"), true);
    $ip = $file_get['ipaddress'];
    $username = $file_get['username'];
    $password = $file_get['password'];
    $db = $file_get['database'];
    $table_weekly = "table_weekly_stats";
    $table_current = "table_current_stats";
    $url = 'https://play.psforever.net/api/char_stats_cep/0';
    $char_limit = 5000;

    $array = [
        "CREATE USER IF NOT EXISTS '$username' INDENTIFIED VIA mysql_native_password USING '$password';",
        "GRANT SELECT , INSERT, UPDATE, CREATE, EVENT ON *.* TO '$username';",
        "CREATE TABLE IF NOT EXISTS $table_weekly  (character_name VARCHAR(60), faction SMALLINT(3), _rank INT(10), stat BIGINT(255), bep BIGINT(255), cep BIGINT(255), br SMALLINT(2), cr SMALLINT(2));",
        "CREATE TABLE IF NOT EXISTS $table_current (character_name VARCHAR(60), faction SMALLINT(3), _rank INT(10), stat BIGINT(255), bep BIGINT(255), cep BIGINT(255), br SMALLINT(2), cr SMALLINT(2));",
        "CREATE DATABASE IF NOT EXISTS `$db`;",
        "CREATE USER IF NOT EXISTS '$username'"
    ];
    function create_event($minute)
    {
        return "CREATE EVENT IF NOT EXISTS `weekly_stats_0$minute` ON SCHEDULE EVERY 1 WEEK STARTS '2023-05-28 20:0$minute:00.000000' ON COMPLETION NOT PRESERVE ENABLE DO ";
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    //  Connect and initialize user and database
    $link = mysqli_connect($ip, "root", "", "mysql");
    mysqli_query($link, $array[5]);    
    mysqli_query($link, $array[1]);
    mysqli_query($link, $array[4]);
    //  Connect to user and database
    $link = mysqli_connect($ip, $username, "", $db);
    mysqli_query($link, $array[2]);
    mysqli_query($link, $array[3]);

    $setup_event = [
        create_event(0) . "DROP TABLE `$table_weekly`;",
        create_event(1) . "CREATE TABLE IF NOT EXISTS `$table_current`.`$table_weekly` (".
            "`character_name` varchar(60) DEFAULT NULL, ".
            "`faction` smallint(3) DEFAULT NULL, ".
            "`_rank` int(10) DEFAULT NULL, ".
            "`stat` bigint(255) DEFAULT NULL, ".
            "`bep` bigint(255) DEFAULT NULL, ".
            "`cep` bigint(255) DEFAULT NULL, ".
            "`br` smallint(2) DEFAULT NULL, ".
            "`cr` smallint(2) DEFAULT NULL".
          ") ENGINE=InnoDB DEFAULT CHARSET=latin1;",
        create_event(2) . "INSERT INTO `$table_current`.`$table_weekly`(`character_name`, `faction`, `_rank`, `stat`, `bep`, `cep`, `br`, `cr`) SELECT `character_name`, `faction`, `_rank`, `stat`, `bep`, `cep`, `br`, `cr` FROM `$table_current`.`$table_weekly`;",
    ];
    for ($i = 0; $i < sizeof($setup_event); $i++)
    {
        mysqli_query($link, $setup_event[$i]);
    }

    function get_total($cep) 
    {
        return $cep / 100;
    }

    function calculateBR($bep) 
    {
        $thresh = array(
            0  => 0,
            1  => 0,
            2  => 1000,
            3  => 3000,
            4  => 7500,
            5  => 15000,
            6  => 30000,
            7  => 45000,
            8  => 67500,
            9  => 101250,
            10 => 126563,
            11 => 158203,
            12 => 197754,
            13 => 247192,
            14 => 308990,
            15 => 386239,
            16 => 482798,
            17 => 603497,
            18 => 754371,
            19 => 942964,
            20 => 1178705,
            21 => 1438020,
            22 => 1710301,
            23 => 1988027,
            24 => 2286231,
            25 => 2583441,
            26 => 2908442,
            27 => 3237942,
            28 => 3618442,
            29 => 3988842,
            30 => 4488542,
            31 => 5027342,
            32 => 5789642,
            33 => 6861342,
            34 => 8229242,
            35 => 10000542,
            36 => 11501741,
            37 => 12982642,
            38 => 14897142,
            39 => 16894542,
            40 => 19994542
        );
      
        for ($i = sizeof($thresh) - 1; $i > 0; $i--) 
        {
            if ($bep >= $thresh[$i]) 
            {
                return $i;
            }
        }
        return 0;
    }
    function calculateCR($cep) 
    {
        $thresh = array(
            0 => 0,
            1 => 10000,
            2 => 50000,
            3 => 150000,
            4 => 300000,
            5 => 600000
        );
        for ($i = sizeof($thresh) - 1; $i > 0; $i--) 
        {
            if ($cep >= $thresh[$i]) 
            {
                return $i;
            }
        }
        return 0;
    }
    
    $get = file_get_contents($url, false, null, 0, $char_limit);
    $len = strlen($get);
    for ($i = 0; $i < 1000; $i++) 
    {
        $json = json_decode($get . ']}', true);
        if ($json != null) 
        {
            break;
        }
        $get = substr($get, 0, $len--);
    }

    $len = sizeof($json['players']);
    if ($len > 50) 
    {
        $len = 50;
    }
    //  Table and stats initialize
    for ($i = 0; $i < $len; $i++) 
    {
        $name    = $json['players'][$i]['name'];
        $faction = (int)$json['players'][$i]['faction_id'];
        $bep     = (int)$json['players'][$i]['bep'];
        $cep     = (int)$json['players'][$i]['cep'];
        $br      = calculateBR($bep);
        $cr      = calculateCR($cep);
        $total   = (int)get_total($cep);
        $check   = mysqli_query($link, "SELECT `character_name` FROM $table_current WHERE `character_name` = '$name'");
        if ($check->num_rows == 0) 
        {
            mysqli_query($link, "INSERT INTO $table_current VALUES ('$name', $faction, 0, $total, $bep, $cep, $br, $cr);");
        } 
        else 
        {
            //  Skipping creating two tables and make this update happen once a week.
            mysqli_query($link, "UPDATE $table_current SET `stat`= $total,`bep`= $bep,`cep`= $cep,`br`=$br,`cr`= $cr WHERE `character_name` = '$name'");
        }
    }
    //  Get unique stat per index from JSON
    function get_unique_stat($link, $json)
    {
        $len = 50;
        $array = [$len];
        $table_weekly = "table_weekly_stats";
        $table_current = "table_current_stats";
        //  Declare values
        for ($i = 0; $i < $len; $i++) 
        {
            $name    = $json['players'][$i]['name'];
            $current = mysqli_query($link, "SELECT * FROM $table_current WHERE character_name = '$name';")->fetch_row();
            //$name    = $current[0];
            $faction = $current[1];
            $bep     = $current[4];
            $cep     = $current[5];
            $br      = calculateBR($bep);
            $cr      = calculateCR($cep);
            $total   = (int)$cep / 100;
            //  Get previous stats state
            $old_total = mysqli_query($link, "SELECT * FROM $table_weekly WHERE character_name = '$name';")->fetch_row();
            $array[$i] = array(
                'rank'    => (int)$old_total[2],
                'change'  => 0,
                'faction' => $faction,
                'name'    => $name,
                'total'   => $total - (int)$old_total[3],
                'br'      => $br,
                'cr'      => $cr,
                'bep'     => $bep - (int)$old_total[4],
                'cep'     => $cep - (int)$old_total[5]
            );
        }
        //  Sorting indices from $array to $sort and returning $sort
        $sort = array_fill(0, $len, array(
            'rank'    => 0,
            'change'  => 0,
            'faction' => 0,
            'name'    => '',
            'total'   => 0,
            'br'      => 0,
            'cr'      => 0,
            'bep'     => 0,
            'cep'     => 0
        ));
        $num = -1;
        $num2 = 0;
        $index = -1;
        $maxvalue = 0;
        $TRIES = 0;
        while (++$index < $len)
        {
            while (++$num < $len)
            {
                if ($maxvalue < $array[$num]['total'])
                {
                    $maxvalue = $array[$num]['total'];
                    $num2 = $num;
                }
            }
            if ($maxvalue == 0)
            {
                continue;
            }
            $_name = $array[$num2]['name'];
            $array[$num2]['total'] = -1;
            $old_total = mysqli_query($link, "SELECT * FROM $table_weekly WHERE `character_name` = '$_name';")->fetch_row();
            $sort[$index] = $array[$num2];
            $sort[$index]['total'] = $maxvalue;
            $sort[$index]['rank'] = (int)$index;
            $sort[$index]['change'] = (int)$old_total[2] - (int)$index;
            $maxvalue = 0;
            $num = -1;
            mysqli_query($link, "UPDATE $table_current SET `_rank`= $index WHERE `character_name` = '$_name';");
            $num2 = -1;
        }
        return $sort;
    }
    function output_string($array, $len)
    {
        function get_image_tag($faction) 
        {
            switch ($faction)
            {
                case 0:
                    return 'Images/Empires-tr-icon.webp';
                case 1:
                    return 'Images/Empires-nc-icon.webp';
                case 2:
                    return 'Images/Empires-vs-icon.webp';
                default:
                    return -1;
            }
        }
        if ($array == -1 || $array[0] == "")
        {
            return "";
        }
        $output = "";
        for ($i = 0; $i < $len; $i++)
        {
            if ($array[$i]['total'] == 0)
            {
                continue;
            }
            $id = $array[$i]['name'];
            $output .=
                "<tr id='$id'>".
                "<td>".$array[$i]['change']."</td>".
                "<td>".($array[$i]['rank'] + 1)."</td>".
                "<td><img src='".get_image_tag($array[$i]['faction'])."' style='width:33%;' /></td>".
                "<td>".$array[$i]['name']."</td>".
                "<td>".$array[$i]['total']."</td>".
                "<td>".$array[$i]['br']."</td>".
                "<td>".$array[$i]['cr']."</td>".
                "<td>".$array[$i]['bep']."</td>".
                "<td>".$array[$i]['cep']."</td>".
                "</tr>".
                "";
        }
        return $output;
    }
    $output = "";
    $display = "none";
    $_array = get_unique_stat($link, $json);
    if ($_array[0] != "")
    {
        $display = "table-row";
        $output = output_string($_array, $len);
    }
    require_once("index.html");
?>
<div class='tables-wrapper'>
    <div class='table-container'>
        <table <?php printf("style='display:".$display.";'"); ?> id='myTable'>
            <thead>
                <tr>
                    <th>Difference</th>
                    <th>Rank</th>
                    <th>Faction</th>
                    <th>Username</th>
                    <th>Kills</th>
                    <th>Battle Rank</th>
                    <th>Command Rank</th>
                    <th>BEP</th>
                    <th>CEP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($output != "")
                    {
                        echo ($output);
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>

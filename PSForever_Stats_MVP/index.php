<?php
    //  Idea would be to change IP to remote server with database updating once a week, OR 
    //  keep localhost and have a script dumping stats into a local database (once a week)
    //  for a comparison.
    $ip = "localhost";
    $username = "default";
    $password = "";
    $table_weekly = "table_weekly_stats";
    $db = "test_stats";
    $url = 'https://play.psforever.net/api/char_stats_cep/0';
    $char_limit = 5000;

    $array = [
        "CREATE USER `$username` INDENTIFIED VIA mysql_native_password USING `$password`;",
        "GRANT SELECT , INSERT, UPDATE, CREATE, EVENT ON *.* TO '$username';",
        "CREATE TABLE IF NOT EXISTS $table_weekly (character_name VARCHAR(60), faction SMALLINT(3), _rank INT(10), stat BIGINT(255), bep BIGINT(255), cep BIGINT(255), br SMALLINT(2), cr SMALLINT(2));"
    ];

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
    

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $link = mysqli_connect($ip, $username, $password, $db);
    mysqli_query($link, $array[2]);

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
        $check   = mysqli_query($link, "SELECT `character_name` FROM $table_weekly WHERE `character_name` = '$name'");
        if ($check->num_rows == 0) 
        {
            mysqli_query($link, "INSERT INTO $table_weekly VALUES ('$name', $faction, 0, $total, $bep, $cep, $br, $cr);");
        } 
        else 
        {
            //  Skipping creating two tables and make this update happen once a week.
            mysqli_query($link, "UPDATE $table_weekly SET `stat`= $total,`bep`= $bep,`cep`= $cep,`br`=$br,`cr`= $cr WHERE `character_name` = '$name'");
        }
    }
    //  Get unique stat per index from JSON
    function get_unique_stat($link, $json)
    {
        $len = 50;
        $array = [$len];
        $table_weekly = "table_weekly_stats";
        //  Declare values
        for ($i = 0; $i < $len; $i++) 
        {
            $name    = $json['players'][$i]['name'];
            $faction = (int)$json['players'][$i]['faction_id'];
            $bep     = (int)$json['players'][$i]['bep'];
            $cep     = (int)$json['players'][$i]['cep'];
            $br      = calculateBR($bep);
            $cr      = calculateCR($cep);
            $total   = (int)$cep / 100;
            //  Get previous stats state
            $old_total = mysqli_query($link, "SELECT * FROM $table_weekly WHERE `character_name` = '$name'")->fetch_row();
            $array[$i] = array(
                'rank'    => (int)$old_total[2],
                'oldrank' => 0,
                'change'  => 0,
                'faction' => $faction,
                'name'    => $name,
                'total'   => $total - ((int)$old_total[3] - 100),
                'br'      => $br,
                'cr'      => $cr,
                'bep'     => $bep - (int)$old_total[4],
                'cep'     => $cep - (int)$old_total[5]
            );
        }
        //  Sorting indices from $array to $sort and returning $sort
        $sort = array_fill(0, $len, array(
            'rank'    => 0,
            'oldrank' => 0,
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
                return array("");
            }
            $_name = $array[$num2]['name'];
            $array[$num2]['total'] = -1;
            $old_total = mysqli_query($link, "SELECT * FROM $table_weekly WHERE `character_name` = '$_name';")->fetch_row();
            $sort[$index] = $array[$num2];
            $sort[$index]['total'] = $maxvalue;
            $sort[$index]['rank'] = (int)$index;
            $sort[$index]['oldrank'] = (int)$old_total[2];
            $sort[$index]['change'] = (int)$old_total[2] - (int)$index;
            $maxvalue = 0;
            $num = -1;
            mysqli_query($link, "UPDATE $table_weekly SET `_rank`= $index WHERE `character_name` = '$_name';");
            $num2 = -1;
        }
        return $sort;
    }
    $display = "none";
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
                    function output_string($array, $len) 
                    {
                        if ($array[0] == "")
                        {
                            return $array;
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
                                "<td>".$array[$i]['faction']."</td>".
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
                    $_array = get_unique_stat($link, $json);
                    var_dump($_array);
                    if ($_array[0] != "")
                    {
                        $display = "table-row";
                        printf(
                            output_string(
                                $_array, $len)
                        );
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>
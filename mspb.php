<?php

// By Afrizal F.A - R&D ICWR
// Reference & Recode From https://devnote.in/export-mysql-database-to-sql-file-using-php/

class MySQLBackupper
{

    public function __construct($connection)
    {

        $this->connection = $connection;

    }

    public function downloadFile($fileName, $fileContent)
    {

        $headerFile = "/*\n- MySQL PHP Backupper\n- Github : https://github.com/ICWR-TEAM/MSPB\n- Database backed up by : incrustwerush.org\n*/\r\n\r\n";

        $fileContent = $headerFile . $fileContent;

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Length: ' . strlen($fileContent));
        header('Pragma: public');
        flush();

        echo($fileContent);

    }

    public function mysqlBackup($fileName, $tables = "*")
    {

        if ($tables == "*") {

            $sql['query'] = "SHOW TABLES";
            $sql['execute'] = mysqli_query($this->connection, $sql['query']);

            $tables = [];

            while ($r = mysqli_fetch_array($sql['execute']))
            {

                $tables[] = $r[0];

            }

        } else {

            if (!is_array($tables)) {

                $tables = str_replace(", ", ",", $tables);
                $tables = explode(",", $tables);

            }

        }

        $return = '';

        foreach ($tables as $table)
        {

            $sql['query'] = "SELECT * FROM " . $table;
            $sql['execute'] = mysqli_query($this->connection, $sql['query']);

            $result = $sql['execute'];
            $numColumns = mysqli_field_count($this->connection);

            $sql['query'] = "SHOW CREATE TABLE " . $table;
            $sql['execute'] = mysqli_query($this->connection, $sql['query']);

            $result2 = $sql['execute'];
            $row2 = mysqli_fetch_row($result2);

            $return .= "\n\n" . $row2[1] . ";\n\n";

            for ($i = 0; $i < $numColumns; $i++) {

                while ($row = mysqli_fetch_row($result))
                {

                    $return .= "INSERT INTO $table VALUES(";

                    for ($j = 0; $j < $numColumns; $j++) {

                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = $row[$j];

                        if (isset($row[$j])) {

                            $return .= '"' . $row[$j] . '"';

                        } else {

                            $return .= '""';

                        }

                        if ($j < ($numColumns - 1)) {

                            $return .= ',';

                        }

                    }

                    $return .= ");\n";

                }
                
            }

            $return .= "\n\n\n";
        }

        $this->downloadFile($fileName, $return);

    }

}

if (!empty($_POST['hostname']) && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['database'])) {

    $cfg['db'] = [

        "hostname" => $_POST['hostname'],
        "username" => $_POST['username'],
        "password" => $_POST['password'],
        "database" => $_POST['database']
    
    ];

    if (!empty($_POST['filename'])) {

        $fileName = $_POST['filename'];

    } else {

        $fileName = $_POST['database'] . "_" . date("Y-m-d_H-i-s") . ".sql";

    }
    
    $cfg['db']['connection'] = mysqli_connect($cfg['db']['hostname'], $cfg['db']['username'], $cfg['db']['password'], $cfg['db']['database']);
    
    if ($cfg['db']['connection']) {

        if (!empty($_POST['tables'])) {

            $class = New MySQLBackupper($cfg['db']['connection']);
            $class->mysqlBackup($fileName, $_POST['tables']);

        } else {

            $class = New MySQLBackupper($cfg['db']['connection']);
            $class->mysqlBackup($fileName);

        }
        
    } else {
    
        echo("<a href=\"?\">Back</a><br />");
        echo("<b>Error : </b>" . mysqli_error($cfg['db']['connection']));
    
    }

    exit();

}

?>

<!DOCTYPE html>
<html>

<head>
    <title>MySQL PHP Backupper</title>
    <link rel="icon" href="https://avatars.githubusercontent.com/u/77117873" />
    <meta name="description" content="MySQL PHP Backupper" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body style="background-color: white;">

    <div style="margin: 20px; text-align: center; border: 1px solid black;">

        <div style="margin-bottom: 10px;">

            <div style="margin: 10px;">
                <b style="font-size: 30px;">MySQL PHP Backupper</b>
            </div>

            <div>
                Version 0.1.0
            </div>
            
        </div>

        <hr />

        <div style="margin-bottom: 10px;">
        
            <form enctype="multipart/form-data" method="post">

                <div style="margin-bottom: 10px;">
                    Hostname : <input type="text" name="hostname" placeholder="localhost" />
                </div>

                <div style="margin-bottom: 10px;">
                    Username : <input type="text" name="username" placeholder="username" />
                </div>

                <div style="margin-bottom: 10px;">
                    Password : <input type="password" name="password" placeholder="********" />
                </div>

                <div style="margin-bottom: 10px;">
                    Database : <input type="text" name="database" placeholder="database" />
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="margin-bottom: 10px;">
                        Tables ( Leave blank if you want to back up all tables! ) :
                    </div>
                    <input type="text" name="tables" placeholder="table1, table2" />
                </div>

                <div style="margin-bottom: 10px;">
                    <input type="submit" value="Back Up!" />
                </div>

            </form>

        </div>

        <hr />

        <div style="margin-bottom: 10px;">
        
            <b>Copyright &copy;2022 - incrustwerush.org</b>

        </div>

    </div>

</body>

</html>

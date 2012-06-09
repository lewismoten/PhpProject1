<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        $indexer = new Indexer();
        $indexer->info();
        
        
        class Indexer
        {
            public function info()
            {
                phpinfo();
            }
        }
        ?>
    </body>
</html>

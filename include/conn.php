    <?php   
          
        /* Connect to a MySQL server  连接数据库服务器 */   
        $link = mysqli_connect(   
                    'localhost',  /* The host to connect to 连接MySQL地址 */   
                    'user',      /* The user to connect as 连接MySQL用户名 */   
                    'password',  /* The password to use 连接MySQL密码 */   
                    'world');    /* The default database to query 连接数据库名称*/   
          
        if (!$link) {   
           printf("Can't connect to MySQL Server. Errorcode: %s ", mysqli_connect_error());   
           exit;   
        }   
          
        /* Send a query to the server 向服务器发送查询请求*/   
        if ($result = mysqli_query($link, 'SELECT Name, Population FROM City ORDER BY Population DESC LIMIT 5')) {   
          
            print("Very large cities are: ");   
          
            /* Fetch the results of the query 返回查询的结果 */   
            while( $row = mysqli_fetch_assoc($result) ){   
                printf("%s (%s) ", $row['Name'], $row['Population']);   
            }   
          
            /* Destroy the result set and free the memory used for it 结束查询释放内存 */   
            mysqli_free_result($result);   
        }   
          
        /* Close the connection 关闭连接*/   
        mysqli_close($link);   
        ?> 
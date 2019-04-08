<?php
Use Kinfy\Http\Router;

Router::MATCH(['GET','POST'],'/11',function (){
    echo "双11";
});

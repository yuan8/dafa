import Echo from "laravel-echo"

window.io = require('socket.io-client');
var port_ws=':6003';
if (typeof io !== 'undefined') { 
     window.Echo = new Echo({    
         broadcaster: 'socket.io',    
         host: window.location.hostname +port_ws,  
    });
}

 window.socket_ws = io(window.location.hostname +port_ws);



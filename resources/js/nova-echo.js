import Echo from 'laravel-echo';

if (Nova.config.echo) {
    const config = Nova.config.echo;
    const echoConfig = config.config;
    const userChannel = config.userChannel;

    if (echoConfig.broadcaster === 'socket.io') {
        window.io = require('socket.io-client');
    } else {
        window.Pusher = require('pusher-js');
    }

    window.Echo = new Echo(echoConfig);

    if (userChannel) {
        window.userPrivateChannel = window.Echo.private(userChannel);
    }
}

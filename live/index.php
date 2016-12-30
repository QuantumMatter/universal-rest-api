<html>
<head>
    <title>Testing</title>
    
    <script src="http://davidkopala.com:81/jwplayer-7.5.2/jwplayer.js"></script>
    <script>jwplayer.key="Ho/ww8EIQxnAhpx9QeTd1MZYhBgsbYWlS0ncUQ==";</script>
</head>
 
<body>

    <div id="liveContainer"></div>
    <script type="text/javascript">
        var playerInstance = jwplayer("liveContainer");
        playerInstance.setup({
            file: "rtmp://davidkopala.com/live/"+(location.search.split('id=')[1] ? location.search.split('id=')[1] : 'home'),
            image: "5291_northglenn 2.gif",
            height: 360,
            width: 640
        });
    </script>
    
</body>
</html>
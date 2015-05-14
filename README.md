# Simple Network Scanner

This network scanner displays a list of the known and unknown devices on your LAN in a very simplist web page generated via PHP.

Known devices are stored in a json file. The `arp-scan` command is used behind the scene to scan for running hosts.

## How to use

Install the `arp-scan` and `php5` packages. On Ubuntu you can use:

    sudo apt-get install arp-scan php5

Copy/rename `network.example.json` to `network.json` and add your known devices.
You can start with an empty file and add mac adresses after they appear in the "unknown" area of the GUI.

    cp network.example.json network.json

Start the script in the PHP development server.
Root is required by the `arp-scan` command.

    sudo php -S localhost:8000 server.php

or simply use the provided script (will prompt for your password):

    ./start.sh

You can then access it in your browser by using the address given when starting the server.

Note that a new scan is performed each time you refresh the page.
Also note that `arp-scan` does not always get a response from all hosts, causing them to be sometimes up or down.

Currently the host performing the scan is not shown as up because the arp-scan does not return it.

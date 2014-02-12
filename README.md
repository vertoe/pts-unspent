pts-unspent
===========

Parsing the protoshares blockchain for unspent outputs.


requirements
------------

- php 5.x with mysqli support
- mysql server with mysqli support
- json rpc php library http://jsonrpcphp.org/


preparation & usage
-------------------

1. create a database `pts_balance`

2. create a table `outputs`

    CREATE TABLE `outputs` (
      `block_num` int(11) NOT NULL,
      `block_hash` char(64) NOT NULL,
      `transaction_hash` char(64) NOT NULL,
      `sequence` int(11) NOT NULL,
      `address` varchar(34) NOT NULL,
      `balance` decimal(16,8) NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1

3. run the first script to fill your mysql database with unspent outputs.

    $ php update-database.php

4. run the second script to create json api with unspent outputs.

    $ php update-jsonapi.php > path/to/pts-unspent.json

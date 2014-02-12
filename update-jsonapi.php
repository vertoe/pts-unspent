<?php

/*
 * PHP parser for unspent outputs in the protoshares blockchain.
 *
 * This script creates json api with unspent outputs.
 * Usage: $ php update-jsonapi.php > path/to/pts-unspent.json
 *
 * Donations accepted:
 * - BTC 1Bzc7PatbRzXz6EAmvSuBuoWED96qy3zgc
 * - PTS PcDLYukq5RtKyRCeC1Gv5VhAJh88ykzfka
 *
 * Copyright (C) 2014 vertoe <vertoe@qhor.net>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */

  // Enter DB details, make sure the database 'pts_balance' exists
  $dbhost='localhost';
  $dbuser='root';
  $dbpass='dbpass';
  $database='pts_balances';

////////////////////////////////////////////////////////////////////////////////

  $mysqli=new mysqli($dbhost, $dbuser, $dbpass, $database);

  if ($mysqli->connect_errno)
    throw new Exception("database connection error");

  $r=$mysqli->query("select max(block_num) from outputs", MYSQLI_USE_RESULT);
  $w=$r->fetch_assoc();
  $blocknum=$w["max(block_num)"]+0;
  $r->close();

  $r=$mysqli->query("select sum(balance) from outputs", MYSQLI_USE_RESULT);
  $w=$r->fetch_assoc();
  $moneysupply=$w["sum(balance)"]+0;
  $r->close();

  echo "{\"blocknum\":".$blocknum.",\"blocktime\":0,\"moneysupply\":".$moneysupply.",\"balances\":\n";

  $r=$mysqli->query("select * from outputs group by address order by balance desc", MYSQLI_USE_RESULT);

  while ($w=$r->fetch_assoc()) {
    echo "    {\"".$w['address']."\":".$w['balance']."},\n";
  }

  echo "}";

  $r->close();

////////////////////////////////////////////////////////////////////////////////

?>

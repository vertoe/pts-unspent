<?php

/*
 * PHP parser for unspent outputs in the protoshares blockchain.
 *
 * This script fills your mysql database with unspent outputs.
 * Usage: $ php update-database.php
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

  // Get this from: http://jsonrpcphp.org/
  require_once "jsonRPCClient.php";

  // Enter DB details, make sure the database 'pts_balance' exists
  $dbhost='localhost';
  $dbuser='root';
  $dbpass='dbpass';
  $database='pts_balances';

  // Enter protoshares rpc details, make sure to set rpcuser, rpcpassword,
  // rpcport, rpcallowip and txindex=1 in your protoshares.conf
  $rpchost='localhost';
  $rpcuser='protosharesrpc';
  $rpcpass='superdupersecretphrase';
  $rpcport='3838';

////////////////////////////////////////////////////////////////////////////////

  $mysqli=new mysqli($dbhost, $dbuser, $dbpass, $database);

  if ($mysqli->connect_errno)
    throw new Exception("database connection error");

  $r=$mysqli->query("select max(block_num) from outputs", MYSQLI_USE_RESULT);
  $w=$r->fetch_assoc();
  $last_block_in_db=$w["max(block_num)"]+0;
  $r->close();

  $pts=new jsonRPCClient("http://".$rpcuser.":".$rpcpass."@".$rpchost.":".$rpcport);
  $blocknum=$pts->getblockcount();

  if ($last_block_in_db<$blocknum)
  {
    for ($i=$last_block_in_db+1; $i<=$blocknum; $i++)
    {
      echo "block ".$i."\n";
      $block=$pts->getblock($pts->getblockhash($i));
      foreach ($block["tx"] as $txid)
      {
        $tx=$pts->decoderawtransaction($pts->getrawtransaction($txid));
        foreach ($tx["vin"] as $tx_in)
        {
          if (!array_key_exists("coinbase", $tx_in))
            $mysqli->query("delete from outputs where transaction_hash='".$tx_in["txid"]."' and sequence=".$tx_in["vout"].";");
        }
        foreach ($tx["vout"] as $tx_out)
        {
          $scripttype=$tx_out["scriptPubKey"]["type"];
          switch ($scripttype)
          {
          case "pubkeyhash":
          case "pubkey":
          case "scripthash":
            $mysqli->query("insert into outputs (block_num, block_hash, transaction_hash, sequence, address, balance) values (".$i.", '".$block["hash"]."', '".$txid."', ".$tx_out["n"].", '".$tx_out["scriptPubKey"]["addresses"][0]."', ".$tx_out["value"].")");
            break;
          case "nonstandard":
          case "multisig":
            $mysqli->query("insert into outputs (block_num, block_hash, transaction_hash, sequence, address, balance) values (".$i.", '".$block["hash"]."', '".$txid."', ".$tx_out["n"].", '** ".$scripttype." **', ".$tx_out["value"].")");
            break;
          default:
            throw new Exception("don't know how to handle ".$scripttype." scripts in transaction ".$txid);
            break;
          }
        }
      }
    }
  }
  else
    echo "database is current\n";

////////////////////////////////////////////////////////////////////////////////

?>

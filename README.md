Proof-of-concept online password store in PHP. Encrypts stored passwords
using RSA keypairs, with a passphrase on the private key. SSL should be
used to encrypt the password during HTTP transport.

## SQLite Table

Current data store is a SQLite3 table:

    $ mkdir db
	$ sqlite3 db/pass.sq3
	-- Loading resources from /Users/adam/.sqliterc
	SQLite version 3.7.9 2011-11-01 00:52:41
	Enter ".help" for instructions
	Enter SQL statements terminated with a ";"
	sqlite> CREATE TABLE passwords ( id INTEGER PRIMARY KEY AUTOINCREMENT , label TEXT, username TEXT, password BLOB, note TEXT, domain TEXT);
	sqlite> CREATE TABLE users( id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, publickey BLOB, privatekey BLOB );
	sqlite> 

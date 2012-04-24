<?php

require 'klein/klein.php';

respond( function( $request, $response, $app ) {
	$app->db = new PDO( 'sqlite:' . __DIR__ . '/db/pass.sq3' );
	$app->db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ );

	// hardcoding for now
	$app->user_id = 1;

	$stmt = $app->db->prepare( "SELECT * FROM users WHERE id = :id" );
	$stmt->execute( array( 'id' => $app->user_id ) );
	$response->set( 'user', $stmt->fetch() );

	$response->layout( 'views/html5.phtml' );
});

respond( '/', function( $request, $response, $app ) {
	$stmt = $app->db->prepare( "SELECT * FROM passwords ORDER BY label" );
	$stmt->execute();

	$response->set( 'rows', $stmt );
	$response->render( 'views/home.phtml' );
});

respond( 'GET', '/passphrase', function( $request, $response, $app ) {
	$response->render( 'views/passphrase.phtml' );
});

respond( 'POST', '/passphrase', function( $request, $response, $app ) {
	if( '' === $request->passphrase ) {
		$response->flash( 'Passphrase must not be blank', 'error' );
		$response->refresh();
	}

	// TODO: if there is already a keypair, export the private key with the new passphrase
	$key = openssl_pkey_new();
	openssl_pkey_export( $key, $private_key, $request->passphrase );
	$details = openssl_pkey_get_details( $key );

	$stmt = $app->db->prepare( "UPDATE users SET publickey = :publickey, privatekey = :privatekey WHERE id = :user_id" );
	$args = array(
		'publickey' => $details['key'],
		'privatekey' => $private_key,
		'user_id' => $app->user_id,
	);
	$stmt->execute( $args );

	$response->refresh();
});

respond( 'GET', '/add', function( $request, $response, $app ) {
	$response->render( 'views/add.phtml' );
});

respond( 'POST', '/add', function( $request, $response, $app ) {
	$stmt = $app->db->prepare( "INSERT INTO passwords (label, username, password, note, domain) VALUES (:label, :username, :password, :note, :domain)" );

	$password = $request->password;
	openssl_public_encrypt( $password, $encrypted_password, $response->user->publickey );

	$args = array(
		'label' => $request->label,
		'username' => $request->username,
		'password' => $encrypted_password,
		'note' => $request->note,
		'domain' => $request->domain,
	);

	$stmt->execute( $args );

	$response->flash( "Saved password for {$args['label']}." );
	$response->redirect( '/' );
});

respond( '/view/[i:id]', function( $request, $response, $app ) {
	$stmt = $app->db->prepare( "SELECT * FROM passwords WHERE id = :id" );
	$stmt->execute( array( 'id' => $request->id ) );

	$row = $stmt->fetch();
	$response->set( 'row', $row );

	$response->render( 'views/view.phtml' );
});

respond( '/password/[i:id]', function( $request, $response, $app ) {
	$stmt = $app->db->prepare( "SELECT password FROM passwords WHERE id = :id" );
	$stmt->execute( array( 'id' => $request->id ) );

	$row = $stmt->fetch();

	$key = openssl_pkey_get_private( $response->user->privatekey, 'asdf' );
	openssl_private_decrypt( $row->password, $password, $key );

	echo $password;
});

dispatch();

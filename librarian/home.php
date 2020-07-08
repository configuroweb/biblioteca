<?php
	require "../db_connect.php";
	require "verify_librarian.php";
	require "header_librarian.php";
?>

<html>
	<head>
		<title>Bienvenido Administrador</title>
		<link rel="stylesheet" type="text/css" href="css/home_style.css" />
	</head>
	<body>
		<div id="allTheThings">
			<a href="pending_registrations.php">
				<input type="button" value="Solicitudes de Registro de Usuario Pendientes" />
			</a><br />
			<a href="pending_book_requests.php">
				<input type="button" value="Solicitudes de Libros Pendientes" />
			</a><br />
			<a href="insert_book.php">
				<input type="button" value="Agregar un Nuevo Libro" />
			</a><br />
			<a href="update_copies.php">
				<input type="button" value="Actualizar el número de copias de un Libro" />
			</a><br />
			<a href="update_balance.php">
				<input type="button" value="Actualiza el balance de un Miembro" />
			</a><br />
			<a href="due_handler.php">
				<input type="button" value="Recordatorios para hoy" />
			</a><br /><br />
		</div>
	</body>
</html>